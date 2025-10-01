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
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * Save a single translation entry.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(Request $request)
    {
        $key = $request->input('key');
        $type = $request->input('type');
        $value = $request->input('value');
        $localeId = $request->input('locale_id');

        if (empty($key) || empty($type) || !isset($value) || empty($localeId)) {
            return response()->json(['error' => 'Missing required fields'], 422);
        }

        try {
            DB::beginTransaction();

            $label = $this->saveTranslation($key, $type, $value, $localeId);

            DB::commit();

            return response()->json([
                'success' => true,
                'label' => $label,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Save multiple translations at once.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveAll(Request $request)
    {
        $translations = $request->input('translations', []);
        $savedLabels = [];
        $errors = [];

        try {
            DB::beginTransaction();

            foreach ($translations as $translation) {
                if (empty($translation['key']) || empty($translation['type']) || !isset($translation['value']) || empty($translation['locale_id'])) {
                    $errors[] = [
                        'key' => $translation['key'] ?? 'unknown',
                        'error' => 'Missing required fields'
                    ];
                    continue;
                }

                try {
                    $label = $this->saveTranslation(
                        $translation['key'],
                        $translation['type'],
                        $translation['value'],
                        $translation['locale_id']
                    );
                    $savedLabels[] = $label;
                } catch (\Exception $e) {
                    $errors[] = [
                        'key' => $translation['key'],
                        'error' => $e->getMessage()
                    ];
                }
            }

            DB::commit();

            return response()->json([
                'success' => empty($errors),
                'labels' => $savedLabels,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Save a single translation.
     *
     * @param string $key
     * @param string $type
     * @param string $value
     * @param int $localeId
     * @return Label
     */
    protected function saveTranslation($key, $type, $value, $localeId)
    {
        $existingTranslation = Translation::query()
            ->select('translations.translation_id', 'translations.translatable_source')
            ->join('labels', 'translations.translatable_id', '=', 'labels.id')
            ->where('translations.translatable_type', '=', nova_translation()->labelModel())
            ->where('labels.key', '=', $key)
            ->first();

        $label = nova_translation()->labelModel()::query()
            ->where('key', $key)
            ->where(function ($query) use ($localeId) {
                $query->whereHas('translations', function ($q) use ($localeId) {
                    $q->where('locale_id', $localeId);
                });
            })
            ->first();

        if (!$label) {
            $label = nova_translation()->labelModel()::create([
                'type' => $type,
                'key' => $key,
                'value' => $value,
            ]);
        } else {
            $label->update([
                'value' => $value,
            ]);
        }

        return $label;
    }

    /**
     * Delete a translation key and all its translations.
     *
     * @param string $key
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($key)
    {
        try {
            DB::beginTransaction();

            $labels = nova_translation()->labelModel()::where('key', $key)->get();

            foreach ($labels as $label) {
                $label->delete();
            }

            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

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

        $translationId = !empty($keyTranslation) ? $keyTranslation->translation_id : (new Label)->freshTranslationId();

        /** @var \BBSLab\NovaTranslation\Models\Label $label */
        $label = Label::withoutEvents(function () use ($data) {
            return Label::query()->create([
                'type' => $data['type'],
                'key' => $data['key'],
                'value' => !empty($data['value']) ? $data['value'] : '',
            ]);
        });

        $label->upsertTranslationEntry($data['locale_id'], $translationId);
    }
}
