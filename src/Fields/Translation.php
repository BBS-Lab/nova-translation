<?php

namespace BBSLab\NovaTranslation\Fields;

use BBSLab\NovaTranslation\Models\Contracts\IsTranslatable;
use BBSLab\NovaTranslation\Models\Locale;
use BBSLab\NovaTranslation\Models\Translation as TranslationModel;
use BBSLab\NovaTranslation\NovaTranslation;
use Laravel\Nova\Fields\Field;

class Translation extends Field
{
    public $component = 'nova-translation-field';

    public function __construct()
    {
        $name = trans('nova-translation::lang.field');

        parent::__construct($name, 'translation');

        $this->withMeta([
            'locales' => $this->locales(),
        ]);
    }

    public function resolve($resource, $attribute = null)
    {
        $this->withMeta([
            'translations' => $resource instanceof IsTranslatable ? $this->translations($resource) : [],
        ]);
    }

    /**
     * @throws \Exception
     */
    protected function locales(): array
    {
        return nova_translation()->locales()->mapWithKeys(function (Locale $locale) {
            return [$locale->getKey() => $locale->toArray()];
        })->toArray();
    }

    protected function translations(IsTranslatable $resource): array
    {
        return $resource->translations->mapWithKeys(function (TranslationModel $translation) {
            return [
                $translation->locale_id => $translation->toArray(),
            ];
        })->toArray();
    }
}
