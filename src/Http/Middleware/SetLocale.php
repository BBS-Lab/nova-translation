<?php

namespace BBSLab\NovaTranslation\Http\Middleware;

use BBSLab\NovaTranslation\Models\Locale;
use BBSLab\NovaTranslation\NovaTranslation;
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
        } else {
            $browserLocale = Locale::havingIso(
                $request->server('HTTP_ACCEPT_LANGUAGE')
            );

            $locale = $browserLocale ? $browserLocale->iso : config('app.locale');
            Session::put(NovaTranslation::localeSessionKey(), $locale);

            app()->setLocale($locale);
        }

        return $next($request);
    }
}
