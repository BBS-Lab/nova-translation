<?php

namespace BBSLab\NovaTranslation\Http\Controllers\TranslatableResource;

use Laravel\Nova\Http\Controllers\ResourceStoreController;
use Laravel\Nova\Http\Requests\CreateResourceRequest;

class StoreController extends ResourceStoreController
{
    /**
     * {@inheritdoc}
     */
    public function handle(CreateResourceRequest $request)
    {
        return parent::handle($request);
    }
}
