<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LoginApiController;
use App\Http\Controllers\Api\BookingApiController;
use App\Http\Controllers\Api\FacilityApiController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


Route::post('/login', [LoginApiController::class, 'authenticate'])->name('login');
Route::post('/logout', [LoginApiController::class, 'logout'])->name('logout');



Route::middleware(['authToken'])->group(function () {

    Route::get('/bookings', [BookingApiController::class, 'index']);
    Route::get('/bookings/upcoming', [BookingApiController::class, 'upcoming']);
    Route::get('/bookings/{booking}', [BookingApiController::class, 'show']);
    Route::post('/bookings', [BookingApiController::class, 'store']);
    Route::put('/bookings/{booking}', [BookingApiController::class, 'update']);
    Route::delete('/bookings/{booking}', [BookingApiController::class, 'destroy']);
    Route::post('/bookings/{booking}/approve', [BookingApiController::class, 'approve']);
    Route::post('/bookings/{booking}/reject', [BookingApiController::class, 'reject']);
    Route::get('/approved-events', [BookingApiController::class, 'approvedEvents']);

    Route::get('/facility-categories', [FacilityApiController::class, 'categories']);
    Route::get('/facilities', [FacilityApiController::class, 'facilities']);
    Route::get('/facility-items', [FacilityApiController::class, 'items']);
    Route::get('/facility-items/{item}', [FacilityApiController::class, 'itemDetails']);

});