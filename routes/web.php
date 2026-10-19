<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('tickets/{ticket}/claim', [TicketController::class, 'claim'])->name('tickets.claim');
    Route::resource('tickets', TicketController::class);

    Route::resource('tickets.comments', CommentController::class)
        ->only(['store', 'update', 'destroy'])
        ->scoped();
});

require __DIR__.'/settings.php';
