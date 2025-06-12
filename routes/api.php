<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LoginApiController;
use App\Http\Controllers\Api\BookingApiController;
use App\Http\Controllers\Api\FacilityApiController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

use Illuminate\Support\Facades\Mail;

Route::get('/test-email', function () {
    $user = (object)[
        'name' => 'Test User',
        'email' => 'jocerdikiawan1@gmail.com'
    ];
    $url = url('/dummy');

    Mail::send('emails.verify', ['user' => $user, 'url' => $url], function ($message) use ($user) {
        $message->to($user->email, $user->name)
                ->subject('Test Email Laravel');
    });

    return "Email sent (check spam folder)";
});


Route::post('/register', [LoginApiController::class, 'register']);
Route::get('/verify-email', [LoginApiController::class, 'verifyEmail']);
Route::post('/resend-verification', [LoginApiController::class, 'resendVerification']);
// Route::post('/forgot-password', [LoginApiController::class, 'forgotPassword']);
// Route::post('/reset-password', [LoginApiController::class, 'resetPassword']);
// Route::get('/reset-password/{token}', [LoginApiController::class, 'showResetForm'])->name('password.reset');
// Route::post('/reset-password-form', [LoginApiController::class, 'resetPasswordForm'])->name('password.update');

Route::post('/login', [LoginApiController::class, 'authenticate'])->name('login');
Route::post('/logout', [LoginApiController::class, 'logout'])->name('logout');

Route::middleware(['authToken'])->group(function () {

    // Route::get('/bookings', [BookingApiController::class, 'index']);
    // Route::get('/bookings/upcoming', [BookingApiController::class, 'upcoming']);
    // Route::get('/bookings/{booking}', [BookingApiController::class, 'show']);
    Route::post('/bookings', [BookingApiController::class, 'store']);
    // Route::put('/bookings/{booking}', [BookingApiController::class, 'update']);
    // Route::delete('/bookings/{booking}', [BookingApiController::class, 'destroy']);
    // Route::post('/bookings/{booking}/approve', [BookingApiController::class, 'approve']);
    // Route::post('/bookings/{booking}/reject', [BookingApiController::class, 'reject']);
    Route::get('/bookings/history', [BookingApiController::class, 'userBookingHistory']);
    Route::get('/approved-events', [BookingApiController::class, 'approvedEvents']);

    Route::get('/facility-categories', [FacilityApiController::class, 'categories']);
    Route::get('/facilities', [FacilityApiController::class, 'facilities']);
    Route::get('/facility-items', [FacilityApiController::class, 'items']);
    Route::get('/facility-items/{item}', [FacilityApiController::class, 'itemDetails']);

});