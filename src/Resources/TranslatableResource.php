<?php

namespace BBSLab\NovaTranslation\Resources;

use BBSLab\NovaTranslation\Models\Locale;
use BBSLab\NovaTranslation\Models\Translation;
use Closure;
use Eminiarts\Tabs\TabsOnEdit;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Laravel\Nova\Contracts\Resolvable;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\FieldCollection;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Http\Requests\ResourceIndexRequest;
use Laravel\Nova\Panel;
use Laravel\Nova\Resource;

abstract class TranslatableResource extends Resource
{
    use TabsOnEdit {
        creationFields as tabsOnEditCreationFields;
        updateFields as tabsOnEditUpdateFields;
    }

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
    public function availablePanelsForCreate($request)
    {
        $panels = parent::availablePanelsForUpdate($request);

        $panels[] = $this->translationsPanel('tabs');

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
        $panelTranslations = new Panel($this->translationsPanelName());

        $panelTranslations->withToolbar();
        $panelTranslations->withMeta([
            'component' => $component,
            'defaultSearch' => $this->defaultSearch,
        ]);

        return $panelTranslations;
    }

    /**
     * Translations panel name.
     *
     * @return string
     */
    protected function translationsPanelName()
    {
        return 'Translations '.Str::lower(static::label());
    }

    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------

    /**
     * {@inheritdoc}
     */
    public function detailFields(NovaRequest $request)
    {
        $fields = parent::detailFields($request);

        $fields = $this->translationsFields($fields);

        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function creationFields(NovaRequest $request)
    {
        $fields = $this->tabsOnEditCreationFields($request);

        $fields = $this->translationsFields($fields);

        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function updateFields(NovaRequest $request)
    {
        $fields = $this->tabsOnEditUpdateFields($request);

        $fields = $this->translationsFields($fields);

        return $fields;
    }

    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------

    /**
     * Transform fields to handle translation system.
     *
     * @param  \Laravel\Nova\Fields\FieldCollection  $fields
     * @param  bool  $forceReadonlyIds
     * @return \Laravel\Nova\Fields\FieldCollection
     * @throws \Exception
     */
    protected function translationsFields(FieldCollection $fields, $forceReadonlyIds = false)
    {
        $translationsFields = [];

        $locales = Locale::query()->select('id', 'iso', 'label')->get();
        $translations = $this->getResourceTranslations($locales);

        foreach ($locales as $locale) {
            /** @var \BBSLab\NovaTranslation\Models\Locale $locale */
            $localeResource = $translations->where('locale_id', '=', $locale->id)->first();
            if (empty($localeResource)) {
                throw new Exception('Invalid locale resource for "'.$locale->label.'"');
            }

            foreach ($fields as $field) {
                /** @var \Laravel\Nova\Fields\Field $field */
                if ($forceReadonlyIds && ($field->attribute === $this->resource->getKeyName())) {
                    $field->readonly();
                }
                $translationsFields[] = $this->translationField($field, $locale, $localeResource);
            }
        }

        return new FieldCollection($translationsFields);
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

        // @TODO... Debug $field->resolve()
        // $value = ($cloneField instanceof Resolvable) ? $cloneField->resolve($localeResource) : $localeResource->{$cloneField->attribute};
        $value = $localeResource->{$cloneField->attribute};

        $cloneField->panel = $this->translationsPanelName();
        $cloneField->value = $value;
        $cloneField->attribute = $cloneField->attribute.'['.$locale->id.']';
        $cloneField->withMeta([
            'tab' => $locale->label,
            'localeId' => $locale->id,
            'localeValue' => $value,
        ]);

        return $cloneField;
    }

    /**
     * Return resource translations.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $locales
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getResourceTranslations(Collection $locales)
    {
        $baseTranslation = Translation::query()
            ->select('translation_id')
            ->where('translatable_id', '=', $this->resource->getKey())
            ->where('translatable_type', '=', get_class($this->resource))
            ->first();

        if (empty($baseTranslation)) {
            $translationId = $this->resource->freshTranslationId();
            $translations = new Collection();
            foreach ($locales as $locale) {
                /** @var \BBSLab\NovaTranslation\Models\Locale $locale */
                $translation = new $this->resource;
                $translation->locale_id = $locale->id;
                $translation->translation_id = $translationId;
                $translations->push($translation);
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
