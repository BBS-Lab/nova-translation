<?php

declare(strict_types=1);

namespace BBSLab\NovaTranslation\GraphQL\Directives;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\FieldManipulator;
use Nuwave\Lighthouse\Support\Contracts\FieldResolver;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/** @deprecated */
class AllTranslationsDirective extends BaseDirective implements FieldResolver, FieldManipulator
{
    use Traits\ExtendSchemaWithLocaleFields;
    use Traits\LocaleFilters;

    public static function definition(): string
    {
        return /* @lang GraphQL */ <<<'SDL'
directive @allTranslations(
  "Specify the class name of the model to use."
  model: String
  "Specify the GraphQL type to add the 'locale' field (if GraphQL type is different from model class basename)."
  type: String
) on FIELD_DEFINITION
SDL;
    }

    public function manipulateFieldDefinition(
        DocumentAST &$documentAST,
        FieldDefinitionNode &$fieldDefinition,
        ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode &$parentType,
    ): void {
        $this->extendSchemaWithLocaleFields(
            $documentAST,
            $this->directiveArgValue('type', class_basename($this->getModelClass()))
        );
    }

    public function resolveField(FieldValue $fieldValue): callable
    {
        return function (mixed $root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) {
            return $resolveInfo
                ->enhanceBuilder(
                    $this->localeFilters($this->getModelClass(), $args),
                    $this->directiveArgValue('scopes', []),
                    $root, $args, $context, $resolveInfo,
                )
                ->get();
        };
    }
}
