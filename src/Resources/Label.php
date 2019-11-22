<?php

namespace BBSLab\NovaTranslation\Resources;

use App\Nova\Resource;
use BBSLab\NovaTranslation\Models\Label as Model;
use BBSLab\NovaTranslation\NovaTranslationServiceProvider;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;

class Label extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'BBSLab\\NovaTranslation\\Models\\Label';

    /**
     * {@inheritdoc}
     */
    public static $title = 'key';

    /**
     * {@inheritdoc}
     */
    public static $search = [
        'key',
        'value',
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

            Select::make('Type', 'type')
                ->sortable()
                ->options([
                    Model::TYPE_TEXT => trans(NovaTranslationServiceProvider::PACKAGE_ID.'.labels.types.'.Model::TYPE_TEXT),
                    Model::TYPE_UPLOAD => trans(NovaTranslationServiceProvider::PACKAGE_ID.'.labels.types.'.Model::TYPE_UPLOAD),
                ])
                ->displayUsingLabels(),

            Text::make('Key', 'key')
                ->sortable()
                ->rules('required'),

            Textarea::make('Value', 'value')
                ->hideFromIndex(),
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
