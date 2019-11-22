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

];
