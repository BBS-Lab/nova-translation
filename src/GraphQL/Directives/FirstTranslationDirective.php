<?php

declare(strict_types=1);

namespace BBSLab\NovaTranslation\GraphQL\Directives;

use Exception;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\FieldManipulator;
use Nuwave\Lighthouse\Support\Contracts\FieldResolver;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class FirstTranslationDirective extends BaseDirective implements FieldResolver, FieldManipulator
{
    use Traits\ExtendSchemaWithLocaleFields, Traits\LocaleFilters;

    /**
     * {@inheritdoc}
     */
    public static function definition(): string
    {
        return /* @lang GraphQL */ <<<'SDL'
directive @firstTranslation(
  "Specify the class name of the model to use."
  model: String
  "Specify the GraphQL type to add the 'locale' field (if GraphQL type is different from model class basename)."
  type: String
) on FIELD_DEFINITION
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
                if (isset($args['localeFilters']) && ! empty($args['localeFilters']['locales'])) {
                    throw new Exception('Multiple locales cannot be queried on a single returned instance! You have to only use "locale" filter on your "localeFilters" parameter.');
                }

                $locale = $args['locale'] ?? app()->getLocale();

                return $resolveInfo
                    ->argumentSet
                    ->enhanceBuilder(
                        $this->getModelClass()::query()
                            ->whereRelation('translation.locale', 'iso', '=', $locale),
                        []
                    )
                    ->first();
            }
        );
    }
}
