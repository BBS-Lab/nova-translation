<?php

namespace BBSLab\NovaTranslation\Http\Middleware;

use BBSLab\NovaTranslation\Models\Locale;
use BBSLab\NovaTranslation\NovaTranslation;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Response
     */
    public function handle($request, $next)
    {
        if (Session::has(NovaTranslation::localeSessionKey())) {
            app()->setLocale(
                Session::get(NovaTranslation::localeSessionKey())
            );

            $this->whenUsingCookies(function () {
                $locale = Cookie::get(NovaTranslation::localeSessionKey());

                if ($locale) {
                    app()->setLocale($locale);
                }
            });
        } else {
            $browserLocale = Locale::havingIso(
            // Take first 2 (as described flags in config)
                substr($request->server('HTTP_ACCEPT_LANGUAGE'), 0, 2)
            );

            $locale = $browserLocale->iso ?? config('app.locale');

            Session::put(NovaTranslation::localeSessionKey(), $locale);

            $this->whenUsingCookies(function () use ($locale) {
                Cookie::queue(
                    NovaTranslation::localeSessionKey(),
                    $locale,
                    config('nova-translation.cookies_ttl', 60 * 24 * 120),
                );
            });

            app()->setLocale($locale);
        }

        return $next($request);
    }

    public function whenUsingCookies(callable $callback): void
    {
        if (config('nova-translation.use_cookies')) {
            $callback();
        }
    }
}
