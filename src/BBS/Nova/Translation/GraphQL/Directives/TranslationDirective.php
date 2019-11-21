<?php

namespace BBS\Nova\Translation\GraphQL\Directives;

use BBS\Nova\Translation\Models\Scopes\TranslatableScope;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\AST\NameNode;
use GraphQL\Language\AST\NodeList;
use GraphQL\Language\AST\NonNullTypeNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Schema\AST\ASTHelper;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\DefinedDirective;
use Nuwave\Lighthouse\Support\Contracts\FieldManipulator;
use Nuwave\Lighthouse\Support\Contracts\FieldResolver;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class TranslationDirective extends BaseDirective implements FieldResolver, FieldManipulator, DefinedDirective
{
    /**
     * {@inheritdoc}
     */
    public function name(): string
    {
        return 'translation';
    }

    /**
     * {@inheritdoc}
     */
    public static function definition(): string
    {
        return /* @lang GraphQL */ <<<'SDL'
directive @translation(
  "Specify the class name of the model to use."
  model: String
  "Specify the GraphQL type to add the 'locale' field."
  type: String
) on ARGUMENT_DEFINITION
SDL;
    }

    /**
     * {@inheritdoc}
     */
    public function manipulateFieldDefinition(DocumentAST &$documentAST, FieldDefinitionNode &$fieldDefinition, ObjectTypeDefinitionNode &$parentType): void
    {
        $typeToAddLocaleField = $this->directiveArgValue('type', class_basename($this->getModelClass()));

        foreach ($documentAST->types as &$type) {
            if ($type->name->value === $typeToAddLocaleField) {
                /* @var \GraphQL\Language\AST\ObjectTypeDefinitionNode $type */
                $type->fields = ASTHelper::mergeNodeList($type->fields, [$this->defineLocaleField()]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function resolveField(FieldValue $fieldValue): FieldValue
    {
        return $fieldValue->setResolver(
            function ($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) {
                $modelClass = $this->getModelClass();

                $isos = [];
                if (isset($args['localeFilters'])) {
                    if (isset($args['localeFilters']['locale'])) {
                        $isos[] = $args['localeFilters']['locale'];
                    }
                    if (isset($args['localeFilters']['locales'])) {
                        $isos = array_merge($isos, $args['localeFilters']['locales']);
                    }
                }

                $table = (new $modelClass)->getTable();
                $query = $modelClass::query()
                    ->withoutGlobalScope(TranslatableScope::class)
                    ->select($table.'.type', $table.'.key', $table.'.value', 'locales.iso AS locale', $table.'.created_at', $table.'.updated_at')
                    ->join('translations', $table.'.id', '=', 'translations.translatable_id')
                    ->join('locales', 'translations.locale_id', '=', 'locales.id')
                    ->where('translations.translatable_type', '=', $modelClass)
                    ->where('locales.available_in_api', '=', true);

                if (! empty($isos)) {
                    $query = $query->whereIn('locales.iso', $isos);
                }

                return $query->get();
            }
        );
    }

    /**
     * Setup "locale" field definition.
     *
     * @return \GraphQL\Language\AST\FieldDefinitionNode
     */
    protected function defineLocaleField()
    {
        return new FieldDefinitionNode([
            'name' => new NameNode(['value' => 'locale']),
            'type' => new NonNullTypeNode(['type' => new NamedTypeNode(['name' => new NameNode(['value' => 'String'])])]),
            'arguments' => new NodeList([]),
            'directives' => new NodeList([]),
            'description' => new StringValueNode(['value' => 'Locale ISO', 'block' => false]),
        ]);
    }
}
