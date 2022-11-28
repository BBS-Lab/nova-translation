<?php

declare(strict_types=1);

namespace BBSLab\NovaTranslation\Http\Middleware;

use BBSLab\NovaTranslation\Models\Contracts\IsTranslatable;
use Closure;
use Exception;
use Laravel\Nova\Http\Requests\NovaRequest;

class ChangeLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /** @var \Laravel\Nova\Http\Requests\NovaRequest $novaRequest */
        $novaRequest = app(NovaRequest::class);

        if ($novaRequest->viaRelationship()) {
            $this->checkViaRelationship($novaRequest);
        } else {
            $this->checkUpdate($novaRequest);
        }

        return $next($request);
    }

    protected function checkViaRelationship(NovaRequest $request): void
    {
        $parent = $request->findParentModel();

        if (empty($parent) || !$parent instanceof IsTranslatable) {
            return;
        }

        app()->setLocale($parent->translation->locale->iso);
    }

    protected function checkUpdate(NovaRequest $request): void
    {
        try {
            $model = $request->findModelOrFail();

            if (empty($model) || !$model instanceof IsTranslatable) {
                return;
            }

            app()->setLocale($model->translation->locale->iso);
        } catch (Exception $exception) {
        }
    }
}
