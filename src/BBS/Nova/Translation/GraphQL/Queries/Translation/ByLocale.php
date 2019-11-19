<?php

namespace BBS\Nova\Translation\GraphQL\Queries\Translation;

use BBS\Nova\Translation\Models\Label;
use BBS\Nova\Translation\Models\Locale;
use BBS\Nova\Translation\Models\Scopes\TranslatableScope;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Builder;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

abstract class ByLocale
{
    /**
     * Filter locales Eloquent Builder depending of given arguments.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $args
     * @return \Illuminate\Database\Eloquent\Builder
     */
    abstract protected function filterLocales(Builder $query, array $args);

    /**
     * Return a value for the field.
     *
     * @param  mixed  $rootValue
     * @param  array  $args
     * @param  \Nuwave\Lighthouse\Support\Contracts\GraphQLContext  $context
     * @param  \GraphQL\Type\Definition\ResolveInfo  $resolveInfo
     * @return array
     */
    public function resolve($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $query = Locale::query()->availableInApi()->select('id', 'iso');
        $query = $this->filterLocales($query, $args);
        $locales = $query->get()->pluck('iso', 'id')->toArray();

        $labels = Label::query()
            ->withoutGlobalScope(TranslatableScope::class)
            ->select('labels.key', 'labels.value', 'translations.locale_id')
            ->join('translations', 'labels.id', '=', 'translations.translatable_id')
            ->where('translations.translatable_type', '=', Label::class)
            ->whereIn('translations.locale_id', array_keys($locales))
            ->get();

        $json = [];
        foreach ($labels as $label) {
            $iso = $locales[$label->locale_id];
            if (! isset($json[$iso])) {
                $json[$iso] = [];
            }
            $json[$iso][$label->key] = $label->value;
        }

        return [
            'json' => $json,
        ];
    }
}
