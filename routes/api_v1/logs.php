<?php

use Illuminate\Support\Facades\Route;

// Logs API (auth required)
Route::middleware('auth:sanctum')->group(function () {
    // Activity logs
    Route::get('activity', [\Modules\Enquiries\Controllers\Api\ActivityLogApiController::class, 'index']);

    // Session logs
    Route::get('session', [\Modules\Enquiries\Controllers\Api\SessionLogApiController::class, 'index']);
    Route::get('session/{id}', [\Modules\Enquiries\Controllers\Api\SessionLogApiController::class, 'show'])->where('id', '[0-9]+');
});
