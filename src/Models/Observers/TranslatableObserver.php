<?php

namespace BBSLab\NovaTranslation\Models\Observers;

use BBSLab\NovaTranslation\Models\Contracts\IsTranslatable;
use BBSLab\NovaTranslation\Models\Locale;
use BBSLab\NovaTranslation\Models\Translation;

class TranslatableObserver
{
    /**
     * Handle the Translatable "created" event.
     *
     * @param  \BBSLab\NovaTranslation\Models\Contracts\IsTranslatable  $translatable
     * @return void
     *
     * @throws \Exception
     */
    public function created(IsTranslatable $translatable)
    {
        $translation = $translatable->upsertTranslationEntry(
            ($currentLocale = nova_translation()->currentLocale())->getKey(),
            $translatable->getKey()
        );

        if (! in_array($translatable->getMorphClass(), nova_translation()->translatableModels())) {
            return;
        }

        $attributes = $translatable->only(
            $translatable->getOnCreateTranslatable()
        );

        $locales = nova_translation()->otherLocales($currentLocale);
        $related = $translatable->translatedParents($locales);

        $translatable::withoutEvents(function () use ($locales, $translatable, $translation, $attributes, $related) {
            $locales->each(function (Locale $locale) use (
                $translatable, $translation, $attributes, $related
            ) {
                $attributes = array_merge($attributes, $related[$locale->iso] ?? []);
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
     *
     * @throws \Exception
     */
    public function updated(IsTranslatable $translatable)
    {
        $attributes = $translatable->only(
            $translatable->getNonTranslatable()
        );

        $related = $translatable->translatedParents(nova_translation()->otherLocales($translatable->translation->locale));

        $translatable::withoutEvents(function () use ($translatable, $attributes, $related) {
            $translatable->translations->each(function (Translation $translation) use ($attributes, $related) {
                $attributes = array_merge($attributes, $related[$translation->locale->iso] ?? []);
                $translation->translatable->update($attributes);
            });
        });
    }

    /**
     * Handle the Translatable "deleted" event.
     *
     * @param  \BBSLab\NovaTranslation\Models\Contracts\IsTranslatable  $translatable
     * @return void
     *
     * @throws \Exception
     */
    public function deleted(IsTranslatable $translatable)
    {
        $translatable->load('translations');
        $translatable->translation->delete();

        if (! in_array($translatable->getMorphClass(), nova_translation()->translatableModels())) {
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
