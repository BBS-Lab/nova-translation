<?php

declare(strict_types=1);

namespace Workbench\App\Models;

use BBSLab\NovaTranslation\Models\Contracts\IsTranslatable;
use BBSLab\NovaTranslation\Models\Traits\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Post extends Model implements IsTranslatable
{
    use Translatable;

    protected $fillable = [
        'uuid',
        'title',
        'slug',
        'content',
        'available',
    ];

    protected $nonTranslatable = [
        'uuid',
        'slug',
        'available',
    ];

    protected array $onCreateTranslatable = [
        'uuid',
        'title',
        'slug',
        'content',
        'available',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function (Post $model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::orderedUuid()->toString();
            }
        });
    }
}
