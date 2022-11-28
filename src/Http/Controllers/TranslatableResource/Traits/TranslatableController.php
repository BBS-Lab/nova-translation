<?php

declare(strict_types=1);

namespace BBSLab\NovaTranslation\Http\Controllers\TranslatableResource\Traits;

use BBSLab\NovaTranslation\Resources\TranslatableResource;
use Laravel\Nova\Http\Requests\NovaRequest;

trait TranslatableController
{
    /**
     * Return if current requested resource is Translatable.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return bool
     */
    protected function isTranslatableResource(NovaRequest $request)
    {
        return is_subclass_of($request->resource(), TranslatableResource::class);
    }
}
