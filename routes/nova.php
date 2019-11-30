<?php

use Illuminate\Support\Facades\Route;

// @TODO...
// count, detach, restore, soft-delete, force, ...

Route::post('/{resource}', [\BBSLab\NovaTranslation\Http\Controllers\TranslatableResource\StoreController::class, 'handle']);
Route::put('/{resource}/{resourceId}', [\BBSLab\NovaTranslation\Http\Controllers\TranslatableResource\UpdateController::class, 'handle']);
Route::delete('/{resource}', [\BBSLab\NovaTranslation\Http\Controllers\TranslatableResource\DestroyController::class, 'handle']);
