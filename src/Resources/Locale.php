<?php

namespace BBSLab\NovaTranslation\Resources;

use App\Nova\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;

class Locale extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'BBSLab\\NovaTranslation\\Models\\Locale';

    /**
     * {@inheritdoc}
     */
    public static $title = 'label';

    /**
     * {@inheritdoc}
     */
    public static $search = [
        'iso',
        'label',
    ];

    /**
     * {@inheritdoc}
     */
    public static $group = null;

    /**
     * {@inheritdoc}
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),

            Text::make('ISO')
                ->sortable()
                ->rules('required', 'max:255')
                ->creationRules('unique:locales,iso')
                ->updateRules('unique:locales,iso,{{resourceId}}'),

            Text::make('Label')
                ->sortable()
                ->rules('required', 'max:255'),

            Select::make('Fallback', 'fallback_id')
                ->options($this->model()->query()->select('id', 'label')->orderBy('label', 'asc')->get()->pluck('label', 'id')->toArray())
                ->nullable()
                ->hideFromIndex()
                ->displayUsingLabels(),

            Boolean::make('Is available in API?', 'available_in_api'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function actions(Request $request)
    {
        return [];
    }
}
