<?php

namespace BBSLab\NovaTranslation\GraphQL\Directives\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait LocaleFilters
{
    /**
     * Get ISOS from GraphQL args.
     *
     * @param  array  $args
     * @return array
     */
    public function isosFromLocaleFilters(array $args)
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

        return $isos;
    }

    /**
     * Filter locales query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  array  $isos
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function filterLocalesQuery(Builder $query, Model $model, array $isos = [])
    {
        $table = $model->getTable();

        $query = $query
            ->select($table.'.*', 'locales.iso AS locale', 'translations.translation_id')
            ->join('translations', $table.'.'.$model->getKeyName(), '=', 'translations.translatable_id')
            ->join('locales', 'translations.locale_id', '=', 'locales.id')
            ->where('translations.translatable_type', '=', get_class($model))
            ->where('locales.available_in_api', '=', true);

        if (! empty($isos)) {
            $query = $query->whereIn('locales.iso', $isos);
        }

        return $query;
    }

    /**
     * Filter model by locale filters.
     *
     * @param  string  $modelClass
     * @param  array  $args
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function localeFilters(string $modelClass, array $args)
    {
        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass;

        return $this->filterLocalesQuery(
            $modelClass::query(),
            $model,
            $this->isosFromLocaleFilters($args)
        );
    }
}
