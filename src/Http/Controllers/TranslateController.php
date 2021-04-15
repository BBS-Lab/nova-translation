<?php

namespace BBSLab\NovaTranslation\Http\Controllers;

use BBSLab\NovaTranslation\Models\Contracts\IsTranslatable;
use BBSLab\NovaTranslation\Models\Locale;
use Laravel\Nova\Nova;
use Laravel\Nova\Resource;

class TranslateController
{
    public function translate(string $resource, $key, Locale $locale)
    {
        $model = Nova::modelInstanceForKey($resource);

        if (empty($model) || ! ($model instanceof IsTranslatable)) {
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
            ? redirect(str_replace('//', '/', $this->redirectToResource($translated)))
            : redirect()->back();
    }

    protected function errorPage(): string
    {
        return Nova::path().'/404';
    }

    protected function redirectToResource(IsTranslatable $translatable): string
    {
        $resource = Nova::resourceForModel($translatable);

        if (! is_subclass_of($resource, Resource::class)) {
            throw new \BadMethodCallException("{$resource} is not a valid Nova resource");
        }

        return implode('/', array_filter([
            trim(Nova::path()),
            'resources',
            $resource::uriKey(),
            $translatable->getKey(),
            'edit',
        ]));
    }
}
