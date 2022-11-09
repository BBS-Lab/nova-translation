<?php

namespace BBSLab\NovaTranslation\Tools;

use Illuminate\Http\Request;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool;

class TranslationMatrix extends Tool
{
    public function boot(): void
    {
        Nova::script('nova-translation', __DIR__.'/../../dist/js/nova-translation.js');
        Nova::style('nova-translation', __DIR__.'/../../dist/css/nova-translation.css');
    }

    public function menu(Request $request)
    {
        return MenuSection::make('Translations')
            ->path('/nova-translation')
            ->icon('server');
    }
}
