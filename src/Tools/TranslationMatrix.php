<?php

namespace BBSLab\NovaTranslation\Tools;

use Laravel\Nova\Nova;
use Laravel\Nova\Tool;

class TranslationMatrix extends Tool
{
    public function boot(): void
    {
        Nova::script('nova-translation', __DIR__.'/../../dist/js/nova-translation.js');
        Nova::style('nova-translation', __DIR__.'/../../dist/css/nova-translation.css');
    }
}
