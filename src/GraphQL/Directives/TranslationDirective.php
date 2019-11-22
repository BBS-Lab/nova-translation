<?php

namespace BBSLab\NovaTranslation\GraphQL\Directives;

use GraphQL\Language\AST\ArgumentNode;
use GraphQL\Language\AST\DirectiveNode;
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
                $type->fields = ASTHelper::mergeNodeList($type->fields, [
                    $this->defineLocaleField(),
                    $this->defineTranslationIdField(),
                ]);
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

                /** @var \Illuminate\Database\Eloquent\Model $model */
                $model = new $modelClass;
                $table = $model->getTable();
                $query = $modelClass::query()
                    ->select($table.'.*', 'locales.iso AS locale', 'translations.translation_id')
                    ->join('translations', $table.'.'.$model->getKeyName(), '=', 'translations.translatable_id')
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

    /**
     * Setup "translationId" field definition.
     *
     * @return \GraphQL\Language\AST\FieldDefinitionNode
     */
    protected function defineTranslationIdField()
    {
        return new FieldDefinitionNode([
            'name' => new NameNode(['value' => 'translationId']),
            'type' => new NonNullTypeNode(['type' => new NamedTypeNode(['name' => new NameNode(['value' => 'Int'])])]),
            'arguments' => new NodeList([]),
            'directives' => new NodeList([
                new DirectiveNode([
                    'name' => new NameNode(['value' => 'rename']),
                    'arguments' => new NodeList([
                        new ArgumentNode([
                            'name' => new NameNode(['value' => 'attribute']),
                            'value' => new StringValueNode(['value' => 'translation_id', 'block' => false]),
                        ]),
                    ]),
                ]),
            ]),
            'description' => new StringValueNode(['value' => 'Item translation ID', 'block' => false]),
        ]);
    }
}
