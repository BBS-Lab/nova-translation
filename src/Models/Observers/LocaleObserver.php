<?php

namespace BBSLab\NovaTranslation\Models\Observers;

use BBSLab\NovaTranslation\Jobs\LocaleCreated;
use BBSLab\NovaTranslation\Jobs\LocaleDeleted;
use BBSLab\NovaTranslation\Models\Locale;
use BBSLab\NovaTranslation\NovaTranslation;

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
        NovaTranslation::forgetLocales();
        LocaleDeleted::dispatch($locale);
    }

    /**
     * Handle the Locale "saved" event.
     *
     * @param  \BBSLab\NovaTranslation\Models\Locale  $locale
     * @return void
     */
    public function saved(Locale $locale)
    {
        NovaTranslation::forgetLocales();
    }
}
