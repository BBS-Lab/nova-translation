<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Auto-synced models
    |--------------------------------------------------------------------------
    |
    | Here you must define the list of models that will be observed by the
    | LocaleObserver in order to sync translation depending on the creation,
    | deletion of locales.
    |
    */

    'auto_synced_models' => [
        \BBSLab\NovaTranslation\Models\Label::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Use default Locale Nova resource
    |--------------------------------------------------------------------------
    |
    | Here you must define if you want to use the provided Locale Nova resource
    | and the group you want to use in the navigation bar.
    |
    */

    'use_default_locale_resource' => true,

    'default_locale_resource_group' => null,
];
