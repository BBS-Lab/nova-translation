<?php

namespace BBSLab\NovaTranslation\Http\Controllers\TranslatableResource;

use BBSLab\NovaTranslation\Resources\TranslatableResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Actions\ActionEvent;
use Laravel\Nova\Http\Controllers\ResourceUpdateController;
use Laravel\Nova\Http\Requests\UpdateResourceRequest;

class UpdateController extends ResourceUpdateController
{
    use Traits\TranslatableController;

    /**
     * {@inheritdoc}
     */
    public function handle(UpdateResourceRequest $request)
    {
        $resource = $request->resource();

        if (is_subclass_of($resource, TranslatableResource::class)) {
            $model = $request->findModelQuery()->lockForUpdate()->firstOrFail();
            $resource = $request->newResourceWith($model);

            $resource->authorizeToUpdate($request);
            // @TODO... Ensure updating rules
            $resource::validateForUpdate($request);

            if ($this->modelHasBeenUpdatedSinceRetrieval($request, $model)) {
                return response('', 409)->throwResponse();
            }

            DB::transaction(function () use ($request, $model, $resource) {
                $this->updateTranslatable($request, $model, $resource);
            });

            return response()->json([
                'id' => $model->getKey(),
                'resource' => $model->attributesToArray(),
                'redirect' => $resource::redirectAfterUpdate($request, $resource),
            ]);
        } else {
            return parent::handle($request);
        }
    }

    /**
     * Update translatable.
     *
     * @param  \Laravel\Nova\Http\Requests\UpdateResourceRequest  $request
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  \BBSLab\NovaTranslation\Resources\TranslatableResource  $resource
     * @return array
     */
    protected function updateTranslatable(UpdateResourceRequest $request, Model $model, TranslatableResource $resource)
    {
        $updateFields = $resource->updateFields($request);
        $translatedData = $this->mapTranslatableData($request, $updateFields);

        $translatedModels = [];
        foreach ($translatedData as $localeId => $fields) {
            $translatedModels[$localeId] = $model->newQuery()->find($fields[$model->getKeyName()]);
            unset($fields['translation_id'], $fields[$model->getKeyName()]);

            foreach ($fields as $field => $value) {
                $translatedModels[$localeId]->$field = $value;
            }

            ActionEvent::forResourceUpdate($request->user(), $translatedModels[$localeId])->save();

            $translatedModels[$localeId]->save();
        }

        return $translatedModels;
    }
}
