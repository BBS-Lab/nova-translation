<?php

namespace BBSLab\NovaTranslation\Resources;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Resource;

class Locale extends Resource
{
    public static $model = 'BBSLab\\NovaTranslation\\Models\\Locale';
    public static $title = 'label';
    public static $search = [
        'id',
        'iso',
        'label',
    ];
    public static $with = [
        'translation',
        'translations.locale',
    ];

    public static function label(): string
    {
        return trans('nova-translation::lang.locales.resources');
    }

    public static function singularLabel(): string
    {
        return trans('nova-translation::lang.locales.resource');
    }

    public function fields(Request $request): array
    {
        return [
            ID::make()->sortable(),

            Text::make('Flag', 'flag')
                ->exceptOnForms()
                ->withMeta(['indexName' => '']),

            Text::make(trans('nova-translation::lang.locales.iso'), 'iso')
                ->sortable()
                ->rules('required', 'max:255')
                ->creationRules('unique:locales,iso')
                ->updateRules('unique:locales,iso,{{resourceId}}'),

            Text::make(trans('nova-translation::lang.locales.label'), 'label')
                ->sortable()
                ->rules('required', 'max:255'),

            Select::make(trans('nova-translation::lang.locales.fallback_id'), 'fallback_id')
                ->options($this->model()->query()->select('id', 'label')->orderBy('label')->get()->pluck('label',
                    'id')->toArray())
                ->nullable()
                ->hideFromIndex()
                ->displayUsingLabels(),

            Boolean::make(trans('nova-translation::lang.locales.available_in_api'), 'available_in_api'),
        ];
    }
}
