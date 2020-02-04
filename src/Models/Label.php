<?php

namespace BBSLab\NovaTranslation\Models;

use BBSLab\NovaTranslation\Models\Contracts\IsTranslatable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $type
 * @property string $key
 * @property string $value
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Label extends Model implements IsTranslatable
{
    use Traits\Translatable;

    const TYPE_TEXT = 'text';

    const TYPE_UPLOAD = 'upload';

    /**
     * {@inheritdoc}
     */
    protected $table = 'labels';

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'type',
        'key',
        'value',
    ];

    /**
     * {@inheritdoc}
     */
    protected $nonTranslatable = [
        'type',
        'key',
    ];
}
