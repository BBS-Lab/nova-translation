<?php

namespace BBSLab\NovaTranslation\Models\Traits;

use BBSLab\NovaTranslation\Models\Contracts\IsTranslatable;
use BBSLab\NovaTranslation\Models\Locale;
use BBSLab\NovaTranslation\Models\Observers\TranslatableObserver;
use BBSLab\NovaTranslation\Models\Translation;
use BBSLab\NovaTranslation\Models\TranslationRelation;
use Exception;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionMethod;

/**
 * @property \BBSLab\NovaTranslation\Models\Translation $translation
 * @property \Illuminate\Database\Eloquent\Collection|\BBSLab\NovaTranslation\Models\Translation[] $translations
 *
 * @method static \Illuminate\Database\Eloquent\Builder locale(?string $iso = null)
 */
trait Translatable
{
    protected $_deleting_translation = false;
    protected $_translating_relation = false;

    public static function bootTranslatable()
    {
        static::observe(TranslatableObserver::class);
    }

    public function getNonTranslatable(): array
    {
        return $this->nonTranslatable ?? [];
    }

    /**
     * Get the list of fields to duplicate on create.
     * (Other fields MUST BE nullable in database).
     *
     * @return array
     */
    public function getOnCreateTranslatable(): array
    {
        return $this->onCreateTranslatable ?? $this->getNonTranslatable();
    }

    /**
     * Translation relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function translation(): MorphOne
    {
        return $this->morphOne(Translation::class, 'translatable');
    }

    public function translations(): TranslationRelation
    {
        return new TranslationRelation($this);
    }

    public function upsertTranslationEntry(int $localeId, int $sourceId, int $translationId = 0): Translation
    {
        return Translation::query()
            ->firstOrCreate([
                'locale_id' => $localeId,
                'translation_id' => ! empty($translationId) ? $translationId : $this->freshTranslationId(),
                'translatable_id' => $this->getKey(),
                'translatable_type' => static::class,
                'translatable_source' => $sourceId,
            ]);
    }

    public function freshTranslationId(): int
    {
        $lastTranslation = Translation::query()
            ->where('translatable_type', '=', static::class)
            ->max('translation_id') ?? 0;

        return $lastTranslation + 1;
    }

    public function scopeLocale(EloquentBuilder $builder, ?string $iso = null): EloquentBuilder
    {
        return $builder->join('translations', function (JoinClause $join) {
            $join->on($this->getQualifiedKeyName(), '=', 'translations.translatable_id')
                ->where('translations.translatable_type', '=', static::class);
        })
            ->join('locales', function (JoinClause $join) use ($iso) {
                $join->on('locales.id', '=', 'translations.locale_id')
                    ->where('locales.iso', '=', $iso ?? app()->getLocale());
            })
            ->select($this->qualifyColumn('*'));
    }

    public function translatedParents(Collection $locales): array
    {
        try {
            $class = new ReflectionClass($this);
        } catch (Exception $exception) {
            return [];
        }

        $related = $locales->mapWithKeys(function (Locale $locale) {
            return [$locale->iso => []];
        })->toArray();

        Collection::make($class->getMethods())->filter(function (ReflectionMethod $method) {
            if (! $type = $method->getReturnType()) {
                return false;
            }

            if (! method_exists($type, 'getName')) {
                return false;
            }

            return $type->getName() === BelongsTo::class;
        })->each(function (ReflectionMethod $method) use (&$related, $locales) {
            $foreignKey = $this->{$method->getName()}()->getForeignKeyName();

            /** @var \Illuminate\Database\Eloquent\Model|null $parent */
            $parent = $this->{$method->getName()};

            if (empty($parent) || ! $parent instanceof IsTranslatable) {
                $locales->each(function (Locale $locale) use (&$related, $parent, $foreignKey) {
                    $related[$locale->iso][$foreignKey] = optional($parent)->getKey();
                });

                return;
            }

            $parent->load('translation.locale');
            $translations = $parent->translations->mapWithKeys(function (Translation $translation) {
                return [$translation->locale->iso => $translation->translatable_id];
            });

            $locales->each(function (Locale $locale) use (&$related, $translations, $foreignKey, $parent) {
                $related[$locale->iso][$foreignKey] = $translations->get($locale->iso) ?? $parent->translate($locale)->getKey();
            });
        });

        return $related;
    }

    public function translate(Locale $locale): IsTranslatable
    {
        /** @var \BBSLab\NovaTranslation\Models\Contracts\IsTranslatable $translated */
        $translated = optional(
            $this->translations()
                ->where('locale_id', '=', $locale->getKey())
                ->with('translatable')
                ->first()
        )->translatable;

        return $translated ?? static::withoutEvents(function () use ($locale) {
            $parents = $this->translatedParents(collect([$locale]));

            $attributes = array_merge(
                $this->only($this->getOnCreateTranslatable()),
                $parents[$locale->iso] ?? []
            );

            /** @var \BBSLab\NovaTranslation\Models\Traits\Translatable $translated */
            $translated = $this->newQuery()->create($attributes);
            $translated->upsertTranslationEntry(
                $locale->getKey(), $this->getKey(), $this->translation->translation_id
            );

            return $translated;
        });
    }

    public function deletingTranslation(): void
    {
        $this->_deleting_translation = true;
    }

    public function isDeletingTranslation(): bool
    {
        return $this->_deleting_translation;
    }

    public function translatingRelation(): void
    {
        $this->_translating_relation = true;
    }

    public function isTranslatingRelation(): bool
    {
        return $this->_translating_relation;
    }
}
