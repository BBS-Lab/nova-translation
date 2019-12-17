<?php

namespace BBSLab\NovaTranslation\Models\Traits;

use BBSLab\NovaTranslation\Models\Locale;
use BBSLab\NovaTranslation\Models\Translation;
use Exception;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property \BBSLab\NovaTranslation\Models\Translation $translation
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
        if (! isset($this->nonTranslatable)) {
            $this->nonTranslatable = [];
        }
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
     * Translation relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function translation()
    {
        return $this->morphOne(Translation::class, 'translatable');
    }

    /**
     * Return current item translations.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function translations()
    {
        return static::query()
            ->select($this->getTable().'.*', 'translations.locale_id', 'translations.translation_id')
            ->join('translations', $this->getTable().'.'.$this->getKeyName(), '=', 'translations.translatable_id')
            ->where('translations.translatable_type', '=', get_class($this))
            ->where('translations.translation_id', '=', $this->translation->translation_id)
            ->get();
    }

    /**
     * Create and return a translation entry for given locale ID.
     *
     * @param  int  $localeId
     * @return \BBS\Nova\Translation\Models\Translation
     */
    public function upsertTranslationEntry(int $localeId, int $translationId = 0)
    {
        $data = [
            'locale_id' => $localeId,
            'translation_id' => ! empty($translationId) ? $translationId : $this->freshTranslationId(),
            'translatable_id' => $this->getKey(),
            'translatable_type' => get_class($this),
        ];

        $translation = Translation::query()->where($data)->first();
        if (empty($translation)) {
            $translation = Translation::query()->create($data);
        }

        return $translation;
    }

    /**
     * Return next fresh translation ID.
     *
     * @return int
     */
    public function freshTranslationId()
    {
        /** @var \BBSLab\NovaTranslation\Models\Translation $lastTranslation */
        $lastTranslation = Translation::query()
            ->select('translation_id')
            ->where('translatable_type', '=', get_class($this))
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
    public function scopeLocale(Builder $builder, string $iso = '')
    {
        $iso = ! empty($iso) ? $iso : app()->getLocale();

        /** @var \BBSLab\NovaTranslation\Models\Locale $locale */
        $locale = Locale::query()->select('id')->where('iso', '=', $iso)->first();
        if (empty($locale)) {
            throw new Exception('Invalid locale provided in locale() scope "'.$iso.'"');
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
