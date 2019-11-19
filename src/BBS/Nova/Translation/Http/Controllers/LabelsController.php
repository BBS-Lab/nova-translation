<?php

namespace BBS\Nova\Translation\Http\Controllers;

use BBS\Nova\Translation\Models\Label;
use BBS\Nova\Translation\Models\Locale;
use BBS\Nova\Translation\Models\Scopes\TranslatableScope;
use BBS\Nova\Translation\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LabelsController
{
    /**
     * Setup labels matrix endpoint.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $labels = $this->labels();

        $locales = Locale::query()->select('id', 'label')->get();

        return response()->json(compact('labels', 'locales'), 200);
    }

    /**
     * Save all labels provided in payload.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(Request $request)
    {
        // @TODO...
        DB::beginTransaction();

        Label::newQueryWithoutScope(TranslatableScope::class)->truncate();
        Translation::query()->where('translatable_type', '=', Label::class)->delete();

        $labels = $request->input('labels', []);
        foreach ($labels as $label) {
            $this->createLabel($label);
        }

        DB::commit();

        return response()->json([
            'labels' => $labels,
        ], 200);
    }

    /**
     * Return all non-scoped labels.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function labels()
    {
        return Label::newQueryWithoutScope(TranslatableScope::class)
            ->select('translations.locale_id', 'labels.key', 'labels.value')
            ->join('translations', 'labels.id', '=', 'translations.translatable_id')
            ->where('translations.translatable_type', '=', Label::class)
            ->get();
    }

    /**
     * Create label and associated translation.
     *
     * @param  array  $data
     * @return void
     */
    protected function createLabel(array $data)
    {
        $translatedLabel = Label::newQueryWithoutScope(TranslatableScope::class)
            ->select('labels.translation_id')
            ->join('translations', function ($join) {
                $join
                    ->on('translations.translatable_id', '=', 'labels.id')
                    ->where('translations.translatable_type', '=', Label::class);
            })
            ->where('labels.key', '=', $data['key'])
            ->first();

        $translationId = ! empty($translatedLabel) ? $translatedLabel->translation_id : Label::freshTranslationId();

        $label = Label::query()->create([
            'translation_id' => $translationId,
            'key' => $data['key'],
            'value' => ! empty($data['value']) ? $data['value'] : '',
        ]);

        Translation::query()->create([
            'locale_id' => $data['locale_id'],
            'translation_id' => $translationId,
            'translatable_id' => $label->id,
            'translatable_type' => Label::class,
        ]);
    }
}
