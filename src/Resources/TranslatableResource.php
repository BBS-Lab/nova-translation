<?php

namespace BBSLab\NovaTranslation\Resources;

use BBSLab\NovaTranslation\Models\Locale;
use BBSLab\NovaTranslation\Models\Translation;
use Eminiarts\Tabs\TabsOnEdit;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\FieldCollection;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use Laravel\Nova\Resource;

abstract class TranslatableResource extends Resource
{
    use TabsOnEdit;

    const PANEL_TRANSLATIONS = 'Translations';

    /**
     * Return resource translations.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getResourceTranslations()
    {
        $baseTranslation = Translation::query()
            ->select('translation_id')
            ->where('translatable_id', '=', $this->resource->getKey())
            ->where('translatable_type', '=', get_class($this->resource))
            ->first();

        if (empty($baseTranslation)) {
            $translationId = $this->resource->freshTranslationId();
            $translations = new Collection();
            foreach ($this->indexedLocales as $localeId => $locale) {
                // @TODO... Instantiate new model (maybe with nonTranslatable() already filled in + "locale_id" and "translation_id")
            }
        } else {
            $translations = $this->resource->translations();
        }

        return $translations;
    }

    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------

    /**
     * {@inheritdoc}
     */
    public function availablePanelsForDetail($request)
    {
        $panels = parent::availablePanelsForDetail($request);

        $panels[] = $this->translationsPanel('detail-tabs');

        return $panels;
    }

    /**
     * {@inheritdoc}
     */
    public function availablePanelsForUpdate($request)
    {
        $panels = parent::availablePanelsForUpdate($request);

        $panels[] = $this->translationsPanel('tabs');

        return $panels;
    }

    /**
     * Return configured translations panel.
     *
     * @param  string  $component
     * @return \Laravel\Nova\Panel
     */
    protected function translationsPanel(string $component = 'detail-tabs')
    {
        $panelTranslations = new Panel(static::PANEL_TRANSLATIONS);

        $panelTranslations->withToolbar();
        $panelTranslations->withMeta([
            'component' => $component,
            'defaultSearch' => $this->defaultSearch,
        ]);

        return $panelTranslations;
    }

    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------

    /**
     * {@inheritdoc}
     */
    public function serializeForDetail(NovaRequest $request)
    {
        $serialized = parent::serializeForDetail($request);
        if (! empty($serialized['id'])) {
            unset($serialized['id']);
        }

        $serialized['fields'] = $this->translationsFields($request, $serialized['fields']);

        return $serialized;
    }

    /**
     * {@inheritdoc}
     */
    public function updateFieldsWithinPanels($request)
    {
        $panels = parent::updateFieldsWithinPanels($request);

        /*
        foreach ($panels as $panel) {
            $panels['fields'] = new FieldCollection($this->translationsFields($request, $panel['fields']->all()));
        }
        */

        return $panels;
    }

    /**
     * Transform fields to handle translation system.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  array  $fields
     * @return array
     * @throws \Exception
     */
    protected function translationsFields(NovaRequest $request, array $fields)
    {
        $translationsFields = [];

        $locales = Locale::query()->select('id', 'iso', 'label')->get();
        $translations = $this->getResourceTranslations();

        foreach ($locales as $locale) {
            /** @var \BBSLab\NovaTranslation\Models\Locale $locale */
            $localeResource = $translations->where('locale_id', '=', $locale->id)->first();
            if (empty($localeResource)) {
                throw new Exception('Invalid locale resource for "'.$locale->label.'"');
            }

            foreach ($fields as $field) {
                $translationsFields[] = $this->translationField($field, $locale, $localeResource);
            }
        }

        return $translationsFields;
    }

    /**
     * Override field to handle translation system.
     *
     * @param  \Laravel\Nova\Fields\Field  $field
     * @param  \BBSLab\NovaTranslation\Models\Locale  $locale
     * @param  \Illuminate\Database\Eloquent\Model  $localeResource
     * @return void
     */
    protected function translationField(Field $field, Locale $locale, Model $localeResource)
    {
        $cloneField = clone $field;

        // @TODO... Use Field resolve()??
        // Compute value (resolve() on detail AND direct attribute on update)
        $value = $localeResource->{$field->attribute};

        $cloneField->panel = static::PANEL_TRANSLATIONS;
        $cloneField->value = $value;
        $cloneField->attribute = $cloneField->attribute.'['.$locale->id.']';
        $cloneField->withMeta([
            'tab' => $locale->label,
            'locale' => $locale->id,
        ]);

        return $cloneField;
    }

    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------

    /**
     * {@inheritdoc}
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->locale();
    }

    /**
     * {@inheritdoc}
     */
    public static function scoutQuery(NovaRequest $request, $query)
    {
        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public static function detailQuery(NovaRequest $request, $query)
    {
        return parent::detailQuery($request, $query);
    }

    /**
     * {@inheritdoc}
     */
    public static function relatableQuery(NovaRequest $request, $query)
    {
        return parent::relatableQuery($request, $query);
    }
}
