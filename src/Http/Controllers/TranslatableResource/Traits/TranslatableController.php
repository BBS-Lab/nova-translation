<?php

namespace BBSLab\NovaTranslation\Http\Controllers\TranslatableResource\Traits;

use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\FieldCollection;
use Laravel\Nova\Http\Requests\NovaRequest;

trait TranslatableController
{
    /**
     * Map translatable data.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Laravel\Nova\Fields\FieldCollection  $fields
     * @return array
     */
    protected function mapTranslatableData(NovaRequest $request, FieldCollection $fields)
    {
        $translatedData = [];

        foreach ($fields as $panel) {
            foreach ($panel['fields'] as $field) {
                /** @var \Laravel\Nova\Fields\Field $field */
                $localeId = $field->meta()['localeId'];
                if (! isset($translatedData[$localeId])) {
                    $translatedData[$localeId] = [];
                }

                $attribute = $this->translatableAttribute($field, $localeId);
                $translatedData[$localeId][$attribute] = $request->input($attribute.'.'.$localeId);
            }
        }

        return $translatedData;
    }

    /**
     * Return translatable attribute name.
     *
     * @param  \Laravel\Nova\Fields\Field  $field
     * @param  int  $localeId
     * @return string
     */
    protected function translatableAttribute(Field $field, int $localeId)
    {
        return substr($field->attribute, 0, strpos($field->attribute, '['.$localeId.']'));
    }
}
