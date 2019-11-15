<?php

namespace BBS\Nova\Translation\Resources;

use App\Nova\Resource;
use BBS\Nova\Translation\Models\Label as Model;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;

class Label extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'BBS\\Nova\\Translation\\Models\\Label';

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

            ID::make('Translatable ID', 'translatable_id')
                ->hideFromIndex()
                ->rules('required'),

            Text::make('Key')
                ->sortable()
                ->rules('required'),

            Textarea::make('Value')
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
