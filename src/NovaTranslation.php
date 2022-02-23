<?php

namespace BBSLab\NovaTranslation;

use BBSLab\NovaTranslation\Models\Locale;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class NovaTranslation
{
    protected $config = [];

    protected $locales = [];

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    protected function cacheKey(): string
    {
        return $this->config['cache']['key'];
    }

    protected function cacheTtl(): int
    {
        return $this->config['cache']['ttl'];
    }

    public function forgetLocales(): void
    {
        Cache::forget($this->cacheKey());
        $this->locales = [];
    }

    public function localeModel(): string
    {
        return $this->config['models']['locale'];
    }

    public function labelModel(): string
    {
        return $this->config['models']['label'];
    }

    /**
     * @throws \Exception
     */
    public function currentLocale(): Locale
    {
        if (!isset($this->locales[$iso = app()->getLocale()])) {
            $locale = $this->localeModel()::havingIso($iso);

            if (empty($locale)) {
                throw new \Exception("No such locale [{$iso}]");
            }

            $this->locales[$iso] = $locale;
        }

        return $this->locales[$iso];
    }

    public function locales(): Collection
    {
        return Cache::remember($this->cacheKey(), $this->cacheTtl(), function () {
            return $this->localeModel()::query()
                ->orderBy('label')
                ->get();
        });
    }

    public function otherLocales(?Locale $current = null): Collection
    {
        $current = $current ?? $this->currentLocale();

        return $this->locales()->reject(function (Locale $locale) use ($current) {
            return $locale->is($current);
        });
    }

    public function translatableModels(): array
    {
        return $this->config['auto_synced_models'] ?? [];
    }

    public function localeSessionKey(): string
    {
        return $this->config['locale_session_key'];
    }
}
