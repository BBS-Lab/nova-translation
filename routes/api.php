<?php

use BBSLab\NovaTranslation\Http\Controllers\LocaleController;
use BBSLab\NovaTranslation\Http\Controllers\TranslateController;
use BBSLab\NovaTranslation\Http\Controllers\TranslationMatrixController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'translation-matrix'], function () {
    Route::get('/export-locale', [TranslationMatrixController::class, 'exportLocale']);

    Route::post('/', [TranslationMatrixController::class, 'save']);
    Route::get('/', [TranslationMatrixController::class, 'index']);
});

Route::get('/translate/{resource}/{key}/locale-{locale}', [TranslateController::class, 'translate']);
Route::get('change-locale/{locale}', [LocaleController::class, '__invoke'])->name('nova-translation.change-locale');
