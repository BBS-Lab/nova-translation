<?php

declare(strict_types=1);

namespace BBSLab\NovaTranslation\GraphQL\Directives\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait LocaleFilters
{
    public function isosFromLocaleFilters(array $args): array
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

        if (empty($isos)) {
            $isos[] = app()->getLocale();
        }

        return $isos;
    }

    public function filterLocalesQuery(Builder $query, Model $model, array $isos = []): Builder
    {
        $table = $model->getTable();

        $query = $query
            ->select($table.'.*', 'locales.iso AS locale', 'translations.translation_id')
            ->join('translations', $table.'.'.$model->getKeyName(), '=', 'translations.translatable_id')
            ->join('locales', 'translations.locale_id', '=', 'locales.id')
            ->where('translations.translatable_type', '=', $model->getMorphClass())
            ->where('locales.available_in_api', '=', true);

        if (! empty($isos)) {
            $query = $query->whereIn('locales.iso', $isos);
        }

        return $query;
    }

    protected function localeFilters(string $modelClass, array $args): Builder
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
