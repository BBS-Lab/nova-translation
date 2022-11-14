<?php

declare(strict_types=1);

namespace BBSLab\NovaTranslation\Events;

class LocaleCreated
{
    public $locale;

    public function __construct($locale)
    {
        $this->locale = $locale;
    }
}
