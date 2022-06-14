<?php

namespace BBSLab\NovaTranslation\Http\Controllers\TranslatableResource;

use BBSLab\NovaTranslation\Models\Translation;
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
        if (! $this->isTranslatableResource($request)) {
            return parent::handle($request);
        }

        // Inherited from parent controller
        [$model, $resource] = DB::transaction(function () use ($request) {
            $model = $request->findModelQuery()->lockForUpdate()->firstOrFail();

            $resource = $request->newResourceWith($model);
            $resource->authorizeToUpdate($request);
            $resource::validateForUpdate($request);

            if ($this->modelHasBeenUpdatedSinceRetrieval($request, $model)) {
                return response('', 409)->throwResponse();
            }

            [$model, $callbacks] = $resource::fillForUpdate($request, $model);

            ActionEvent::forResourceUpdate($request->user(), $model)->save();

            $model->save();

            collect($callbacks)->each->__invoke();

            return [$model, $resource];
        });

        // Update translations nonTranslatable fields
        $translations = Translation::query()
            ->select('translatable_id')
            ->where('translation_id', '=', $model->translation->translation_id)
            ->where('translatable_type', '=', $model->getMorphClass())
            ->get();
        foreach ($translations as $translation) {
            $translatedModel = $resource::newModel()->find($translation->translatable_id);
            foreach ($model->getNonTranslatable() as $field) {
                $translatedModel->$field = $model->$field;
            }
            $translatedModel->save();
        }

        // Inherited from parent controller
        return response()->json([
            'id' => $model->getKey(),
            'resource' => $model->attributesToArray(),
            'redirect' => $resource::redirectAfterUpdate($request, $resource),
        ]);
    }
}
