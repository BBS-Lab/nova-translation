<?php

namespace BBS\Nova\Translation\Http\Controllers;

use BBS\Nova\Translation\Models\Label;
use BBS\Nova\Translation\Models\Locale;
use BBS\Nova\Translation\Models\Scopes\TranslatableScope;
use BBS\Nova\Translation\Models\Translation;
use Illuminate\Http\Request;

class LabelsController
{
    /**
     * Setup labels matrix endpoint.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $labels = Label::query()
            ->withoutGlobalScope(TranslatableScope::class)
            ->select('translations.locale_id', 'labels.key', 'labels.value')
            ->join('translations', 'labels.id', '=', 'translations.translatable_id')
            ->where('translations.translatable_type', '=', Label::class)
            ->get();

        $locales = Locale::query()->select('id', 'iso', 'label')->get();

        return response()->json(compact('labels', 'locales'), 200);
    }

    public function save(Request $request)
    {
        print_r($request->all());
        exit;
    }
}
