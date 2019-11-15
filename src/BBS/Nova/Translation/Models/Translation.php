<?php

namespace BBS\Nova\Translation\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $locale_id
 * @property int $translatable_id
 * @property string $translatable_type
 */
class Translation extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'translations';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = null;

    /**
     * {@inheritdoc}
     */
    public $incrementing = false;

    /**
     * {@inheritdoc}
     */
    public $timestamps = false;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'locale_id',
        'translatable_id',
        'translatable_type',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'locale_id' => 'integer',
        'translatable_id' => 'integer',
    ];
    
    public function translatable()
    {
        
    }
}
