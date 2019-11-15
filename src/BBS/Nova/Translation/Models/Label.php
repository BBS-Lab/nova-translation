<?php

namespace BBS\Nova\Translation\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $translatable_id
 * @property string $key
 * @property string $value
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Label extends Model
{
    use Traits\Translatable;

    /**
     * {@inheritdoc}
     */
    protected $table = 'labels';

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'translatable_id',
        'key',
        'value',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'translatable_id' => 'integer',
    ];
}
