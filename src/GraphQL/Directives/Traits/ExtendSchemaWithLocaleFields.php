<?php

declare(strict_types=1);

namespace BBSLab\NovaTranslation\GraphQL\Directives\Traits;

use GraphQL\Language\AST\ArgumentNode;
use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\AST\NameNode;
use GraphQL\Language\AST\NodeList;
use GraphQL\Language\AST\NonNullTypeNode;
use GraphQL\Language\AST\StringValueNode;
use Nuwave\Lighthouse\Schema\AST\ASTHelper;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;

trait ExtendSchemaWithLocaleFields
{
    /**
     * Extend GraphQL schema for given type with "locales" fields.
     *
     * @param  \Nuwave\Lighthouse\Schema\AST\DocumentAST  $documentAST
     * @param  string  $typeToExtend
     * @return void
     */
    protected function extendSchemaWithLocaleFields(DocumentAST &$documentAST, string $typeToExtend)
    {
        foreach ($documentAST->types as &$type) {
            if ($type->name->value === $typeToExtend) {
                /* @var \GraphQL\Language\AST\ObjectTypeDefinitionNode $type */
                $type->fields = ASTHelper::mergeUniqueNodeList($type->fields, [
                    $this->defineLocaleField(),
                    $this->defineTranslationIdField(),
                ], true);
            }
        }
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
