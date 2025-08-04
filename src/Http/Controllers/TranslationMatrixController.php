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
            $existingTranslation = Translation::query()
                ->select('translations.translation_id', 'translations.translatable_source')
                ->join('labels', 'translations.translatable_id', '=', 'labels.id')
                ->where('translations.translatable_type', '=', nova_translation()->labelModel())
                ->where('labels.key', '=', $key)
                ->first();

            DB::beginTransaction();

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

            if ($existingTranslation) {
                Translation::updateOrCreate(
                    [
                        'translatable_id' => $label->id,
                        'translatable_type' => nova_translation()->labelModel(),
                        'locale_id' => $localeId,
                    ],
                    [
                        'translation_id' => $existingTranslation->translation_id,
                        'translatable_source' => $existingTranslation->translatable_source,
                    ]
                );
            } else {
                $translationId = (new Label)->freshTranslationId();
                Translation::create([
                    'locale_id' => $localeId,
                    'translation_id' => $translationId,
                    'translatable_id' => $label->id,
                    'translatable_type' => nova_translation()->labelModel(),
                    'translatable_source' => $label->id, // This label becomes the source
                ]);
            }

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
                Translation::where('translatable_id', $label->id)
                    ->where('translatable_type', nova_translation()->labelModel())
                    ->delete();
                
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