<?php

declare(strict_types=1);

use BBSLab\NovaTranslation\NovaTranslation;

if (! function_exists('nova_translation')) {
    function nova_translation(): NovaTranslation
    {
        return app(NovaTranslation::class);
    }
}
