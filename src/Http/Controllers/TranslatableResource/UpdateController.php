<?php

namespace BBSLab\NovaTranslation\Http\Controllers\TranslatableResource;

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
        if ($this->isTranslatableResource($request)) {
            // @TODO... Use nonTranslatable to override other locales
        }

        return parent::handle($request);
    }
}
