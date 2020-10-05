<?php

namespace BBSLab\NovaTranslation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property int $locale_id
 * @property int $translation_id
 * @property int $translatable_id
 * @property string $translatable_type
 * @property int $translatable_source
 * @property \BBSLab\NovaTranslation\Models\Locale $locale
 * @property \BBSLab\NovaTranslation\Models\Contracts\IsTranslatable $translatable
 * @property \BBSLab\NovaTranslation\Models\Contracts\IsTranslatable $source
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
    public $timestamps = false;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'locale_id',
        'translation_id',
        'translatable_id',
        'translatable_type',
        'translatable_source',
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

    public function source(): MorphTo
    {
        return $this->morphTo('source', 'translatable_type', 'translatable_source');
    }

    /**
     * Locale relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function locale()
    {
        return $this->belongsTo(Locale::class);
    }
}
