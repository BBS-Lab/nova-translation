<?php

namespace BBSLab\NovaTranslation\Models\Traits;

use BBSLab\NovaTranslation\Models\Locale;
use BBSLab\NovaTranslation\Models\Translation;
use Exception;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property array $non_translatable
 * @property \Illuminate\Database\Eloquent\Collection $translations
 */
trait Translatable
{
    /**
     * {@inheritdoc}
     */
    public static function bootTranslatable()
    {
        static::deleted(function ($model) {
            Translation::query()
                ->where('translatable_id', '=', $model->getKey())
                ->where('translatable_type', '=', get_class($model))
                ->delete();
        });
    }

    /**
     * Initialize a translatable model.
     *
     * @return void
     */
    public function initializeTranslatable()
    {
        $this->nonTranslatable = [];
    }

    /**
     * Get the list of non translatable fields.
     *
     * @return array
     */
    public function getNonTranslatable()
    {
        return $this->nonTranslatable;
    }

    /**
     * Translations relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translatable')->withPivot(['locale_id', 'translation_id']);
    }

    /**
     * Create and return a translation entry for given locale ID.
     *
     * @param  int  $localeId
     * @return \BBS\Nova\Translation\Models\Translation
     */
    public function createTranslationEntry(int $localeId, int $translationId = 0)
    {
        Translation::query()->create([
            'locale_id' => $localeId,
            'translation_id' => ! empty($translationId) ? $translationId : static::freshTranslationId(),
            'translatable_id' => $this->getKey(),
            'translatable_type' => get_class($this),
        ]);
    }

    /**
     * Return next fresh translation ID.
     *
     * @return int
     */
    public static function freshTranslationId()
    {
        /** @var \BBSLab\NovaTranslation\Models\Translation $lastTranslation */
        $lastTranslation = Translation::query()
            ->select('translation_id')
            ->orderBy('translation_id', 'desc')
            ->first();

        return ! empty($lastTranslation) ? ($lastTranslation->translation_id + 1) : 1;
    }

    /**
     * Scope a query to only retrieve items from given locale.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  string  $iso
     * @return \Illuminate\Database\Eloquent\Builder
     * @throws \Exception
     */
    public function scopeInLocale(Builder $builder, string $iso = '')
    {
        $iso = ! empty($iso) ? $iso : app()->getLocale();

        /** @var \BBSLab\NovaTranslation\Models\Locale $locale */
        $locale = Locale::query()->select('id')->where('iso', '=', $iso)->first();
        if (empty($locale)) {
            throw new Exception('Invalid locale provided in inLocale() scope "'.$iso.'"');
        }

        return $builder->join('translations', function ($join) use ($locale) {
            $model = new static;

            $join
                ->on($model->getTable().'.'.$model->getKeyName(), '=', 'translations.translatable_id')
                ->where('translations.translatable_type', '=', get_class($model))
                ->where('translations.locale_id', '=', $locale->id);
        });
    }
}
