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
        $tablePrefix = $model->getTable() . '.';

        $currentLocaleId = config('current_locale_id');
        if (empty($currentLocaleId)) {
            /** @var \BBS\Nova\Translation\Models\Locale $currentLocale */
            $currentLocale = Locale::query()->select('id')->where('iso', '=', app()->getLocale())->first();
            $currentLocaleId = $currentLocale->id;
            config(['current_locale_id' => $currentLocaleId]);
        }

        $builder
            ->join('translations', $tablePrefix . $model->translatableIdField(), '=', 'translations.translatable_id')
            ->where('translations.locale_id', '=', $currentLocaleId);
    }
}
