<?php

namespace BBSLab\NovaTranslation;

use BBSLab\NovaTranslation\Models\Locale;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class NovaTranslation
{
    const LOCALES_CACHE_KEY = 'nova-translation-locales';

    /**
     * @return \BBSLab\NovaTranslation\Models\Locale
     *
     * @throws \Exception
     */
    public static function currentLocale(): Locale
    {
        $locale = Locale::iso($iso = app()->getLocale());

        if (empty($locale)) {
            throw new \Exception("No such locale [{$iso}]");
        }

        return $locale;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public static function locales(): Collection
    {
        return Cache::rememberForever(static::LOCALES_CACHE_KEY, function () {
            return Locale::query()
                ->orderBy('label')
                ->get();
        });
    }

    /**
     * @param  \BBSLab\NovaTranslation\Models\Locale|null  $current
     * @return \Illuminate\Support\Collection
     * @throws \Exception
     */
    public static function otherLocales(?Locale $current = null): Collection
    {
        $current = $current ?? static::currentLocale();

        return static::locales()->reject(function (Locale $locale) use ($current) {
            return $locale->is($current);
        });
    }

    public static function translatableModels(): array
    {
        return config(NovaTranslationServiceProvider::PACKAGE_ID.'.auto_synced_models', []) ?? [];
    }
}
