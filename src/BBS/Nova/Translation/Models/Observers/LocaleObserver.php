<?php

namespace BBS\Nova\Translation\Models\Observers;

use BBS\Nova\Translation\Models\Locale;
use BBS\Nova\Translation\Models\Scopes\TranslatableScope;
use BBS\Nova\Translation\Models\Translation;
use BBS\Nova\Translation\ServiceProvider;

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
        foreach ($this->translatableModels() as $modelClassName) {
            /** @var \Illuminate\Database\Eloquent\Model $model */
            $model = (new $modelClassName);

            foreach ($model->query()->cursor() as $instance) {
                $data = [];
                foreach ($instance->getNonTranslatable() as $field) {
                    $data[$field] = $instance->$field;
                }

                $created = $model->query()->create($data);
                $created->createTranslationEntry($locale->id);
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
            /** @var \BBS\Nova\Translation\Models\Translation $translation */
            $instance = (new $translation->translatable_type)
                ->newQueryWithoutScope(TranslatableScope::class)
                ->find($translation->translatable_id);

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
        return config(ServiceProvider::PACKAGE_ID.'.auto_synced_models');
    }
}
