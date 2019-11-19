<?php

namespace BBS\Nova\Translation\GraphQL\Queries\Translation;

use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class ByLocaleIso
{
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
        // @TODO...
        dump($args);

        return [
            'json' => [],
        ];
    }
}
