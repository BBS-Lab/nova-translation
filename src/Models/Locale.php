<?php

namespace BBSLab\NovaTranslation\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $iso
 * @property string $label
 * @property int $fallback_id
 * @property \BBSLab\NovaTranslation\Models\Locale $fallback
 * @property bool $available_in_api
 * @property string $flag
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder availableInApi(array $args = [])
 */
class Locale extends Model
{
    protected $fillable = [
        'iso',
        'label',
        'fallback_id',
        'available_in_api',
    ];

    protected $casts = [
        'fallback_id' => 'integer',
        'available_in_api' => 'boolean',
    ];

    protected $attributes = [
        'available_in_api' => true,
    ];

    protected $appends = ['flag'];

    /**
     * @var ?callable
     */
    public static $flagResolver;

    public function scopeAvailableInApi(Builder $query, array $args = []): Builder
    {
        return $query->where('available_in_api', '=', $args['available_in_api'] ?? true);
    }

    public function fallback(): BelongsTo
    {
        return $this->belongsTo($this->getMorphClass(), 'fallback_id');
    }

    public static function havingIso(string $iso): ?Locale
    {
        return static::query()->where('iso', '=', $iso)->first();
    }

    public function getFlagAttribute(): ?string
    {
        return once(function () {
            return static::$flagResolver
                ? call_user_func(static::$flagResolver, $this)
                : $this->resolveFlag();
        });
    }

    protected function resolveFlag(): ?string
    {
        $chunks = explode('-', $this->iso);
        $country = strtoupper(end($chunks));

        return config('nova-translation.flags.'.$country) ?? config('nova-translation.flags.default');
    }
}
