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
     * Return next fresh translatable ID.
     *
     * @return int
     */
    public function freshTranslatableId()
    {
        $translatableIdField = $this->translatableIdField();
        $lastTranslatable = static::query()->select($translatableIdField)->orderBy($translatableIdField, 'desc')->first();

        return ! empty($lastTranslatable) ? ($lastTranslatable->getAttribute($translatableIdField) + 1) : 1;
    }

    /**
     * Return translatable ID value.
     *
     * @return int
     */
    public function translatableId()
    {
        return $this->getAttribute($this->translatableIdField());
    }

    /**
     * Define translatable ID field.
     *
     * @return string
     */
    public function translatableIdField()
    {
        return 'translatable_id';
    }
}
