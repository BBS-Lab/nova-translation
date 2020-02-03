<?php

namespace BBSLab\NovaTranslation\Http\Controllers;

use BBSLab\NovaTranslation\Models\Locale;
use BBSLab\NovaTranslation\NovaTranslation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LocaleController
{
    public function __invoke(Request $request, string $locale)
    {
        if (Locale::query()->where('iso', '=', $locale)->exists()) {
            $request->session()->put(
                NovaTranslation::localeSessionKey(),
                $locale
            );
        }

        return redirect()->back();
    }
}
