<?php

declare(strict_types=1);

namespace BBSLab\NovaTranslation\Http\View\Composers;

use Illuminate\View\View;

class LocaleComposer
{
    /**
     * @throws \Exception
     */
    public function compose(View $view): void
    {
        $view->with([
            'locale' => $current = nova_translation()->currentLocale(),
            'locales' => nova_translation()->otherLocales($current),
        ]);
    }
}
