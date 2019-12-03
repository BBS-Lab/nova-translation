<?php

namespace BBSLab\NovaTranslation\Http\Controllers\TranslatableResource;

use BBSLab\NovaTranslation\Models\Translation;
use BBSLab\NovaTranslation\Resources\TranslatableResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Actions\Actionable;
use Laravel\Nova\Actions\ActionEvent;
use Laravel\Nova\Contracts\Deletable;
use Laravel\Nova\DeleteField;
use Laravel\Nova\Http\Controllers\ResourceDestroyController;
use Laravel\Nova\Http\Requests\DeleteResourceRequest;
use Laravel\Nova\Http\Requests\NovaRequest;

class DestroyController extends ResourceDestroyController
{
    /**
     * {@inheritdoc}
     */
    public function handle(DeleteResourceRequest $request)
    {
        $resource = $request->resource();

        if (is_subclass_of($resource, TranslatableResource::class)) {
            $request->chunks(150, function ($models) use ($request) {
                $models->each(function ($model) use ($request) {
                    $translatableIds = Translation::query()
                        ->select('translatable_id')
                        ->where('translatable_type', '=', get_class($model))
                        ->where('translation_id', '=', $model->translation->translation_id)
                        ->get()
                        ->pluck('translatable_id')
                        ->toArray();

                    $translatedModels = $model->query()->whereIn($model->getKeyName(), $translatableIds)->get();
                    foreach ($translatedModels as $translatedModel) {
                        $this->deleteSingleTranslatableModel($request, $translatedModel);
                    }
                });
            });
        } else {
            return parent::handle($request);
        }
    }

    /**
     * Delete a single model.
     *
     * @param  \Laravel\Nova\Http\Requests\DeleteResourceRequest  $request
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     * @throws \Exception
     */
    protected function deleteSingleTranslatableModel(DeleteResourceRequest $request, Model $model)
    {
        $this->deleteTranslatableModelFields($request, $model);

        if (in_array(Actionable::class, class_uses_recursive($model))) {
            $model->actions()->delete();
        }

        $model->delete();

        DB::table('action_events')->insert(
            ActionEvent::forResourceDelete($request->user(), collect([$model]))->map->getAttributes()->all()
        );
    }

    /**
     * Delete the deletable fields on the given model / resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param $model
     * @param  bool  $skipSoftDeletes
     * @return void
     */
    protected function deleteTranslatableModelFields(NovaRequest $request, $model, $skipSoftDeletes = true)
    {
        if ($skipSoftDeletes && $request->newResourceWith($model)->softDeletes()) {
            return;
        }

        // @TODO... Filter throught detailFields
        if (false) {
            $request->newResourceWith($model)
                ->detailFields($request)
                ->whereInstanceOf(Deletable::class)
                ->filter->isPrunable()
                ->each(function ($field) use ($request, $model) {
                    DeleteField::forRequest($request, $field, $model);
                });
        }
    }
}
