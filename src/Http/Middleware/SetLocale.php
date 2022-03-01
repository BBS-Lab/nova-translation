<?php

namespace BBSLab\NovaTranslation\Http\Middleware;

use BBSLab\NovaTranslation\Models\Locale;
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
        } else {
            $browserLocale = Locale::havingIso(
                // Take first 2 (as described flags in config)
                substr($request->server('HTTP_ACCEPT_LANGUAGE'), 0, 2)
            );

            $locale = $browserLocale ? $browserLocale->iso : config('app.locale');
            Session::put(nova_translation()->localeSessionKey(), $locale);

            app()->setLocale($locale);
        }

        return $next($request);
    }
}
