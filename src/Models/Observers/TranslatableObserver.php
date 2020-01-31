<?php

namespace BBSLab\NovaTranslation\Models\Observers;

use BBSLab\NovaTranslation\Models\Contracts\IsTranslatable;
use BBSLab\NovaTranslation\Models\Locale;
use BBSLab\NovaTranslation\Models\Translation;
use BBSLab\NovaTranslation\NovaTranslation;

class TranslatableObserver
{
    /**
     * Handle the Translatable "created" event.
     *
     * @param  \BBSLab\NovaTranslation\Models\Contracts\IsTranslatable  $translatable
     * @return void
     * @throws \Exception
     */
    public function created(IsTranslatable $translatable)
    {
        $translation = $translatable->upsertTranslationEntry(
            ($currentLocale = NovaTranslation::currentLocale())->getKey(),
            0
        );

        if (! in_array(get_class($translatable), NovaTranslation::translatableModels())) {
            return;
        }

        $attributes = $translatable->only(
            $translatable->getOnCreateTranslatable()
        );

        $translatable::withoutEvents(function () use ($translatable, $translation, $currentLocale, $attributes) {
            NovaTranslation::otherLocales($currentLocale)->each(function (Locale $locale) use (
                $translatable, $translation, $attributes
            ) {
                /** @var \BBSLab\NovaTranslation\Models\Contracts\IsTranslatable $model */
                $model = $translatable->query()->create($attributes);
                $model->upsertTranslationEntry($locale->getkey(), $translation->translation_id);
            });
        });
    }

    /**
     * Handle the Translatable "updated" event.
     *
     * @param  \BBSLab\NovaTranslation\Models\Contracts\IsTranslatable  $translatable
     * @return void
     */
    public function updated(IsTranslatable $translatable)
    {
        $attributes = $translatable->only(
            $translatable->getNonTranslatable()
        );

        $translatable::withoutEvents(function () use ($translatable, $attributes) {
            $translatable->translations()->each(function ($model) use ($translatable, $attributes) {
                /** @var \BBSLab\NovaTranslation\Models\Contracts\IsTranslatable $model */
                $model->update($attributes);
            });
        });
    }

    /**
     * Handle the Translatable "deleted" event.
     *
     * @param  \BBSLab\NovaTranslation\Models\Contracts\IsTranslatable  $translatable
     * @return void
     */
    public function deleted(IsTranslatable $translatable)
    {
        Translation::query()
            ->where('translatable_id', '=', $translatable->translation->translatable_id)
            ->where('translatable_type', '=', $translatable->translation->translatable_type)
            ->where('translation_id', '=', $translatable->translation->translation_id)
            ->where('locale_id', '=', $translatable->translation->locale_id)
            ->delete();
    }
}
