<?php

namespace BBS\Nova\Translation\Models\Scopes;

use BBS\Nova\Translation\Models\Locale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TranslatableScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $currentLocaleId = config('current_locale_id');
        if (empty($currentLocaleId)) {
            /** @var \BBS\Nova\Translation\Models\Locale $currentLocale */
            $currentLocale = Locale::query()->select('id')->where('iso', '=', app()->getLocale())->first();
            $currentLocaleId = $currentLocale->id;
            config(['current_locale_id' => $currentLocaleId]);
        }

        $builder->join('translations', function ($join) use ($model, $currentLocaleId) {
            $join
                ->where('translations.locale_id', '=', $currentLocaleId)
                ->where('translations.translatable_type', '=', get_class($model))
                ->where($model->getTable().'.'.$model->translationIdField(), '=', 'translations.translation_id');
        });
    }
}
