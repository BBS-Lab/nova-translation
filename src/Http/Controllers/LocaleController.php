<?php

namespace BBSLab\NovaTranslation\Http\Controllers;

use BBSLab\NovaTranslation\Models\Locale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class LocaleController
{
    public function __invoke(Request $request, string $locale)
    {
        if (Locale::query()->where('iso', '=', $locale)->exists()) {
            $request->session()->put(
                nova_translation()->localeSessionKey(),
                $locale
            );

            $this->whenUsingCookies(function () use ($locale) {
                Cookie::queue(
                    NovaTranslation::localeSessionKey(),
                    $locale,
                    config('nova-translation.cookies_ttl', 60 * 24 * 120)
                );
            });
        }

        return redirect()->back();
    }

    public function whenUsingCookies(callable $callback): void
    {
        if (config('nova-translation.use_cookies')) {
            $callback();
        }
    }
}
