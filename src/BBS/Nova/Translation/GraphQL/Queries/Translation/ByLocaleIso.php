<?php

namespace BBS\Nova\Translation\GraphQL\Queries\Translation;

use Illuminate\Database\Eloquent\Builder;

class ByLocaleIso extends ByLocale
{
    /**
     * {@inheritdoc}
     */
    protected function filterLocales(Builder $query, array $args)
    {
        if (empty($args['iso']) || ($args['iso'] === '*')) {
            //
        } else {
            $localeIsos = explode(',', trim($args['iso'], ','));
            $query = $query->whereIn('iso', $localeIsos);
        }

        return $query;
    }
}
