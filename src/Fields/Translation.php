<?php

namespace BBSLab\NovaTranslation\Fields;

use BBSLab\NovaTranslation\Models\Locale;
use BBSLab\NovaTranslation\Models\Translation as TranslationModel;
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
    public function resolveForDisplay($resource, $attribute = null)
    {
        $this->withMeta([
            'translations' => $this->translations($resource),
        ]);

        return parent::resolveForDisplay($resource, $attribute);
    }

    /**
     * Return all indexed locales.
     *
     * @return array
     */
    protected function locales()
    {
        $locales = [];

        $query = Locale::query();
        foreach ($query->cursor() as $locale) {
            $locales[$locale->id] = $locale->toArray();
        }

        return $locales;
    }

    /**
     * Return translations entries for given translatable model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $resource
     * @return array
     */
    protected function translations(Model $resource)
    {
        $translations = [];

        $query = TranslationModel::query()
            ->where('translation_id', '=', $resource->translation_id)
            ->where('translatable_type', '=', $resource->translatable_type);
        foreach ($query->cursor() as $translation) {
            $translations[$translation->locale_id] = $translation->toArray();
        }

        return $translations;
    }
}
