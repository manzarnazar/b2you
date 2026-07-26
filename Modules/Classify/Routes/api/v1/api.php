<?php

use Illuminate\Support\Facades\Route;
use Modules\Classify\Http\Controllers\Api\V1\Admin\ListingController as AdminListingController;
use Modules\Classify\Http\Controllers\Api\V1\Admin\ReportController as AdminReportController;
use Modules\Classify\Http\Controllers\Api\V1\Admin\SettingsController as AdminSettingsController;
use Modules\Classify\Http\Controllers\Api\V1\Customer\ListingController as CustomerListingController;
use Modules\Classify\Http\Controllers\Api\V1\Vendor\ListingController as VendorListingController;

// Customer / public classify APIs
Route::group(['prefix' => 'classify', 'middleware' => ['localization', 'module-check']], function () {
    Route::get('listings', [CustomerListingController::class, 'index']);
    Route::get('listing/{id}', [CustomerListingController::class, 'show']);
    Route::get('listing/{id}/similar', [CustomerListingController::class, 'similar']);
    Route::get('seller/{store_id}', [CustomerListingController::class, 'seller']);

    Route::group(['middleware' => ['auth:api']], function () {
        Route::get('favorites', [CustomerListingController::class, 'favorites']);
        Route::post('favorite', [CustomerListingController::class, 'addFavorite']);
        Route::delete('favorite', [CustomerListingController::class, 'removeFavorite']);
        Route::post('report', [CustomerListingController::class, 'report']);
        Route::post('chat', [CustomerListingController::class, 'chat']);
    });
});

// Vendor classify APIs
Route::group(['prefix' => 'vendor/classify', 'middleware' => ['vendor.api', 'actch:vendor_app']], function () {
    Route::get('listings', [VendorListingController::class, 'index']);
    Route::get('categories', [VendorListingController::class, 'categories']);
    Route::post('listing', [VendorListingController::class, 'store']);
    Route::get('listing/{id}', [VendorListingController::class, 'show']);
    Route::put('listing/{id}', [VendorListingController::class, 'update']);
    Route::post('listing/{id}', [VendorListingController::class, 'update']); // multipart fallback
    Route::delete('listing/{id}', [VendorListingController::class, 'destroy']);
    Route::post('listing/{id}/images', [VendorListingController::class, 'uploadImages']);
    Route::post('listing/{id}/sold', [VendorListingController::class, 'sold']);
    Route::post('listing/{id}/renew', [VendorListingController::class, 'renew']);
    Route::post('listing/{id}/archive', [VendorListingController::class, 'archive']);
    Route::get('listing/{id}/stats', [VendorListingController::class, 'stats']);
});

// Admin classify APIs (token-style if used; primarily Blade uses web routes)
Route::group(['prefix' => 'admin/classify', 'middleware' => ['localization']], function () {
    Route::get('listings', [AdminListingController::class, 'index']);
    Route::get('listings/{id}', [AdminListingController::class, 'show']);
    Route::post('approve/{id}', [AdminListingController::class, 'approve']);
    Route::post('reject/{id}', [AdminListingController::class, 'reject']);
    Route::post('feature/{id}', [AdminListingController::class, 'feature']);
    Route::post('premium/{id}', [AdminListingController::class, 'premium']);
    Route::get('reports', [AdminReportController::class, 'index']);
    Route::post('reports/{id}/resolve', [AdminReportController::class, 'resolve']);
    Route::post('reports/{id}/dismiss', [AdminReportController::class, 'dismiss']);
    Route::get('settings', [AdminSettingsController::class, 'show']);
    Route::post('settings', [AdminSettingsController::class, 'update']);
});
