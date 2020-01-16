<?php

namespace BBSLab\NovaTranslation\Models\Observers;

use BBSLab\NovaTranslation\Models\Locale;
use BBSLab\NovaTranslation\Models\Translation;
use BBSLab\NovaTranslation\NovaTranslationServiceProvider;

class LocaleObserver
{
    /**
     * Handle the Locale "created" event.
     *
     * @param  \BBS\Nova\Translation\Models\Locale  $locale
     * @return void
     */
    public function created(Locale $locale)
    {
        /** @var \BBSLab\NovaTranslation\Models\Locale $defaultLocale */
        $defaultLocale = Locale::query()->select('id')->availableInApi()->first();

        foreach ($this->translatableModels() as $modelClassName) {
            /** @var \Illuminate\Database\Eloquent\Model $model */
            $model = (new $modelClassName);
            $table = $model->getTable();

            $query = $model->query()
                ->select($table.'.*', 'translations.translation_id')
                ->join('translations', $table.'.'.$model->getKeyName(), '=', 'translations.translatable_id')
                ->where('translations.translatable_type', '=', $modelClassName)
                ->where('translations.locale_id', '=', $defaultLocale->id);

            foreach ($query->cursor() as $instance) {
                $data = [];
                foreach ($instance->getOnCreateTranslatable() as $field) {
                    $data[$field] = $instance->$field;
                }

                $created = $model->query()->create($data);
                $created->upsertTranslationEntry($locale->id, $instance->translation_id);
            }
        }
    }

    /**
     * Handle the Locale "deleted" event.
     *
     * @param  \BBS\Nova\Translation\Models\Locale  $locale
     * @return void
     */
    public function deleted(Locale $locale)
    {
        $query = Translation::query()
            ->where('locale_id', '=', $locale->id)
            ->whereIn('translatable_type', $this->translatableModels());

        foreach ($query->cursor() as $translation) {
            /** @var \BBSLab\NovaTranslation\Models\Translation $translation */
            $instance = (new $translation->translatable_type)->find($translation->translatable_id);

            if (! empty($instance)) {
                // Related translation is deleted by the Translatable "deleted" observer
                $instance->delete();
            }
        }
    }

    /**
     * Return list of translated models.
     *
     * @return array
     */
    protected function translatableModels()
    {
        return config(NovaTranslationServiceProvider::PACKAGE_ID.'.auto_synced_models');
    }
}
