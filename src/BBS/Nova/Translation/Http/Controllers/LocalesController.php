<?php

namespace BBS\Nova\Translation\Http\Controllers;

use BBS\Nova\Translation\Models\Locale;

class LocalesController
{
    /**
     * Setup locales endpoint.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function all()
    {
        $locales = Locale::orderBy('id', 'asc')->get();

        return response()->json($locales, 200);
    }
}
