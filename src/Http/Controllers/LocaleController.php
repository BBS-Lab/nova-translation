<?php

namespace BBSLab\NovaTranslation\Http\Controllers;

use BBSLab\NovaTranslation\Models\Locale;
use Illuminate\Http\Request;

class LocaleController
{
    public function __invoke(Request $request, string $locale)
    {
        if (Locale::query()->where('iso', '=', $locale)->exists()) {
            $request->session()->put(
                nova_translation()->localeSessionKey(),
                $locale
            );
        }

        return redirect()->back();
    }
}
