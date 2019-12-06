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
        if (Session::has('nova_locale')) {
            $locale = Session::get('nova_locale');
        } else {
            $browserLocale = substr($request->server('HTTP_ACCEPT_LANGUAGE'), 0, 2);
            $databaseLocale = Locale::query()->select('id')->where('iso', '=', $browserLocale)->first();
            if (! empty($databaseLocale)) {
                $locale = $browserLocale;
            } else {
                $locale = config('app.locale');
            }
            Session::put('nova_locale', $locale);
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
