<?php

namespace BBSLab\NovaTranslation\Http\Controllers;

use BBSLab\NovaTranslation\Models\Contracts\IsTranslatable;
use BBSLab\NovaTranslation\Models\Locale;
use Laravel\Nova\Nova;

class TranslateController
{
    public function translate(string $resource, $key, Locale $locale)
    {
        $model = Nova::modelInstanceForKey($resource);

        if (empty($model) || !($model instanceof IsTranslatable)) {
            return redirect()->back();
        }

        /** @var \BBSLab\NovaTranslation\Models\Contracts\IsTranslatable $model */
        $model = $model->newQuery()->find($key);

        if (empty($model)) {
            return redirect()->back();
        }

        /** @var \BBSLab\NovaTranslation\Models\Contracts\IsTranslatable $translated */
        $translated = $model->translate($locale);

        return $translated
            ? redirect(nova_resource_url(Nova::resourceForModel($translated), $translated->getKey()).'/edit')
            : redirect()->back();
    }

    protected function errorPage(): string
    {
        return Nova::path().'/404';
    }
}
