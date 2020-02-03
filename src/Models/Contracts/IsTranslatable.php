<?php

namespace BBSLab\NovaTranslation\Models\Contracts;

use BBSLab\NovaTranslation\Models\Locale;
use BBSLab\NovaTranslation\Models\Translation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 * @property \BBSLab\NovaTranslation\Models\Translation $translation
 */
interface IsTranslatable
{
    /**
     * Get the list of non translatable fields.
     *
     * @return array
     */
    public function getNonTranslatable(): array;

    /**
     * Get the list of fields to duplicate on create.
     * (Other fields MUST BE nullable in database).
     *
     * @return array
     */
    public function getOnCreateTranslatable(): array;

    /**
     * Translation relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function translation(): MorphOne;

    /**
     * Return current item translations.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function translations(): Collection;

    /**
     * Create and return a translation entry for given locale ID.
     *
     * @param  int  $localeId
     * @param  int  $translationId
     * @return \BBSLab\NovaTranslation\Models\Translation
     */
    public function upsertTranslationEntry(int $localeId, int $translationId = 0): Translation;

    /**
     * Return next fresh translation ID.
     *
     * @return int
     */
    public function freshTranslationId(): int;

    /**
     * Translate model to given locale and return translated model.
     *
     * @param  \BBSLab\NovaTranslation\Models\Locale  $locale
     * @return \BBSLab\NovaTranslation\Models\Contracts\IsTranslatable
     */
    public function translate(Locale $locale): IsTranslatable;
}
