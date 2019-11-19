<?php

namespace BBS\Nova\Translation\GraphQL\Queries\Translation;

use Illuminate\Database\Eloquent\Builder;

class ByLocaleId extends ByLocale
{
    /**
     * {@inheritdoc}
     */
    protected function filterLocales(Builder $query, array $args)
    {
        if (empty($args['id']) || ($args['id'] === '*')) {
            //
        } else {
            $localeIds = explode(',', trim($args['id'], ','));
            $query = $query->whereIn('id', $localeIds);
        }

        return $query;
    }
}
