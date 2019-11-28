<?php

namespace BBSLab\NovaTranslation\Resources;

use BBSLab\NovaTranslation\Models\Locale;
use BBSLab\NovaTranslation\Models\Translation;
use Eminiarts\Tabs\Tabs;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Http\Requests\ResourceIndexRequest;
use Laravel\Nova\Panel;
use Laravel\Nova\Resource;

abstract class TranslatableResource extends Resource
{
    /**
     * Return tabs of translations for current resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $fields
     * @return array
     */
    protected function translations(Request $request, array $fields)
    {
        if ($request instanceof ResourceIndexRequest) {
            return $fields;
        }

        $locales = Locale::query()->select('id', 'iso', 'label')->get();
        $translations = $this->getResourceTranslations($locales);

        $tabs = [];
        foreach ($locales as $locale) {
            /** @var \BBSLab\NovaTranslation\Models\Locale $locale */
            $localeResource = $translations->where('locale_id', '=', $locale->id)->first();
            if (! empty($localeResource)) {
                $this->resource = clone $localeResource;
                $tabs[] = new Panel($locale->iso, (new \ArrayObject($fields))->getArrayCopy());
            }
        }

        return [
            new Tabs('Translations', $tabs),
        ];
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
                // @TODO... Instantiate new model (maybe with nonTranslatable() already filled in + "locale_id" and "translation_id")
            }
        } else {
            $translations = $this->resource->translations();
        }

        return $translations;
    }

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
