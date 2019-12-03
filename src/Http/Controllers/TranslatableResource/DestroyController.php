<?php

namespace BBSLab\NovaTranslation\Http\Controllers\TranslatableResource;

use Laravel\Nova\Http\Controllers\ResourceDestroyController;
use Laravel\Nova\Http\Requests\DeleteResourceRequest;

class DestroyController extends ResourceDestroyController
{
    /**
     * {@inheritdoc}
     */
    public function handle(DeleteResourceRequest $request)
    {
        return parent::handle($request);
    }
}
