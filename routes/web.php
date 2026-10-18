<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::resource('tickets', TicketController::class);

    Route::resource('tickets.comments', CommentController::class)
        ->only(['store', 'update', 'destroy']);
});

require __DIR__.'/settings.php';
