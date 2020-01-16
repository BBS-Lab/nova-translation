<?php

namespace BBSLab\NovaTranslation\Http\Controllers\TranslatableResource;

use BBSLab\NovaTranslation\Models\Locale;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Actions\ActionEvent;
use Laravel\Nova\Http\Controllers\ResourceStoreController;
use Laravel\Nova\Http\Requests\CreateResourceRequest;

class StoreController extends ResourceStoreController
{
    use Traits\TranslatableController;

    /**
     * {@inheritdoc}
     */
    public function handle(CreateResourceRequest $request)
    {
        if (! $this->isTranslatableResource($request)) {
            return parent::handle($request);
        }

        // Inherited from parent controller
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

        // Create base translation
        $currentLocale = Locale::query()->select('id')->where('iso', '=', app()->getLocale())->first();
        $baseTranslation = $model->upsertTranslationEntry($currentLocale->id, 0);

        // Create base model
        $otherLocales = Locale::query()->select('id')->where('id', '!=', $currentLocale->id)->get();
        foreach ($otherLocales as $otherLocale) {
            $otherModel = $resource::newModel();
            foreach ($model->getOnCreateTranslatable() as $field) {
                $otherModel->$field = $model->$field;
            }
            $otherModel->save();
            $otherModel->upsertTranslationEntry($otherLocale->id, $baseTranslation->translation_id);
        }

        // Inherited from parent controller
        return response()->json([
            'id' => $model->getKey(),
            'resource' => $model->attributesToArray(),
            'redirect' => $resource::redirectAfterCreate($request, $request->newResourceWith($model)),
        ], 201);
    }
}
