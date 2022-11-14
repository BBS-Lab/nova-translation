<?php

declare(strict_types=1);

namespace BBSLab\NovaTranslation\Http\Controllers;

use BBSLab\NovaTranslation\Tools\TranslationMatrix;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool;

class ToolController
{
    public function __invoke(NovaRequest $request): Response
    {
        /** @var ?\BBSLab\NovaTranslation\Tools\TranslationMatrix $tool */
        $tool = collect(Nova::registeredTools())->first(fn (Tool $tool) => $tool instanceof TranslationMatrix);

        return Inertia::render('TranslationMatrix');
    }
}
