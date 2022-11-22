<?php

declare(strict_types=1);

namespace BBSLab\NovaTranslation\Http\Controllers;

use BBSLab\NovaTranslation\Models\Label;
use BBSLab\NovaTranslation\Models\Locale;
use BBSLab\NovaTranslation\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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

        $locales = nova_translation()->locales();

        return response()->json(compact('labels', 'locales'));
    }

    /**
     * Save all labels provided in payload.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(Request $request)
    {
        $raw = $request->input('labels', []);

        $labels = [];
        $translations = [];

        $labelId = 1;
        $translationId = 1;

        foreach ($raw as $key => $items) {
            $source = $labelId;
            foreach ($items as $item) {
                $labels[] = [
                    'id' => $labelId,
                    'type' => $item['type'],
                    'key' => $key,
                    'value' => $item['value'],
                ];

                $translations[] = [
                    'locale_id' => $item['locale_id'],
                    'translation_id' => $translationId,
                    'translatable_id' => $labelId,
                    'translatable_type' => nova_translation()->labelModel(),
                    'translatable_source' => $source,
                ];

                $labelId++;
            }

            $translationId++;
        }

        DB::connection()->getPdo()->setAttribute(\PDO::ATTR_AUTOCOMMIT, 0);
        DB::beginTransaction();

        nova_translation()->labelModel()::query()->truncate();
        Translation::query()->where('translatable_type', '=', nova_translation()->labelModel())->delete();

        nova_translation()->labelModel()::query()->insert($labels);
        Translation::query()->insert($translations);

        DB::commit();

        return response()->json([
            'labels' => $this->labels(),
        ]);
    }

    /**
     * Download labels in JSON key-value format for given locale.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportLocale(Request $request)
    {
        $locale = $request->input('locale', app()->getLocale());

        $json = nova_translation()->labelModel()::query()
            ->select('key', 'value')
            ->locale($locale)
            ->orderBy('key', 'asc')
            ->get()
            ->pluck('value', 'key')
            ->toArray();

        $path = storage_path('app/labels_'.$locale.'.json');
        file_put_contents($path, json_encode($json, JSON_PRETTY_PRINT));

        return response()->download($path, $locale.'.json');
    }

    protected function labels(): Collection
    {
        return nova_translation()->labelModel()::query()
            ->select('translations.locale_id', 'labels.type', 'labels.key', 'labels.value')
            ->join('translations', 'labels.id', '=', 'translations.translatable_id')
            ->where('translations.translatable_type', '=', nova_translation()->labelModel())
            ->get()
            ->groupBy(['key', 'locale_id'])
            ->transform(function (Collection $localeCollection) {
                $localeCollection = $localeCollection->transform(function (Collection $items) {
                    return $items->first();
                });

                $missingLocales = nova_translation()->locales()->reject(function (Locale $locale) use ($localeCollection) {
                    return $localeCollection->contains('locale_id', '=', $locale->getKey());
                });

                if ($missingLocales->isNotEmpty()) {
                    $original = $localeCollection->first();

                    foreach ($missingLocales as $locale) {
                        $label = nova_translation()->labelModel()::make([
                            'type' => $original?->type,
                            'key' => $original?->key,
                            'value' => null,
                        ]);

                        $label->locale_id = $locale->getKey();

                        $localeCollection->push($label);
                    }
                }

                return $localeCollection;
            });
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
        $label = Label::withoutEvents(function () use ($data) {
            return Label::query()->create([
                'type' => $data['type'],
                'key' => $data['key'],
                'value' => ! empty($data['value']) ? $data['value'] : '',
            ]);
        });

        $label->upsertTranslationEntry($data['locale_id'], $translationId);
    }
}
