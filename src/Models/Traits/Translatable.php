<?php

namespace BBSLab\NovaTranslation\Models\Traits;

use BBSLab\NovaTranslation\Models\Locale;
use BBSLab\NovaTranslation\Models\Observers\TranslatableObserver;
use BBSLab\NovaTranslation\Models\Translation;
use BBSLab\NovaTranslation\Models\TranslationRelation;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Query\JoinClause;

/**
 * @property \BBSLab\NovaTranslation\Models\Translation $translation
 * @property \Illuminate\Database\Eloquent\Collection|\BBSLab\NovaTranslation\Models\Translation[] $translations
 * @method static \Illuminate\Database\Eloquent\Builder locale(?string $iso = null)
 */
trait Translatable
{
    protected $_deleting_translation = false;
    protected $_translating_relation = false;

    /**
     * {@inheritdoc}
     */
    public static function bootTranslatable()
    {
        static::observe(TranslatableObserver::class);
    }

    /**
     * Get the list of non translatable fields.
     *
     * @return array
     */
    public function getNonTranslatable(): array
    {
        return isset($this->nonTranslatable) ? $this->nonTranslatable : [];
    }

    /**
     * Get the list of fields to duplicate on create.
     * (Other fields MUST BE nullable in database).
     *
     * @return array
     */
    public function getOnCreateTranslatable(): array
    {
        return isset($this->onCreateTranslatable) ? $this->onCreateTranslatable : $this->getNonTranslatable();
    }

    /**
     * Translation relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function translation(): MorphOne
    {
        return $this->morphOne(Translation::class, 'translatable');
    }

    /**
     * Return current item translations.
     *
     * @return \BBSLab\NovaTranslation\Models\TranslationRelation
     */
    public function translations(): TranslationRelation
    {
        return new TranslationRelation($this);
    }

    /**
     * Create and return a translation entry for given locale ID.
     *
     * @param  int  $localeId
     * @param  int  $sourceId
     * @param  int  $translationId
     * @return \BBSLab\NovaTranslation\Models\Translation
     */
    public function upsertTranslationEntry(int $localeId, int $sourceId, int $translationId = 0): Translation
    {
        /** @var \BBSLab\NovaTranslation\Models\Translation $translation */
        $translation = Translation::query()
            ->firstOrCreate([
                'locale_id' => $localeId,
                'translation_id' => ! empty($translationId) ? $translationId : $this->freshTranslationId(),
                'translatable_id' => $this->getKey(),
                'translatable_type' => static::class,
                'translatable_source' => $sourceId,
            ]);

        return $translation;
    }

    /**
     * Return next fresh translation ID.
     *
     * @return int
     */
    public function freshTranslationId(): int
    {
        $lastTranslation = Translation::query()
            ->where('translatable_type', '=', static::class)
            ->max('translation_id') ?? 0;

        return $lastTranslation + 1;
    }

    /**
     * Scope a query to only retrieve items from given locale.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  string  $iso
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLocale(EloquentBuilder $builder, string $iso = null)
    {
        return $builder->join('translations', function (JoinClause $join) {
            $join->on($this->getQualifiedKeyName(), '=', 'translations.translatable_id')
                ->where('translations.translatable_type', '=', static::class);
        })
            ->join('locales', function (JoinClause $join) use ($iso) {
                $join->on('locales.id', '=', 'translations.locale_id')
                    ->where('locales.iso', '=', $iso ?? app()->getLocale());
            })
            ->select($this->qualifyColumn('*'));
    }

    /**
     * Translate model to given locale and return translated model.
     *
     * @param  \BBSLab\NovaTranslation\Models\Locale  $locale
     * @return \BBSLab\NovaTranslation\Models\Contracts\IsTranslatable
     */
    public function translate(Locale $locale)
    {
        /** @var \BBSLab\NovaTranslation\Models\Contracts\IsTranslatable $translated */
        $translated = optional(
            $this->translations()
                ->where('locale_id', '=', $locale->getKey())
                ->with('translatable')
                ->first()
        )->translatable;

        return $translated ?? static::withoutEvents(function () use ($locale) {
            /** @var self $translated */
            $translated = $this->newQuery()->create(
                $this->only(
                    $this->getOnCreateTranslatable()
                )
            );

            $translated->upsertTranslationEntry(
                $locale->getKey(), $this->getKey(), $this->translation->translation_id
            );

            return $translated;
        });
    }

    /**
     * Set deleting translation state.
     *
     * @return void
     */
    public function deletingTranslation(): void
    {
        $this->_deleting_translation = true;
    }

    /**
     * Determine is the model currently in a delete translation process.
     *
     * @return bool
     */
    public function isDeletingTranslation(): bool
    {
        return $this->_deleting_translation;
    }

    /**
     * Set translating relation state.
     *
     * @return void
     */
    public function translatingRelation(): void
    {
        $this->_translating_relation = true;
    }

    /**
     * Determine is the model currently translating a relation.
     *
     * @return bool
     */
    public function isTranslatingRelation(): bool
    {
        return $this->_translating_relation;
    }
}
