<?php

namespace BBSLab\NovaTranslation\Resources;

use BBSLab\NovaTranslation\Models\Locale;
use BBSLab\NovaTranslation\Models\Translation;
use Eminiarts\Tabs\Tabs;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
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
        $locales = Locale::query()->select('id', 'iso', 'label')->get();

        $baseTranslation = Translation::query()
            ->select('translation_id')
            ->where('translatable_id', '=', $this->resource->getKey())
            ->where('translatable_type', '=', get_class($this->resource))
            ->first();
        $translationId = ! empty($baseTranslation) ? $baseTranslation->translation_id : $this->resource->freshTranslationId();

        $tabs = [];
        $test = [Text::make('key')];
        foreach ($locales as $locale) {
            $tabs[$locale->label] = [Text::make('key_' . $locale->iso)];
            /*
            $tabs[] = new Tabs($locale->label, array_merge([
                // Text::make('Translation ID', 'translation_id', $translationId)->withMeta(['type' => 'hidden']),
            ], $fields));
            */
        }

        return [
            new Tabs('Translations', $tabs),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->inLocale();
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
