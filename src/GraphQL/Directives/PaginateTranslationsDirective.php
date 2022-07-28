<?php

namespace BBSLab\NovaTranslation\GraphQL\Directives;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Laravel\Scout\Builder as ScoutBuilder;
use Nuwave\Lighthouse\Pagination\PaginateDirective;
use Nuwave\Lighthouse\Pagination\PaginationArgs;
use Nuwave\Lighthouse\Pagination\PaginationManipulator;
use Nuwave\Lighthouse\Pagination\PaginationType;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class PaginateTranslationsDirective extends PaginateDirective
{
    use Traits\LocaleFilters;

    /**
     * {@inheritdoc}
     */
    public function name(): string
    {
        return 'paginateTranslations';
    }

    /**
     * {@inheritdoc}
     */
    public static function definition(): string
    {
        return /* @lang GraphQL */ <<<'SDL'
directive @paginateTranslations(
  "Specify the class name of the model to use."
  model: String
  "Specify the GraphQL type to add the 'locale' field (if GraphQL type is different from model class basename)."
  type: String
  "Which pagination style to use. Allowed values: paginator, connection."
  paginatorType: String = "paginator"
  "Apply scopes to the underlying query."
  scopes: [String!]
  "Overwrite the paginate_max_count setting value to limit the amount of items that a user can request per page."
  maxCount: Int
  "Use a default value for the amount of returned items in case the client does not request it explicitly?."
  defaultCount: Int
) on FIELD_DEFINITION
SDL;
    }

    /**
     * {@inheritdoc}
     */
    public function manipulateFieldDefinition(DocumentAST &$documentAST, FieldDefinitionNode &$fieldDefinition, ObjectTypeDefinitionNode &$parentType): void
    {
        $paginationManipulator = new PaginationManipulator($documentAST);

        $paginationManipulator
            ->setModelClass($this->getModelClass())
            ->transformToPaginatedField(
                $this->paginationType(),
                $fieldDefinition,
                $parentType,
                $this->directiveArgValue('defaultCount'),
                $this->paginateMaxCount()
            );
    }

    /**
     * {@inheritdoc}
     */
    public function resolveField(FieldValue $fieldValue): FieldValue
    {
        return $fieldValue->setResolver(
            function ($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): LengthAwarePaginator {
                $paginationArgs = PaginationArgs::extractArgs($args, $this->paginationType(), $this->paginateMaxCount());
                
                $first = $paginationArgs->first;
                $page = $paginationArgs->page;

                $query = $resolveInfo
                    ->argumentSet
                    ->enhanceBuilder(
                        $this->localeFilters($this->getModelClass(), $args),
                        $this->directiveArgValue('scopes', [])
                    );

                if ($query instanceof ScoutBuilder) {
                    return $query->paginate($first, 'page', $page);
                }

                return $query->paginate($first, ['*'], 'page', $page);
            }
        );
    }

    protected function paginationType(): PaginationType
    {
        return new PaginationType(
            $this->directiveArgValue('paginatorType', PaginationType::PAGINATOR)
        );
    }
}
