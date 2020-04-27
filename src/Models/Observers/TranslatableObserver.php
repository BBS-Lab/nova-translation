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
            $translatable->getKey()
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
                $model->upsertTranslationEntry(
                    $locale->getkey(), $translatable->getKey(), $translation->translation_id
                );
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
            $translatable->translations->each(function (Translation $translation) use ($translatable, $attributes) {
                $translation->translatable->update($attributes);
            });
        });
    }

    /**
     * Handle the Translatable "deleted" event.
     *
     * @param  \BBSLab\NovaTranslation\Models\Contracts\IsTranslatable  $translatable
     * @return void
     * @throws \Exception
     */
    public function deleted(IsTranslatable $translatable)
    {
        $translatable->load('translations');
        $translatable->translation->delete();

        if (! in_array(get_class($translatable), NovaTranslation::translatableModels())) {
            return;
        }

        // Prevent deleted translation to delete other translations again.
        if ($translatable->isDeletingTranslation()) {
            return;
        }

        $translatable->translations->each(function (Translation $translation) {
            $translatable = $translation->translatable;
            $translatable->deletingTranslation();
            $translatable->delete();
        });
    }
}
