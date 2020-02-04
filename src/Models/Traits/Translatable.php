<?php

namespace BBSLab\NovaTranslation\Models\Traits;

use BBSLab\NovaTranslation\Models\Contracts\IsTranslatable;
use BBSLab\NovaTranslation\Models\Locale;
use BBSLab\NovaTranslation\Models\Observers\TranslatableObserver;
use BBSLab\NovaTranslation\Models\Translation;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Query\JoinClause;

/**
 * @property \BBSLab\NovaTranslation\Models\Translation $translation
 * @method static \Illuminate\Database\Eloquent\Builder locale(?string $iso = null)
 */
trait Translatable
{
    /**
     * {@inheritdoc}
     */
    public static function bootTranslatable()
    {
        static::observe(TranslatableObserver::class);
    }

    /**
     * Get the list of non translatable fields.
     *
     * @return array
     */
    public function getNonTranslatable(): array
    {
        return isset($this->nonTranslatable) ? $this->nonTranslatable : [];
    }

    /**
     * Get the list of fields to duplicate on create.
     * (Other fields MUST BE nullable in database).
     *
     * @return array
     */
    public function getOnCreateTranslatable(): array
    {
        return isset($this->onCreateTranslatable) ? $this->onCreateTranslatable : $this->getNonTranslatable();
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

    /**
     * Return current item translations.
     *
     * @return \Illuminate\Database\Eloquent\Collection|self[]
     */
    public function translations(): Collection
    {
        return static::query()
            ->select($this->qualifyColumn('*'), 'translations.locale_id', 'translations.translation_id')
            ->with('translation')
            ->join('translations', $this->getQualifiedKeyName(), '=', 'translations.translatable_id')
            ->where('translations.translation_id', '=', optional($this->translation)->translation_id)
            ->where('translations.translatable_type', '=', static::class)
            ->where($this->getQualifiedKeyName(), '<>', $this->getKey())
            ->get();
    }

    /**
     * Create and return a translation entry for given locale ID.
     *
     * @param  int  $localeId
     * @param  int  $translationId
     * @return \BBSLab\NovaTranslation\Models\Translation
     */
    public function upsertTranslationEntry(int $localeId, int $translationId = 0): Translation
    {
        /** @var \BBSLab\NovaTranslation\Models\Translation $translation */
        $translation = Translation::query()
            ->firstOrCreate([
                'locale_id' => $localeId,
                'translation_id' => ! empty($translationId) ? $translationId : $this->freshTranslationId(),
                'translatable_id' => $this->getKey(),
                'translatable_type' => static::class,
            ]);

        return $translation;
    }

    /**
     * Return next fresh translation ID.
     *
     * @return int
     */
    public function freshTranslationId(): int
    {
        $lastTranslation = Translation::query()
            ->where('translatable_type', '=', static::class)
            ->max('translation_id') ?? 0;

        return $lastTranslation + 1;
    }

    /**
     * Scope a query to only retrieve items from given locale.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  string  $iso
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLocale(EloquentBuilder $builder, string $iso = null)
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
    public function translate(Locale $locale)
    {
        /** @var \BBSLab\NovaTranslation\Models\Contracts\IsTranslatable $translated */
        $translated = $this->translations()->first(function (IsTranslatable $translatable) use ($locale) {
            return $translatable->translation->locale_id === $locale->getKey();
        });

        return $translated ?? static::withoutEvents(function () use ($locale) {
            /** @var self $translated */
            $translated = $this->newQuery()->create(
                $this->only(
                    $this->getOnCreateTranslatable()
                )
            );
            $translated->upsertTranslationEntry($locale->getKey(), $this->translation->translation_id);

            return $translated;
        });
    }
}
