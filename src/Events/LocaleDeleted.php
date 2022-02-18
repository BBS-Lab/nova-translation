<?php

namespace BBSLab\NovaTranslation\Events;

class LocaleDeleted
{
    public $locale;

    public function __construct($locale)
    {
        $this->locale = $locale;
    }
}
