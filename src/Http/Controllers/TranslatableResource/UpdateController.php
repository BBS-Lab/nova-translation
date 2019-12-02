<?php

namespace BBSLab\NovaTranslation\Http\Controllers\TranslatableResource;

use BBSLab\NovaTranslation\Resources\TranslatableResource;
use Laravel\Nova\Http\Controllers\ResourceUpdateController;
use Laravel\Nova\Http\Requests\UpdateResourceRequest;

class UpdateController extends ResourceUpdateController
{
    /**
     * {@inheritdoc}
     */
    public function handle(UpdateResourceRequest $request)
    {
        $resource = $request->resource();
        if ($resource instanceof TranslatableResource) {
            dd($resource, $request);
        } else {
            return parent::handle($request);
        }
    }
}
