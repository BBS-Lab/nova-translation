<?php

namespace BBS\Nova\Translation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $locale_id
 * @property int $translation_id
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
        'translation_id',
        'translatable_id',
        'translatable_type',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'locale_id' => 'integer',
        'translation_id' => 'integer',
        'translatable_id' => 'integer',
    ];

    /**
     * Translatable polymorphic relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function translatable(): MorphTo
    {
        return $this->morphTo();
    }
}
