<?php

namespace BBSLab\NovaTranslation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $iso
 * @property string $label
 * @property int $fallback_id
 * @property \BBSLab\NovaTranslation\Models\Locale $fallback
 * @property bool $available_in_api
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Locale extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'locales';

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'iso',
        'label',
        'fallback_id',
        'available_in_api',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'fallback_id' => 'integer',
        'available_in_api' => 'boolean',
    ];

    /**
     * Scope a query to only include locales available in API.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $args
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAvailableInApi($query, array $args = [])
    {
        $availableInApi = isset($args['available_in_api']) ? ! empty($args['available_in_api']) : true;

        return $query->where('available_in_api', '=', $availableInApi);
    }

    /**
     * Locale fallback relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function fallback(): BelongsTo
    {
        return $this->belongsTo(static::class, 'fallback_id');
    }

    /**
     * @param  string  $iso
     * @return \BBSLab\NovaTranslation\Models\Locale|null
     */
    public static function iso(string $iso)
    {
        return static::query()->firstWhere('iso', '=', $iso);
    }
}
