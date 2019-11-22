<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'labels'], function () {
    Route::post('/', [\BBSLab\NovaTranslation\Http\Controllers\LabelsController::class, 'save']);
    Route::get('/', [\BBSLab\NovaTranslation\Http\Controllers\LabelsController::class, 'index']);
});
