<?php

namespace BBS\Nova\Translation\Models\Traits;

use BBS\Nova\Translation\Models\Locale;
use BBS\Nova\Translation\Models\Translation;
use Exception;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property array $nonTranslatable
 * @property \Illuminate\Database\Eloquent\Collection $translations
 * @mixin \Illuminate\Database\Eloquent\Model
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
        $translationIdField = $this->translationIdField();

        $this->fillable[] = $translationIdField;
        $this->nonTranslatable[] = $translationIdField;
        $this->casts[$translationIdField] = 'integer';
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

        /** @var \BBS\Nova\Translation\Models\Locale $locale */
        $locale = Locale::query()->select('id')->where('iso', '=', $iso)->first();
        if (empty($locale)) {
            throw new Exception('Invalid locale provided in inLocale scope "'.$iso.'"');
        }

        return $builder->join('translations', function ($join) use ($model, $locale) {
            $join
                ->on($model->getTable().'.'.$model->getKeyName(), '=', 'translations.translatable_id')
                ->where('translations.translatable_type', '=', get_class($model))
                ->where('translations.locale_id', '=', $locale->id);
        });
    }

    /**
     * Return next fresh translation ID.
     *
     * @return int
     */
    public static function freshTranslationId()
    {
        $instance = new static;
        $translationIdField = $instance->translationIdField();

        /** @var \Illuminate\Database\Eloquent\Model $lastTranslation */
        $lastTranslation = static::query()
            ->select($instance->getTable().'.'.$translationIdField)
            ->orderBy($translationIdField, 'desc')
            ->first();

        return ! empty($lastTranslation) ? ($lastTranslation->getAttribute($translationIdField) + 1) : 1;
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
     * Get the list of non translatable fields.
     *
     * @return array
     */
    public function getNonTranslatable()
    {
        return $this->nonTranslatable;
    }

    /**
     * Translation ID accessor.
     *
     * @return int
     */
    public function getTranslationIdAttribute()
    {
        return $this->translationId();
    }

    /**
     * Return translation ID value.
     *
     * @return int
     */
    public function translationId()
    {
        return $this->getAttribute($this->translationIdField());
    }

    /**
     * Define translation ID field name.
     *
     * @return string
     */
    public function translationIdField()
    {
        return 'translation_id';
    }

    /**
     * Create and return a translation entry for given locale ID.
     *
     * @param  int  $localeId
     * @return \BBS\Nova\Translation\Models\Translation
     */
    public function createTranslationEntry(int $localeId)
    {
        Translation::query()->create([
            'locale_id' => $localeId,
            'translation_id' => $this->translationId(),
            'translatable_id' => $this->getKey(),
            'translatable_type' => get_class($this),
        ]);
    }
}
