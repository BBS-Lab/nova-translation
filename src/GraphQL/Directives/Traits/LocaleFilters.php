<?php

namespace BBSLab\NovaTranslation\GraphQL\Directives\Traits;

trait LocaleFilters
{
    /**
     * Filter model by locale filters.
     *
     * @param  string  $modelClass
     * @param  array  $args
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function localeFilters(string $modelClass, array $args)
    {
        $isos = [];
        if (isset($args['localeFilters'])) {
            if (isset($args['localeFilters']['locale'])) {
                $isos[] = $args['localeFilters']['locale'];
            }
            if (isset($args['localeFilters']['locales'])) {
                $isos = array_merge($isos, $args['localeFilters']['locales']);
            }
        }

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass;
        $table = $model->getTable();

        $query = $modelClass::query()
            ->select($table.'.*', 'locales.iso AS locale', 'translations.translation_id')
            ->join('translations', $table.'.'.$model->getKeyName(), '=', 'translations.translatable_id')
            ->join('locales', 'translations.locale_id', '=', 'locales.id')
            ->where('translations.translatable_type', '=', $modelClass)
            ->where('locales.available_in_api', '=', true);

        if (! empty($isos)) {
            $query = $query->whereIn('locales.iso', $isos);
        }

        return $query;
    }
}
