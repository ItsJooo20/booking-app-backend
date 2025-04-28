<?php

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\JsonTestController;
use App\Http\Controllers\FacilityItemController;
use App\Http\Controllers\FacilityCategoryController;

// Route::get('/', function () {
//     return view('context.index');
// });
// Route::get('/getData', [JsonTestController::class, 'calendar']);
Route::get('/', [LoginController::class, 'log'])->name('login');
Route::post('/login', [LoginController::class, 'authenticate'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');

    Route::get('/users', [AdminController::class, 'listUsers'])->name('users.list');
    Route::get('/users/deleted', [AdminController::class, 'listDeletedUsers'])->name('users.deleted');
    Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::post('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
    Route::post('/users/{id}/restore', [AdminController::class, 'restoreUser'])->name('users.restore');

    Route::resource('facility-categories', FacilityCategoryController::class);
    Route::resource('facilities', FacilityController::class);
    Route::resource('facility-items', FacilityItemController::class);

    
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings/store', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::get('/bookings/{booking}/edit', [BookingController::class, 'edit'])->name('bookings.edit');
    Route::put('/bookings/{booking}/update', [BookingController::class, 'update'])->name('bookings.update');
    Route::delete('/bookings/{booking}/destroy', [BookingController::class, 'destroy'])->name('bookings.destroy');
    Route::post('/bookings/{booking}/approve', [BookingController::class, 'approve'])->name('bookings.approve');
    Route::post('/bookings/{booking}/reject', [BookingController::class, 'reject'])->name('bookings.reject');

    Route::prefix('reports')->group(function() {
        Route::get('/', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/generate', [ReportController::class, 'generate'])->name('reports.generate');
        Route::get('/download-pdf', [ReportController::class, 'downloadPdf'])->name('reports.download');
    });
});

Route::get('/home', function (){
    return view('welcome');
})->name('home');