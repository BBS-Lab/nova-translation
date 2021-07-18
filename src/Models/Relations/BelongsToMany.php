<?php

namespace BBSLab\NovaTranslation\Models\Relations;

use BBSLab\NovaTranslation\Models\Contracts\IsTranslatable;
use BBSLab\NovaTranslation\Models\Locale;
use BBSLab\NovaTranslation\Models\Translation;
use BBSLab\NovaTranslation\NovaTranslation;
use Illuminate\Database\Eloquent\Relations\BelongsToMany as Relation;
use Illuminate\Support\Collection;

class BelongsToMany extends Relation
{
    /**
     * The parent model instance.
     *
     * @var \BBSLab\NovaTranslation\Models\Contracts\IsTranslatable
     */
    protected $parent;

    /**
     * Attach a model to the parent.
     *
     * @param  mixed  $id
     * @param  array  $attributes
     * @param  bool  $touch
     * @return void
     * @throws \Exception
     */
    public function attach($id, array $attributes = [], $touch = true)
    {
        parent::attach($id, $attributes, $touch);

        if (! in_array(get_class($this->parent), NovaTranslation::translatableModels())) {
            return;
        }

        if ($this->parent->isTranslatingRelation()) {
            return;
        }

        $keys = $this->getTranslatedKeys($this->parseIds($id), $locales = NovaTranslation::otherLocales());

        $this->parent->translations()
            ->with(['locale', 'translatable'])
            ->get()
            ->each(function (Translation $translation) use ($keys, $attributes, $touch) {
                $model = $translation->translatable;
                $model->translatingRelation();
                $model->{$this->relationName}()->attach(
                    data_get($keys, $translation->locale->iso, []),
                    $attributes,
                    $touch
                );
            });
    }

    public function detach($ids = null, $touch = true)
    {
        $result = parent::detach($ids, $touch);

        if (! in_array(get_class($this->parent), NovaTranslation::translatableModels())) {
            return $result;
        }

        if ($this->parent->isTranslatingRelation()) {
            return $result;
        }

        $keys = $this->getTranslatedKeys($this->parseIds($ids), NovaTranslation::otherLocales());

        $this->parent->translations()
            ->with(['locale', 'translatable'])
            ->get()
            ->each(function (Translation $translation) use ($keys, $touch) {
                $model = $translation->translatable;
                $model->translatingRelation();
                $model->{$this->relationName}()->detach(data_get($keys, $translation->locale->iso, []), $touch);
            });

        return $result;
    }

    public function sync($ids, $detaching = true)
    {
        $changes = parent::sync($ids, $detaching);

        if (! in_array(get_class($this->parent), NovaTranslation::translatableModels())) {
            return $changes;
        }

        if ($this->parent->isTranslatingRelation()) {
            return $changes;
        }

        $ids = $this->parseIds($ids);
        $keys = $this->getTranslatedKeys($ids, NovaTranslation::otherLocales());

        $this->parent->translations()
            ->with(['locale', 'translatable'])
            ->get()
            ->each(function (Translation $translation) use ($keys, $detaching) {
                $model = $translation->translatable;
                $model->translatingRelation();
                $model->{$this->relationName}()->sync(data_get($keys, $translation->locale->iso, []), $detaching);
            });

        return $changes;
    }

    public function getTranslatedKeys(array $keys, Collection $locales): array
    {
        if (! $this->related instanceof IsTranslatable) {
            return $locales->mapWithKeys(function (Locale $locale) use ($keys) {
                return [$locale->iso => $keys];
            })->toArray();
        }

        $ids = [];

        foreach ($keys as $key => $value) {
            $ids[] = is_array($value) ? $key : $value;
        }

        return $this->related->newQuery()
            ->whereIn($this->getRelatedKeyName(), $ids)
            ->with('translations.locale')
            ->get()
            ->groupBy('translations.*.locale.iso')
            ->mapWithKeys(function (Collection $group, $iso) use ($keys) {
                $translations = $group->flatMap(function (IsTranslatable $translatable) use ($iso) {
                    return $translatable->translations->filter(function (Translation $translation) use ($iso) {
                        return $translation->locale->iso === $iso;
                    });
                })
                    ->mapWithKeys(function (Translation $translation) {
                        return [$translation->translatable_source => $translation->translatable_id];
                    })
                    ->toArray();

                $new = [];

                foreach ($keys as $key => $value) {
                    if (is_array($value)) {
                        if (isset($translations[$key])) {
                            $new[$translations[$key]] = $value;
                        }
                    } else {
                        if (isset($translations[$value])) {
                            $new[$key] = $translations[$value];
                        }
                    }
                }

                return [$iso => $new];
            })->toArray();
    }
}
