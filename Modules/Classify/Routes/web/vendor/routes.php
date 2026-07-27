<?php

use Illuminate\Support\Facades\Route;
use Modules\Classify\Http\Controllers\Web\Vendor\ChatController;
use Modules\Classify\Http\Controllers\Web\Vendor\ListingController;

Route::group(['middleware' => ['vendor', 'current-module']], function () {
    Route::group(['prefix' => 'classify', 'as' => 'classify.'], function () {
        Route::get('listings', [ListingController::class, 'index'])->name('listings.index');
        Route::get('listings/create', [ListingController::class, 'create'])->name('listings.create');
        Route::post('listings', [ListingController::class, 'store'])->name('listings.store');
        Route::get('listings/{id}', [ListingController::class, 'show'])->name('listings.show');
        Route::get('listings/{id}/edit', [ListingController::class, 'edit'])->name('listings.edit');
        Route::post('listings/{id}', [ListingController::class, 'update'])->name('listings.update');
        Route::delete('listings/{id}', [ListingController::class, 'destroy'])->name('listings.destroy');
        Route::post('listings/{id}/sold', [ListingController::class, 'sold'])->name('listings.sold');
        Route::post('listings/{id}/renew', [ListingController::class, 'renew'])->name('listings.renew');
        Route::post('listings/{id}/archive', [ListingController::class, 'archive'])->name('listings.archive');

        Route::get('chats', [ChatController::class, 'index'])->name('chats.index');
        Route::get('chats/{id}', [ChatController::class, 'show'])->name('chats.show');
        Route::post('chats/{id}/send', [ChatController::class, 'send'])->name('chats.send');
    });
});
