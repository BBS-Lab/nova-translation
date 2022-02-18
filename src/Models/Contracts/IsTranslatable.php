<?php

namespace BBSLab\NovaTranslation\Models\Contracts;

use BBSLab\NovaTranslation\Models\Locale;
use BBSLab\NovaTranslation\Models\Translation;
use BBSLab\NovaTranslation\Models\TranslationRelation;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 *
 * @property \BBSLab\NovaTranslation\Models\Translation $translation
 * @property \Illuminate\Database\Eloquent\Collection|\BBSLab\NovaTranslation\Models\Translation[] $translations
 * @property bool $_deleting_translation
 * @property bool $_translating_relation
 */
interface IsTranslatable
{
    public function getNonTranslatable(): array;

    public function getOnCreateTranslatable(): array;

    public function translation(): MorphOne;

    public function translations(): TranslationRelation;

    public function upsertTranslationEntry(int $localeId, int $sourceId, int $translationId = 0): Translation;

    public function freshTranslationId(): int;

    public function translate(Locale $locale): IsTranslatable;

    public function deletingTranslation(): void;

    public function isDeletingTranslation(): bool;

    public function translatingRelation(): void;

    public function isTranslatingRelation(): bool;
}
