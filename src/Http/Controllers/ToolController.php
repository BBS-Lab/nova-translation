<?php

declare(strict_types=1);

namespace BBSLab\NovaTranslation\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool;
use Oneduo\NovaFileManager\NovaFileManager;

class ToolController
{
    public function __invoke(NovaRequest $request): Response
    {
        /** @var ?\Oneduo\NovaFileManager\NovaFileManager $tool */
        $tool = collect(Nova::registeredTools())->first(fn (Tool $tool) => $tool instanceof NovaFileManager);

        return Inertia::render('NovaFileManager', [
            'config' => array_merge(
                [
                    'upload' => config('nova-file-manager.upload'),
                    'outdated' => $this->updateChecker(),
                    'tour' => config('nova-file-manager.tour.enabled'),
                ],
                $tool?->options(),
            ),
        ]);
    }
}
