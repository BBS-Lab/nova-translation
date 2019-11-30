<?php

namespace BBSLab\NovaTranslation\GraphQL\Directives;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\DefinedDirective;
use Nuwave\Lighthouse\Support\Contracts\FieldManipulator;
use Nuwave\Lighthouse\Support\Contracts\FieldResolver;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class AllTranslationsDirective extends BaseDirective implements FieldResolver, FieldManipulator, DefinedDirective
{
    use Traits\ExtendSchemaWithLocaleFields, Traits\LocaleFilters;

    /**
     * {@inheritdoc}
     */
    public function name(): string
    {
        return 'allTranslations';
    }

    /**
     * {@inheritdoc}
     */
    public static function definition(): string
    {
        return /* @lang GraphQL */ <<<'SDL'
directive @allTranslations(
  "Specify the class name of the model to use."
  model: String
  "Specify the GraphQL type to add the 'locale' field (if GraphQL type is different from model class basename)."
  type: String
) on ARGUMENT_DEFINITION
SDL;
    }

    /**
     * {@inheritdoc}
     */
    public function manipulateFieldDefinition(DocumentAST &$documentAST, FieldDefinitionNode &$fieldDefinition, ObjectTypeDefinitionNode &$parentType): void
    {
        $this->extendSchemaWithLocaleFields(
            $documentAST,
            $this->directiveArgValue('type', class_basename($this->getModelClass()))
        );
    }

    /**
     * {@inheritdoc}
     */
    public function resolveField(FieldValue $fieldValue): FieldValue
    {
        return $fieldValue->setResolver(
            function ($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) {
                return $resolveInfo
                    ->argumentSet
                    ->enhanceBuilder(
                        $this->localeFilters($this->getModelClass(), $args),
                        $this->directiveArgValue('scopes', [])
                    )
                    ->get();
            }
        );
    }
}
