<?php

namespace BBS\Nova\Translation\Models\Traits;

use BBS\Nova\Translation\Models\Scopes\TranslatableScope;

trait Translatable
{
    /**
     * {@inheritdoc}
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new TranslatableScope);
    }

    /**
     * Return next fresh translation ID.
     *
     * @return int
     */
    public function freshTranslationId()
    {
        $translationIdField = $this->translationIdField();
        $lastTranslation = static::query()->select($translationIdField)->orderBy($translationIdField, 'desc')->first();

        return ! empty($lastTranslation) ? ($lastTranslation->getAttribute($translationIdField) + 1) : 1;
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
}
