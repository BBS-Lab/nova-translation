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
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveAll(Request $request)
    {
        $translations = $request->input('translations', []);
        $savedLabels = [];
        $errors = [];

        try {
            DB::beginTransaction();

            // Group translations by key to process them together
            $translationsByKey = collect($translations)->groupBy('key');

            foreach ($translationsByKey as $key => $keyTranslations) {
                // Process first translation to create the label if needed
                $firstTranslation = $keyTranslations->first();

                if (empty($firstTranslation['key']) || empty($firstTranslation['type']) || !isset($firstTranslation['value']) || empty($firstTranslation['locale_id'])) {
                    $errors[] = [
                        'key' => $firstTranslation['key'] ?? 'unknown',
                        'error' => 'Missing required fields',
                    ];

                    continue;
                }

                try {
                    // Save first translation (this will create the label if it doesn't exist)
                    $firstLabel = $this->saveTranslation(
                        $firstTranslation['key'],
                        $firstTranslation['type'],
                        $firstTranslation['value'],
                        $firstTranslation['locale_id']
                    );
                    $savedLabels[] = $firstLabel;

                    // Process remaining translations for the same key
                    foreach ($keyTranslations->skip(1) as $translation) {
                        if (empty($translation['key']) || empty($translation['type']) || !isset($translation['value']) || empty($translation['locale_id'])) {
                            $errors[] = [
                                'key' => $translation['key'] ?? 'unknown',
                                'error' => 'Missing required fields',
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
                                'error' => $e->getMessage(),
                            ];
                        }
                    }
                } catch (\Exception $e) {
                    $errors[] = [
                        'key' => $firstTranslation['key'],
                        'error' => $e->getMessage(),
                    ];
                }
            }

            DB::commit();

            return response()->json([
                'success' => empty($errors),
                'labels' => $savedLabels,
                'errors' => $errors,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    /**
     * Save a single translation.
     *
     * @param  string  $key
     * @param  string  $type
     * @param  string  $value
     * @param  int  $localeId
     * @return Label
     */
    protected function saveTranslation($key, $type, $value, $localeId)
    {
        // Find existing translation for this key to get translation_id and source
        $existingTranslation = Translation::query()
            ->select('translations.translation_id', 'translations.translatable_source')
            ->join('labels', 'translations.translatable_id', '=', 'labels.id')
            ->where('translations.translatable_type', '=', nova_translation()->labelModel())
            ->where('labels.key', '=', $key)
            ->first();

        // Find the label for this specific locale_id by joining with translations table
        // This ensures we get the correct label for the specific locale
        // When observer creates labels for other locales, each locale gets its own label
        $label = nova_translation()->labelModel()::query()
            ->join('translations', function ($join) use ($localeId) {
                $join->on('labels.id', '=', 'translations.translatable_id')
                    ->where('translations.translatable_type', '=', nova_translation()->labelModel())
                    ->where('translations.locale_id', '=', $localeId);
            })
            ->where('labels.key', '=', $key)
            ->select('labels.*')
            ->first();

        if (!$label) {
            // Label doesn't exist for this locale_id
            // Check if any label with this key exists (could be from another locale created by observer)
            $anyLabelWithKey = nova_translation()->labelModel()::query()
                ->where('key', $key)
                ->first();

            if ($anyLabelWithKey) {
                // A label with this key exists, but not linked to this locale_id
                // The observer should have created a label for this locale when the first label was created
                // Let's check if a translation entry exists for this locale_id (even if we didn't find the label above)
                // This can happen if the observer created the label but our initial query didn't find it
                $translationForLocale = Translation::query()
                    ->join('labels', 'translations.translatable_id', '=', 'labels.id')
                    ->where('translations.translatable_type', '=', nova_translation()->labelModel())
                    ->where('labels.key', '=', $key)
                    ->where('translations.locale_id', '=', $localeId)
                    ->select('labels.*')
                    ->first();

                if ($translationForLocale) {
                    // Found the label created by observer, use it
                    $label = $translationForLocale;
                } else {
                    // No label exists for this locale yet, create it
                    // Get translation_id and source from existing label to maintain consistency
                    $labelTranslation = Translation::query()
                        ->where('translatable_type', '=', nova_translation()->labelModel())
                        ->where('translatable_id', '=', $anyLabelWithKey->id)
                        ->first();

                    $translationId = $labelTranslation ? $labelTranslation->translation_id : ($existingTranslation ? $existingTranslation->translation_id : 0);
                    $sourceId = $labelTranslation ? $labelTranslation->translatable_source : ($existingTranslation ? $existingTranslation->translatable_source : $anyLabelWithKey->id);

                    // Create new label for this locale without triggering observer
                    // to prevent creating labels for all other locales
                    $label = nova_translation()->labelModel()::withoutEvents(function () use ($key, $type, $value) {
                        return nova_translation()->labelModel()::create([
                            'type' => $type,
                            'key' => $key,
                            'value' => $value,
                        ]);
                    });

                    // Create translation entry linking this label to the locale
                    $label->upsertTranslationEntry($localeId, $sourceId, $translationId);
                }
            } else {
                // No label exists at all for this key, create it
                // Use withoutEvents to prevent observer from creating duplicate labels
                // We'll create labels manually only for the locales we need
                $label = nova_translation()->labelModel()::withoutEvents(function () use ($key, $type, $value) {
                    return nova_translation()->labelModel()::create([
                        'type' => $type,
                        'key' => $key,
                        'value' => $value,
                    ]);
                });

                // Create translation entry for the requested locale
                $translationId = $existingTranslation ? $existingTranslation->translation_id : 0;
                $sourceId = $existingTranslation ? $existingTranslation->translatable_source : $label->id;
                $label->upsertTranslationEntry($localeId, $sourceId, $translationId);
            }

            $label->update(['value' => $value]);
        } else {
            // Label exists for this locale, just update the value
            $label->update([
                'value' => $value,
            ]);
        }

        return $label->fresh();
    }

    /**
     * Delete a translation key and all its translations.
     *
     * @param  string  $key
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
                    $item = $items->first();

                    // Ensure all required properties are present for frontend
                    return [
                        'key' => $item->key,
                        'type' => $item->type,
                        'value' => $item->value ?? null,
                        'locale_id' => $item->locale_id,
                    ];
                });

                $missingLocales = nova_translation()->locales()->reject(function (Locale $locale) use ($localeCollection) {
                    return $localeCollection->contains('locale_id', '=', $locale->getKey());
                });

                if ($missingLocales->isNotEmpty()) {
                    $original = $localeCollection->first();
                    $items = $localeCollection->all();

                    foreach ($missingLocales as $locale) {
                        $items[] = [
                            'key' => $original['key'] ?? null,
                            'type' => $original['type'] ?? null,
                            'value' => null,
                            'locale_id' => $locale->getKey(),
                        ];
                    }

                    $localeCollection = collect($items);
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
