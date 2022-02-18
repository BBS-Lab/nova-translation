<?php

namespace BBSLab\NovaTranslation\Models\Observers;

use BBSLab\NovaTranslation\Events\LocaleCreated;
use BBSLab\NovaTranslation\Events\LocaleDeleted;
use BBSLab\NovaTranslation\Models\Locale;
use BBSLab\NovaTranslation\NovaTranslation;

class LocaleObserver
{
    public function created(Locale $locale): void
    {
        event(new LocaleCreated($locale));
    }

    public function deleted(Locale $locale): void
    {
        NovaTranslation::forgetLocales();

        event(new LocaleDeleted($locale));
    }

    public function saved(Locale $locale): void
    {
        NovaTranslation::forgetLocales();
    }

    public function saving(Locale $locale)
    {
        unset($locale->flag);
    }
}
