<?php

namespace BBSLab\NovaTranslation\Resources;

use BBSLab\NovaTranslation\Fields\Translation;
use BBSLab\NovaTranslation\NovaTranslationServiceProvider;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;

class Label extends TranslatableResource
{
    public static $model = 'BBSLab\\NovaTranslation\\Models\\Label';
    public static $title = 'key';
    public static $search = [
        'id',
        'key',
        'value',
    ];
    public static $with = [
        'translation',
        'translations.locale',
    ];

    public static function label(): string
    {
        return trans('nova-translation::lang.labels.resources');
    }

    public static function singularLabel(): string
    {
        return trans('nova-translation::lang.labels.resource');
    }

    public function fields(Request $request): array
    {
        return [
            ID::make()->sortable(),

            Translation::make(),

            Text::make(trans('nova-translation::lang.labels.key'), 'key')
                ->sortable()
                ->rules('required'),

            Textarea::make(trans('nova-translation::lang.labels.value'), 'value')
                ->hideFromIndex(),
        ];
    }

    public static function newModel()
    {
        return tap(parent::newModel(), function ($model) {
            $model->type = 'text';
        });
    }
}
