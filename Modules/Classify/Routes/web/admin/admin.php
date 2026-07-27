<?php

use Illuminate\Support\Facades\Route;
use Modules\Classify\Http\Controllers\Web\Admin\CategoryFieldController;
use Modules\Classify\Http\Controllers\Web\Admin\DashboardController;
use Modules\Classify\Http\Controllers\Web\Admin\ListingController;
use Modules\Classify\Http\Controllers\Web\Admin\ReportController;
use Modules\Classify\Http\Controllers\Web\Admin\SettingsController;

Route::group(['middleware' => ['admin', 'current-module']], function () {
    Route::group(['prefix' => 'classify', 'as' => 'classify.'], function () {
        Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard');

        Route::group(['prefix' => 'listings', 'as' => 'listings.'], function () {
            Route::get('/', [ListingController::class, 'index'])->name('index');
            Route::get('{id}/edit', [ListingController::class, 'edit'])->name('edit');
            Route::put('{id}', [ListingController::class, 'update'])->name('update');
            Route::get('{id}', [ListingController::class, 'show'])->name('show');
            Route::post('{id}/approve', [ListingController::class, 'approve'])->name('approve');
            Route::post('{id}/reject', [ListingController::class, 'reject'])->name('reject');
            Route::post('{id}/feature', [ListingController::class, 'feature'])->name('feature');
            Route::post('{id}/premium', [ListingController::class, 'premium'])->name('premium');
            Route::delete('{id}', [ListingController::class, 'destroy'])->name('destroy');
        });

        Route::group(['prefix' => 'category-fields', 'as' => 'category-fields.'], function () {
            Route::get('/', [CategoryFieldController::class, 'index'])->name('index');
            Route::get('{categoryId}', [CategoryFieldController::class, 'show'])->name('show');
            Route::post('{categoryId}', [CategoryFieldController::class, 'store'])->name('store');
            Route::put('{categoryId}/{fieldId}', [CategoryFieldController::class, 'update'])->name('update');
            Route::delete('{categoryId}/{fieldId}', [CategoryFieldController::class, 'destroy'])->name('destroy');
            Route::get('{categoryId}/{fieldId}/toggle', [CategoryFieldController::class, 'toggle'])->name('toggle');
            Route::get('{categoryId}/{fieldId}/move', [CategoryFieldController::class, 'move'])->name('move');
        });

        Route::get('settings', [SettingsController::class, 'index'])->name('settings');
        Route::post('settings', [SettingsController::class, 'update'])->name('settings.update');

        Route::group(['prefix' => 'reports', 'as' => 'reports.'], function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::post('{id}/resolve', [ReportController::class, 'resolve'])->name('resolve');
            Route::post('{id}/dismiss', [ReportController::class, 'dismiss'])->name('dismiss');
        });
    });
});
