<?php

declare(strict_types=1);

namespace BBSLab\NovaTranslation\Http\Middleware;

use BBSLab\NovaTranslation\Models\Locale;
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
        if (Session::has(nova_translation()->localeSessionKey())) {
            app()->setLocale(
                Session::get(nova_translation()->localeSessionKey())
            );
        } elseif (Cookie::has(nova_translation()->localeSessionKey())) {
            $locale = Cookie::get(nova_translation()->localeSessionKey());

            if ($locale) {
                app()->setLocale($locale);
            }
        } else {
            $browserLocale = Locale::havingIso(
                // Take first 2 (as described flags in config)
                substr($request->server('HTTP_ACCEPT_LANGUAGE'), 0, 2) ?? ''
            );

            $locale = $browserLocale->iso ?? config('app.locale');

            Session::put(nova_translation()->localeSessionKey(), $locale);

            $this->whenUsingCookies(function () use ($locale) {
                if (! Cookie::has(nova_translation()->localeSessionKey())) {
                    Cookie::queue(
                        Cookie::make(
                            nova_translation()->localeSessionKey(),
                            $locale,
                            config('nova-translation.cookies_ttl')
                        )
                    );
                }
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
