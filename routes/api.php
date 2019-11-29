<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'translation-matrix'], function () {
    Route::post('/', [\BBSLab\NovaTranslation\Http\Controllers\TranslationMatrixController::class, 'save']);
    Route::get('/', [\BBSLab\NovaTranslation\Http\Controllers\TranslationMatrixController::class, 'index']);
});
