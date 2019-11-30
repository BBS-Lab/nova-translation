<?php

namespace BBSLab\NovaTranslation\Http\Controllers;

use BBSLab\NovaTranslation\Models\Label;
use BBSLab\NovaTranslation\Models\Locale;
use BBSLab\NovaTranslation\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TranslationMatrixController
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

        Label::query()->truncate();
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
        return Label::query()
            ->select('translations.locale_id', 'labels.type', 'labels.key', 'labels.value')
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
        /** @var \BBSLab\NovaTranslation\Models\Translation $keyTranslation */
        $keyTranslation = Translation::query()
            ->select('translations.translation_id')
            ->join('labels', 'translations.translatable_id', '=', 'labels.id')
            ->where('translations.translatable_type', '=', Label::class)
            ->where('labels.key', '=', $data['key'])
            ->first();

        $translationId = ! empty($keyTranslation) ? $keyTranslation->translation_id : (new Label)->freshTranslationId();

        /** @var \BBSLab\NovaTranslation\Models\Label $label */
        $label = Label::query()->create([
            'type' => Label::TYPE_TEXT,
            'key' => $data['key'],
            'value' => ! empty($data['value']) ? $data['value'] : '',
        ]);

        $label->upsertTranslationEntry($data['locale_id'], $translationId);
    }
}
