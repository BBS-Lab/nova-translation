<?php

namespace BBSLab\NovaTranslation\Models\Observers;

use BBSLab\NovaTranslation\Jobs\LocaleCreated;
use BBSLab\NovaTranslation\Jobs\LocaleDeleted;
use BBSLab\NovaTranslation\Models\Locale;
use BBSLab\NovaTranslation\NovaTranslationServiceProvider;

class LocaleObserver
{
    /**
     * Handle the Locale "created" event.
     *
     * @param  \BBSLab\NovaTranslation\Models\Locale  $locale
     * @return void
     */
    public function created(Locale $locale)
    {
        LocaleCreated::dispatch($locale);
    }

    /**
     * Handle the Locale "deleted" event.
     *
     * @param  \BBSLab\NovaTranslation\Models\Locale  $locale
     * @return void
     */
    public function deleted(Locale $locale)
    {
        LocaleDeleted::dispatch($locale);
    }

    /**
     * Return list of translated models.
     *
     * @return array
     */
    protected function translatableModels()
    {
        return config(NovaTranslationServiceProvider::PACKAGE_ID.'.auto_synced_models');
    }
}
