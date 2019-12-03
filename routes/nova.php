<?php

use Illuminate\Support\Facades\Route;

// @TODO...
// count, detach, restore, soft-delete, force, ...

Route::post('/{resource}', 'TranslatableResource\StoreController@handle');
Route::put('/{resource}/{resourceId}', 'TranslatableResource\UpdateController@handle');
Route::delete('/{resource}', 'TranslatableResource\DestroyController@handle');
