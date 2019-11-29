<?php

namespace BBSLab\NovaTranslation\Http\Controllers\TranslatableResource;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Actions\ActionEvent;
use Laravel\Nova\Http\Requests\CreateResourceRequest;

class StoreController extends ResourceStoreController
{
    /**
     * {@inheritdoc}
     */
    public function handle(CreateResourceRequest $request)
    {
        $resource = $request->resource();

        $resource::authorizeToCreate($request);

        $resource::validateForCreation($request);

        $model = DB::transaction(function () use ($request, $resource) {
            [$model, $callbacks] = $resource::fill(
                $request, $resource::newModel()
            );

            if ($request->viaRelationship()) {
                $request->findParentModelOrFail()
                    ->{$request->viaRelationship}()
                    ->save($model);
            } else {
                $model->save();
            }

            ActionEvent::forResourceCreate($request->user(), $model)->save();

            collect($callbacks)->each->__invoke();

            return $model;
        });

        return response()->json([
            'id' => $model->getKey(),
            'resource' => $model->attributesToArray(),
            'redirect' => $resource::redirectAfterCreate($request, $request->newResourceWith($model)),
        ], 201);
    }
}
