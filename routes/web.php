<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EventController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth','verified'])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // EVENTOS

    Route::get('/events',[EventController::class,'getEvents']);
    Route::post('/event',[EventController::class,'store']);
    Route::put('/event/{id}',[EventController::class,'update']);
    Route::delete('/event/{id}',[EventController::class,'destroy']);
    Route::get('/events/today',[EventController::class,'getTodayEvents']);
    

});


Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});


require __DIR__.'/auth.php';