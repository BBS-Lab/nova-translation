<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'labels'], function () {
    Route::get('/', [\BBS\Nova\Translation\Http\Controllers\LabelsController::class, 'matrix']);
});
