<?php

namespace BBSLab\NovaTranslation\Http\Controllers\TranslatableResource;

use BBSLab\NovaTranslation\Models\Translation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Actions\Actionable;
use Laravel\Nova\Actions\ActionEvent;
use Laravel\Nova\Http\Controllers\ResourceDestroyController;
use Laravel\Nova\Http\Requests\DeleteResourceRequest;

class DestroyController extends ResourceDestroyController
{
    use Traits\TranslatableController;

    public function handle(DeleteResourceRequest $request)
    {
        if (! $this->isTranslatableResource($request)) {
            return parent::handle($request);
        }

        $request->chunks(150, function ($models) use ($request) {
            $models->each(function ($model) use ($request) {
                $translatableIds = Translation::query()
                    ->select('translatable_id')
                    ->where('translatable_type', '=', $model->getMorphClass())
                    ->where('translation_id', '=', $model->translation->translation_id)
                    ->get()
                    ->pluck('translatable_id')
                    ->toArray();

                $translatedModels = $model->query()->whereIn($model->getKeyName(), $translatableIds)->get();
                foreach ($translatedModels as $translatedModel) {
                    $this->deleteSingleModel($request, $translatedModel);
                }
            });
        });
    }

    /**
     * Delete a single model (based on parent::handle() behavior).
     *
     * @param  \Laravel\Nova\Http\Requests\DeleteResourceRequest  $request
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     *
     * @throws \Exception
     */
    protected function deleteSingleModel(DeleteResourceRequest $request, Model $model)
    {
        $this->deleteFields($request, $model);

        if (in_array(Actionable::class, class_uses_recursive($model))) {
            $model->actions()->delete();
        }

        $model->delete();

        DB::table('action_events')->insert(
            ActionEvent::forResourceDelete($request->user(), collect([$model]))->map->getAttributes()->all()
        );
    }
}
