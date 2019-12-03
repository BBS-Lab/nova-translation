<?php

namespace BBSLab\NovaTranslation\Http\Controllers\TranslatableResource;

use Laravel\Nova\Http\Controllers\ResourceUpdateController;
use Laravel\Nova\Http\Requests\UpdateResourceRequest;

class UpdateController extends ResourceUpdateController
{
    /**
     * {@inheritdoc}
     */
    public function handle(UpdateResourceRequest $request)
    {
        return parent::handle($request);
    }
}
