<?php

namespace BBSLab\NovaTranslation\Models\Traits;

use BBSLab\NovaTranslation\Models\Contracts\IsTranslatable;
use BBSLab\NovaTranslation\Models\Locale;
use BBSLab\NovaTranslation\Models\Observers\TranslatableObserver;
use BBSLab\NovaTranslation\Models\Translation;
use BBSLab\NovaTranslation\Models\TranslationRelation;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Query\JoinClause;

/**
 * @property \BBSLab\NovaTranslation\Models\Translation $translation
 * @property \Illuminate\Database\Eloquent\Collection|\BBSLab\NovaTranslation\Models\Translation[] $translations
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

    /**
     * Translate model to given locale and return translated model.
     *
     * @param  \BBSLab\NovaTranslation\Models\Locale  $locale
     * @return \BBSLab\NovaTranslation\Models\Contracts\IsTranslatable
     */
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
            /** @var self $translated */
            $translated = $this->newQuery()->create(
                $this->only(
                    $this->getOnCreateTranslatable()
                )
            );

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
