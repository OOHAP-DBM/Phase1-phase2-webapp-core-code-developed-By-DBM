<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Vendor\OfferController as VendorApiOfferController;
use App\Http\Controllers\Api\Customer\OfferController as CustomerApiOfferController;

/**
 * Offer API Routes (v1)
 * Base: /api/v1/offers
 *
 * Vendor offers in response to enquiries
 */

// Vendor routes
Route::prefix('vendor')
    ->name('vendor.')->middleware(['auth:sanctum', 'role:vendor'])->group(function () {
//     Route::post('/', [\Modules\Offers\Controllers\Api\OfferController::class, 'store']);
//     Route::get('/', [\Modules\Offers\Controllers\Api\OfferController::class, 'index']);
//     Route::get('/{id}', [\Modules\Offers\Controllers\Api\OfferController::class, 'show']);
//     Route::put('/{id}', [\Modules\Offers\Controllers\Api\OfferController::class, 'update']);
//     Route::post('/{id}/withdraw', [\Modules\Offers\Controllers\Api\OfferController::class, 'withdraw']);
// Route::post('/enquiries/{enquiryId}/offers', [
//     \Modules\Offers\Controllers\Api\OfferController::class,
//     'createDraft'
// ]);

 Route::get('/', [VendorApiOfferController::class, 'index'])->name('index');
            Route::get('create-context', [VendorApiOfferController::class, 'createContext'])->name('create-context');
            Route::post('/', [VendorApiOfferController::class, 'store'])->name('store');
            Route::get('/{offer}', [VendorApiOfferController::class, 'show'])->name('show');
            Route::post('{offer}/archive', [VendorApiOfferController::class, 'archive'])->name('archive');
            Route::post('{offer}/unarchive', [VendorApiOfferController::class, 'unarchive'])->name('unarchive');
            Route::post('{offer}/remind', [VendorApiOfferController::class, 'sendReminder'])->name('remind');
            Route::post('{offer}/vendor-reject', [VendorApiOfferController::class, 'vendorReject'])->name('vendor-reject');
            Route::post('{offer}/accept-customer-modification', [VendorApiOfferController::class, 'acceptCustomerModification'])->name('accept-customer-modification');


});

// Customer routes
Route::prefix('customer')
    ->name('customer.')->middleware(['auth:sanctum', 'role:customer'])->group(function () {
  //  Route::get('/enquiry/{enquiryId}/offers', [\Modules\Offers\Controllers\Api\OfferController::class, 'getOffersByEnquiry']);
    Route::get('/', [CustomerApiOfferController::class, 'index'])->name('index');
            Route::get('{offer}', [CustomerApiOfferController::class, 'show'])->name('show');
            Route::post('{offer}/accept', [CustomerApiOfferController::class, 'accept'])->name('accept');
            Route::post('{offer}/reject', [CustomerApiOfferController::class, 'reject'])->name('reject');
            Route::get('{offer}/modify-context', [CustomerApiOfferController::class, 'modifyContext'])->name('modify-context');
            Route::get('{offer}/hoardings', [CustomerApiOfferController::class, 'getHoardings'])->name('hoardings');
            Route::post('{offer}/modify', [CustomerApiOfferController::class, 'storeModification'])->name('modify');
});
