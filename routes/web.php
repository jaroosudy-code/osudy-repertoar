<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ColorController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\RoundController;
use App\Http\Controllers\SetlistController;
use App\Http\Controllers\SetlistSongController;
use App\Http\Controllers\SongController;
use Illuminate\Support\Facades\Route;

// Prihlasenie – bez ochrany hesla
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Vsetko ostatne vyzaduje heslo
Route::middleware(\App\Http\Middleware\RequireSitePassword::class)->group(function () {

    Route::get('/', fn() => redirect()->route('songs.index'));

    Route::resource('songs', SongController::class)->except(['show']);

    Route::get('colors', [ColorController::class, 'index'])->name('colors.index');
    Route::post('colors', [ColorController::class, 'store'])->name('colors.store');
    Route::delete('colors/{color}', [ColorController::class, 'destroy'])->name('colors.destroy');

    Route::resource('setlists', SetlistController::class)->except(['show']);
    Route::get('setlists/{setlist}', [SetlistController::class, 'show'])->name('setlists.show');

    Route::prefix('setlists/{setlist}')->group(function () {
        Route::post('rounds', [RoundController::class, 'store'])->name('rounds.store');
        Route::patch('rounds/{round}', [RoundController::class, 'update'])->name('rounds.update');
        Route::delete('rounds/{round}', [RoundController::class, 'destroy'])->name('rounds.destroy');
        Route::patch('rounds-reorder', [RoundController::class, 'reorder'])->name('rounds.reorder');

        Route::post('songs', [SetlistSongController::class, 'store'])->name('setlist-songs.store');
        Route::delete('songs/{entry}', [SetlistSongController::class, 'destroy'])->name('setlist-songs.destroy');
        Route::patch('songs/reorder', [SetlistSongController::class, 'reorder'])->name('setlist-songs.reorder');

        Route::get('export/csv', [ExportController::class, 'csv'])->name('setlists.export.csv');
    });

    Route::get('settings', [AuthController::class, 'showSettings'])->name('settings');
    Route::patch('settings/password', [AuthController::class, 'updatePassword'])->name('settings.password');
});
