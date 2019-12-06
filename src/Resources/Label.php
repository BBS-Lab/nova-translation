<?php

namespace BBSLab\NovaTranslation\Resources;

use BBSLab\NovaTranslation\Fields\Translation;
use BBSLab\NovaTranslation\Models\Label as Model;
use BBSLab\NovaTranslation\NovaTranslationServiceProvider;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Resource;

class Label extends TranslatableResource
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
    public static function label()
    {
        return trans(NovaTranslationServiceProvider::PACKAGE_ID.'::lang.labels.resources');
    }

    /**
     * {@inheritdoc}
     */
    public static function singularLabel()
    {
        return trans(NovaTranslationServiceProvider::PACKAGE_ID.'::lang.labels.resource');
    }

    /**
     * {@inheritdoc}
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),

            Translation::make(),

            Select::make(trans(NovaTranslationServiceProvider::PACKAGE_ID.'::lang.labels.type'), 'type')
                ->sortable()
                ->options([
                    Model::TYPE_TEXT => trans(NovaTranslationServiceProvider::PACKAGE_ID.'::lang.labels.types.'.Model::TYPE_TEXT),
                    Model::TYPE_UPLOAD => trans(NovaTranslationServiceProvider::PACKAGE_ID.'::lang.labels.types.'.Model::TYPE_UPLOAD),
                ])
                ->displayUsingLabels(),

            Text::make(trans(NovaTranslationServiceProvider::PACKAGE_ID.'::lang.labels.key'), 'key')
                ->sortable()
                ->rules('required'),

            Textarea::make(trans(NovaTranslationServiceProvider::PACKAGE_ID.'::lang.labels.value'), 'value')
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
