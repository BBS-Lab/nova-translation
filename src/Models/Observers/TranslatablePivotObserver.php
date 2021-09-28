<?php

namespace BBSLab\NovaTranslation\Models\Observers;

use BBSLab\NovaTranslation\Models\Contracts\IsTranslatable;
use BBSLab\NovaTranslation\Models\Locale;
use BBSLab\NovaTranslation\Models\Relations\BelongsToMany;
use BBSLab\NovaTranslation\Models\Translation;
use BBSLab\NovaTranslation\NovaTranslation;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionMethod;

class TranslatablePivotObserver
{
    protected function isPivotParentTranslatable(Pivot $pivot): bool
    {
        return $pivot->pivotParent instanceof IsTranslatable;
    }

    protected function guessRelationship(Pivot $pivot): ?BelongsToMany
    {
        try {
            $class = new ReflectionClass($pivot->pivotParent);
        } catch (Exception $exception) {
            return null;
        }

        $method = Collection::make($class->getMethods())
            ->filter(function (ReflectionMethod $method) use ($pivot) {
                if ($method->getReturnType() == null) {
                    return false;
                }

                if (! method_exists($type = $method->getReturnType(), 'getName')) {
                    return false;
                }

                if ($type->getName() !== BelongsToMany::class) {
                    return false;
                }

                return $pivot->pivotParent->{$method->getName()}()->getTable() === $pivot->getTable();
            })->first();

        if (empty($method)) {
            return null;
        }

        return $pivot->pivotParent->{$method->getName()}();
    }

    protected function getTranslatedKeys(Model $related, Collection $locales): array
    {
        if (! $related instanceof IsTranslatable) {
            return $locales->mapWithKeys(function (Locale $locale) use ($related) {
                return [$locale->iso => $related->getKey()];
            })->toArray();
        }

        return $related->translations()
            ->with('locale')
            ->get()
            ->mapWithKeys(function (Translation $translation) {
                return [$translation->locale->iso => $translation->translatable_id];
            })->toArray();
    }

    /**
     * Handle the Translatable "created" event.
     *
     * @param  \Illuminate\Database\Eloquent\Relations\Pivot  $pivot
     * @return void
     * @throws \Exception
     */
    public function created(Pivot $pivot)
    {
        $this->handlePivot($pivot);
    }

    /**
     * Handle the Translatable "deleted" event.
     *
     * @param  \Illuminate\Database\Eloquent\Relations\Pivot  $pivot
     * @return void
     * @throws \Exception
     */
    public function deleted(Pivot $pivot)
    {
        $this->handlePivot($pivot, 'delete');
    }

    protected function handlePivot(Pivot $pivot, $method = 'save')
    {
        if (! $this->isPivotParentTranslatable($pivot)) {
            return;
        }

        /** @var \BBSLab\NovaTranslation\Models\Contracts\IsTranslatable $parent */
        $parent = $pivot->pivotParent;
        $relationship = $this->guessRelationship($pivot);

        if (empty($relationship)) {
            return;
        }

        $related = $relationship->getRelated()
            ->newQuery()
            ->with('translations.locale')
            ->find($pivot->{$relationship->getRelatedPivotKeyName()});

        if (empty($related)) {
            return;
        }

        $others = $this->getTranslatedKeys($related, NovaTranslation::otherLocales());

        $parent->translations()->with(['locale', 'translatable'])
            ->get()
            ->each(function (Translation $translation) use ($relationship, $others, $method) {
                if ($key = data_get($others, $translation->locale->iso)) {
                    $relationship->newPivot([
                        $relationship->getForeignPivotKeyName() => $translation->translatable_id,
                        $relationship->getRelatedPivotKeyName() => $key,
                    ])->{$method}();
                }
            });
    }
}
