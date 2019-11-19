<?php

namespace BBS\Nova\Translation\Models\Traits;

use BBS\Nova\Translation\Models\Scopes\TranslatableScope;
use BBS\Nova\Translation\Models\Translation;

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
        static::addGlobalScope(new TranslatableScope);

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
     * Return next fresh translation ID.
     *
     * @return int
     */
    public static function freshTranslationId()
    {
        $instance = new static;
        $translationIdField = $instance->translationIdField();

        /** @var \Illuminate\Database\Eloquent\Model $lastTranslation */
        $lastTranslation = static::newQueryWithoutScope(TranslatableScope::class)
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
