<?php

namespace BBSLab\NovaTranslation\Models;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class TranslationRelation extends Relation
{
    /**
     * @var \BBSLab\NovaTranslation\Models\Translation|\Illuminate\Database\Eloquent\Builder
     */
    protected $query;

    /**
     * @var \BBSLab\NovaTranslation\Models\Contracts\IsTranslatable
     */
    protected $parent;

    /**
     * @var \BBSLab\NovaTranslation\Models\Translation
     */
    protected $related;

    public function __construct(Model $parent)
    {
        parent::__construct(Translation::query(), $parent);
    }

    public function getRelationExistenceQuery(EloquentBuilder $query, EloquentBuilder $parentQuery, $columns = ['*'])
    {
        return $query
            ->select($columns)
            ->whereColumn('translations.translatable_id', '=', $this->getQualifiedParentKeyName())
            ->where('translations.translatable_type', '=', $this->parent->getMorphClass());
    }

    public function addConstraints()
    {
        if (static::$constraints) {
            $this->query
                ->where('translatable_id', '<>', $this->parent->getKey())
                ->where('translatable_type', '=', $this->parent->getMorphClass())
                ->whereExists(function (Builder $query) {
                    $query->select(DB::raw(1))
                        ->from('translations as original')
                        ->whereRaw('original.translation_id = translations.translation_id')
                        ->where('original.translatable_id', '=', $this->parent->getKey())
                        ->where('original.translatable_type', '=', $this->parent->getMorphClass());
                });
        }
    }

    public function addEagerConstraints(array $models)
    {
        $ids = collect($models)->pluck($this->parent->getKeyName());

        $this->query
            ->whereNotIn('translatable_id', $ids)
            ->where('translatable_type', '=', $this->parent->getMorphClass())
            ->whereExists(function (Builder $query) use ($ids) {
                $query->select(DB::raw(1))
                    ->from('translations as original')
                    ->whereRaw('translations.translation_id = original.translation_id')
                    ->whereIn('original.translatable_id', $ids)
                    ->where('original.translatable_type', '=', $this->parent->getMorphClass());
            });
    }

    public function initRelation(array $models, $relation): array
    {
        foreach ($models as $model) {
            /** @var \BBSLab\NovaTranslation\Models\Contracts\IsTranslatable $model */
            $model->setRelation($relation, $this->related->newCollection());
        }

        return $models;
    }

    public function match(array $models, Collection $results, $relation): array
    {
        if ($results->isEmpty()) {
            return $models;
        }

        foreach ($models as $model) {
            /** @var \BBSLab\NovaTranslation\Models\Contracts\IsTranslatable $model */
            $model->setRelation(
                $relation,
                $results->filter(function (Translation $translation) use ($model) {
                    return $translation->translation_id === $model->translation->translation_id
                        && $translation->translatable_id !== $model->getKey();
                })
            );
        }

        return $models;
    }

    public function getResults()
    {
        return ! is_null($this->getParent()->getKey())
            ? $this->query->get()
            : $this->related->newCollection();
    }
}
