<?php

namespace BBSLab\NovaTranslation\Fields;

use BBSLab\NovaTranslation\Models\Contracts\IsTranslatable;
use BBSLab\NovaTranslation\Models\Locale;
use BBSLab\NovaTranslation\Models\Translation as TranslationModel;
use BBSLab\NovaTranslation\NovaTranslation;
use BBSLab\NovaTranslation\NovaTranslationServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Fields\Field;

class Translation extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'nova-translation-field';

    /**
     * {@inheritdoc}
     */
    public function __construct(string $name = '', ?string $attribute = null, ?mixed $resolveCallback = null)
    {
        $name = trans(NovaTranslationServiceProvider::PACKAGE_ID.'::lang.field');
        $attribute = 'translation';

        parent::__construct($name, $attribute, $resolveCallback);

        $this->withMeta([
            'locales' => $this->locales(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($resource, $attribute = null)
    {
        $this->withMeta([
            'translations' => $resource instanceof IsTranslatable ? $this->translations($resource) : [],
        ]);

        parent::resolve($resource, $attribute);
    }

    /**
     * Return all indexed locales.
     *
     * @return array
     * @throws \Exception
     */
    protected function locales()
    {
        return NovaTranslation::locales()->mapWithKeys(function (Locale $locale) {
            return [$locale->getKey() => $locale->toArray()];
        })->toArray();
    }

    /**
     * Return translations entries for given translatable model.
     *
     * @param  \BBSLab\NovaTranslation\Models\Contracts\IsTranslatable  $resource
     * @return array
     */
    protected function translations(IsTranslatable $resource)
    {
        return $resource->translations()->mapWithKeys(function (IsTranslatable $translatable) {
            return [
                $translatable->translation->locale_id => $translatable->translation->toArray(),
            ];
        })->toArray();
    }
}
