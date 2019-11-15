<?php

namespace BBS\Nova\Translation\Tools;

use BBS\Nova\Translation\ServiceProvider;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool;

class TranslationMatrix extends Tool
{
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        Nova::script(ServiceProvider::PACKAGE_ID, __DIR__ . '/../../../../../dist/js/tool.js');
        Nova::style(ServiceProvider::PACKAGE_ID, __DIR__ . '/../../../../../dist/css/tool.css');
    }
}
