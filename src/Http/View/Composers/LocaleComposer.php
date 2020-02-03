<?php

namespace BBSLab\NovaTranslation\Http\View\Composers;

use BBSLab\NovaTranslation\NovaTranslation;
use Illuminate\View\View;

class LocaleComposer
{
    /**
     * Bind data to the view.
     *
     * @param  \Illuminate\View\View  $view
     * @return void
     * @throws \Exception
     */
    public function compose(View $view)
    {
        $view->with([
            'locale' => $current = NovaTranslation::currentLocale(),
            'locales' => NovaTranslation::otherLocales($current),
        ]);
    }
}
