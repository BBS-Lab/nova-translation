<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'labels'], function () {
    Route::post('/', [\BBS\Nova\Translation\Http\Controllers\LabelsController::class, 'save']);
    Route::get('/', [\BBS\Nova\Translation\Http\Controllers\LabelsController::class, 'index']);
});
