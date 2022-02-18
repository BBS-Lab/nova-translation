<?php

namespace BBSLab\NovaTranslation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $locale_id
 * @property int $translation_id
 * @property int $translatable_id
 * @property string $translatable_type
 * @property int $translatable_source
 * @property ?\BBSLab\NovaTranslation\Models\Locale $locale
 * @property ?\BBSLab\NovaTranslation\Models\Contracts\IsTranslatable $translatable
 * @property ?\BBSLab\NovaTranslation\Models\Contracts\IsTranslatable $source
 */
class Translation extends Model
{
    protected $table = 'translations';

    public $timestamps = false;

    protected $fillable = [
        'locale_id',
        'translation_id',
        'translatable_id',
        'translatable_type',
        'translatable_source',
    ];

    public function translatable(): MorphTo
    {
        return $this->morphTo();
    }

    public function source(): MorphTo
    {
        return $this->morphTo('source', 'translatable_type', 'translatable_source');
    }

    public function locale(): BelongsTo
    {
        return $this->belongsTo(Locale::class);
    }
}
