<?php

namespace BBSLab\NovaTranslation\Models;

use BBSLab\NovaTranslation\Models\Contracts\IsTranslatable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $type
 * @property string $key
 * @property string $value
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Label extends Model implements IsTranslatable
{
    use Traits\Translatable;

    protected $fillable = [
        'id',
        'type',
        'key',
        'value',
    ];

    protected $nonTranslatable = [
        'type',
        'key',
    ];

    protected $onCreateTranslatable = [
        'type',
        'key',
        'value',
    ];
}
