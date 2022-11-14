<?php

declare(strict_types=1);

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
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
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
                'translatable_type' => $this->getMorphClass(),
                'translatable_source' => $sourceId,
            ]);
    }

    public function freshTranslationId(): int
    {
        $lastTranslation = Translation::query()
            ->where('translatable_type', '=', $this->getMorphClass())
            ->max('translation_id') ?? 0;

        return $lastTranslation + 1;
    }

    public function scopeLocale(EloquentBuilder $builder, $iso = null): EloquentBuilder
    {
        if (is_array($iso)) {
            $iso = null;
        }

        $prefix = Str::random(8);
        $table = "{$prefix}_translations";

        return $builder->join("translations as {$table}", function (JoinClause $join) use ($table) {
            $join->on($this->getQualifiedKeyName(), '=', "{$table}.translatable_id")
                ->where("{$table}.translatable_type", '=', $this->getMorphClass());
        })
            ->join('locales', function (JoinClause $join) use ($iso, $table) {
                $join->on('locales.id', '=', "{$table}.locale_id")
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

            return in_array($type->getName(), [BelongsTo::class, MorphTo::class]);
        })->each(function (ReflectionMethod $method) use (&$related, $locales) {
            switch ($method->getReturnType()->getName()) {
                case BelongsTo::class: $this->relatedBelongsTo($method, $related, $locales); break;
                case MorphTo::class: $this->relatedMorphTo($method, $related, $locales); break;
                default: break;
            }
        });

        return $related;
    }

    protected function relatedBelongsTo(ReflectionMethod $method, array &$related, Collection $locales): void
    {
        $foreignKey = $this->{$method->getName()}()->getForeignKeyName();

        /** @var \Illuminate\Database\Eloquent\Model|null $parent */
        $parent = $this->{$method->getName()};

        if (empty($parent) || ! $parent instanceof IsTranslatable) {
            $locales->each(function (Locale $locale) use (&$related, $parent, $foreignKey) {
                $related[$locale->iso][$foreignKey] = optional($parent)->getKey();
            });

            return;
        }

        $parent->load('translation.locale', 'translations');
        $translations = $parent->translations->mapWithKeys(function (Translation $translation) {
            return [$translation->locale->iso => $translation->translatable_id];
        });

        $locales->each(function (Locale $locale) use (&$related, $translations, $foreignKey, $parent) {
            $related[$locale->iso][$foreignKey] = $translations->get($locale->iso) ?? $parent->translate($locale)->getKey();
        });
    }

    protected function relatedMorphTo(ReflectionMethod $method, array &$related, Collection $locales): void
    {
        $foreignKey = $this->{$method->getName()}()->getForeignKeyName();
        $morphType = $this->{$method->getName()}()->getMorphType();
        $attribute = Str::snake($method->getName());

        /** @var \Illuminate\Database\Eloquent\Model|null $parent */
        $parent = $this->{$attribute};

        if (empty($parent) || ! $parent instanceof IsTranslatable) {
            $locales->each(function (Locale $locale) use (&$related, $parent, $foreignKey, $morphType) {
                $related[$locale->iso][$foreignKey] = optional($parent)->getKey();
                $related[$locale->iso][$morphType] = $parent ? $parent->getMorphClass() : null;
            });

            return;
        }

        $parent->load('translation.locale', 'translations');
        $translations = $parent->translations->mapWithKeys(function (Translation $translation) {
            return [$translation->locale->iso => $translation->translatable_id];
        });

        $locales->each(function (Locale $locale) use (&$related, $translations, $foreignKey, $morphType, $parent) {
            $related[$locale->iso][$foreignKey] = $translations->get($locale->iso) ?? $parent->translate($locale)->getKey();
            $related[$locale->iso][$morphType] = $parent->getMorphClass();
        });
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

    public function initTranslation(): IsTranslatable
    {
        if ($this->translation) {
            return $this;
        }

        $translation = $this->upsertTranslationEntry(
            ($currentLocale = nova_translation()->currentLocale())->getKey(),
            $this->getKey()
        );

        if (! in_array($this->getMorphClass(), nova_translation()->translatableModels())) {
            return $this;
        }

        $attributes = $this->only(
            $this->getOnCreateTranslatable()
        );
        $locales = nova_translation()->otherLocales($currentLocale);

        $this::withoutEvents(function () use ($locales, $translation, $attributes) {
            $locales->each(function (Locale $locale) use ($translation, $attributes) {
                /** @var \BBSLab\NovaTranslation\Models\Contracts\IsTranslatable $model */
                $model = $this->newQuery()->create($attributes);
                $model->upsertTranslationEntry(
                    $locale->getkey(), $this->getKey(), $translation->translation_id
                );
            });
        });

        return $this;
    }

    public function updateTranslationParents(): IsTranslatable
    {
        if (! $this->translation) {
            return $this->initTranslation();
        }

        $locales = nova_translation()->otherLocales($currentLocale = nova_translation()->currentLocale());
        $related = $this->translatedParents($locales);

        static::withoutEvents(function () use ($related) {
            $this->translations->each(function (Translation $translation) use ($related) {
                $translation->translatable->update($related[$translation->locale->iso] ?? []);
            });
        });

        return $this;
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
