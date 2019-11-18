<?php

namespace BBS\Nova\Translation\Http\Controllers;

use BBS\Nova\Translation\Models\Label;
use BBS\Nova\Translation\Models\Locale;
use BBS\Nova\Translation\Models\Scopes\TranslatableScope;
use BBS\Nova\Translation\Models\Translation;

class LabelsController
{
    /**
     * Setup labels matrix endpoint.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function matrix()
    {
        $locales = Locale::query()->select('id', 'iso', 'label')->get();

        $labels = Label::query()
            ->withoutGlobalScope(TranslatableScope::class)
            ->select('translations.locale_id', 'labels.key', 'labels.value')
            ->join('translations', 'labels.id', '=', 'translations.translatable_id')
            ->where('translations.translatable_type', '=', Label::class)
            ->get();

        $matrix = [];
        foreach ($labels as $label) {
            if (! isset($matrix[$label->key])) {
                $matrix[$label->key] = [];
            }
            $matrix[$label->key][$label->locale_id] = $label->value;
        }

        return response()->json([
            'matrix' => $matrix,
            'locales' => $locales,
        ], 200);
    }
}
