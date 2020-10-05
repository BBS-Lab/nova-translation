<?php

namespace BBSLab\NovaTranslation\Models\Contracts;

use BBSLab\NovaTranslation\Models\Locale;
use BBSLab\NovaTranslation\Models\Translation;
use BBSLab\NovaTranslation\Models\TranslationRelation;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 * @property \BBSLab\NovaTranslation\Models\Translation $translation
 * @property \Illuminate\Database\Eloquent\Collection|\BBSLab\NovaTranslation\Models\Translation[] $translations
 * @property bool $_deleting_translation
 * @property bool $_translating_relation
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
     * @return \BBSLab\NovaTranslation\Models\TranslationRelation
     */
    public function translations(): TranslationRelation;

    /**
     * Create and return a translation entry for given locale ID.
     *
     * @param  int  $localeId
     * @param  int  $sourceId
     * @param  int  $translationId
     * @return \BBSLab\NovaTranslation\Models\Translation
     */
    public function upsertTranslationEntry(int $localeId, int $sourceId, int $translationId = 0): Translation;

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
    public function translate(Locale $locale);

    /**
     * Set deleting translation state.
     *
     * @return void
     */
    public function deletingTranslation(): void;

    /**
     * Determine is the model currently in a delete translation process.
     *
     * @return bool
     */
    public function isDeletingTranslation(): bool;

    /**
     * Set deleting translation state.
     *
     * @return void
     */
    public function translatingRelation(): void;

    /**
     * Determine is the model currently in a delete translation process.
     *
     * @return bool
     */
    public function isTranslatingRelation(): bool;
}
