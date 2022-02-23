<?php

namespace BBSLab\NovaTranslation\Tools;

use Laravel\Nova\Fields\Field;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool;

class TranslationMatrix extends Tool
{
    public function boot(): void
    {
        $map = array_map(function ($field) {
            return tap($field, function ($field) {
                if ($field instanceof Field) {
                    $field->name = trans('nova-translation::lang.fields.value');
                }
            });
        }, config('nova-translation.map') ?? []);

        Nova::script('nova-translation', __DIR__.'/../../dist/js/nova-translation.js');
        Nova::style('nova-translation', __DIR__.'/../../dist/css/nova-translation.css');

        Nova::provideToScript([
            'nova_translation' => [
                'map' => $map,
            ],
        ]);
    }
}
