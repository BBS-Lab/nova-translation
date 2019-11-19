<?php

namespace BBS\Nova\Translation\GraphQL\Scalars;

use GraphQL\Type\Definition\ScalarType;

class TranslationJson extends ScalarType
{
    /**
     * {@inheritdoc}
     */
    public function serialize($value)
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function parseValue($value)
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function parseLiteral($valueNode, ?array $variables = null)
    {
        return $valueNode->value;
    }
}
