<?php

namespace BBSLab\NovaTranslation\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
    protected $table = 'locales';

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

    protected $appends = ['flag'];

    /**
     * @var \Callable|null
     */
    public static $flagResolver;

    public function scopeAvailableInApi(Builder $query, array $args = []): Builder
    {
        $availableInApi = isset($args['available_in_api']) ? ! empty($args['available_in_api']) : true;

        return $query->where('available_in_api', '=', $availableInApi);
    }

    public function fallback(): BelongsTo
    {
        return $this->belongsTo(static::class, 'fallback_id');
    }

    public static function havingIso(string $iso): ?Locale
    {
        $iso = DB::connection()->getPdo()->quote(Str::lower($iso));

        return static::query()->whereRaw('LOWER(`iso`) = '.$iso)->first();
    }

    public function getFlagAttribute(): ?string
    {
        if (! isset($this->attributes['flag'])) {
            $this->attributes['flag'] = static::$flagResolver
                ? call_user_func(static::$flagResolver, $this)
                : $this->resolveFlag();
        }

        return $this->attributes['flag'];
    }

    protected function resolveFlag(): ?string
    {
        $chunks = explode('-', $this->iso);
        $country = strtoupper(end($chunks));

        return config('nova-translation.flags.'.$country) ?? config('nova-translation.flags.default');
    }
}
