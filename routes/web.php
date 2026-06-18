<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BandController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ChordDiagramController;
use App\Http\Controllers\ColorController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\RoundController;
use App\Http\Controllers\SetlistController;
use App\Http\Controllers\SetlistSongController;
use App\Http\Controllers\SongController;
use Illuminate\Support\Facades\Route;

// Prihlasenie – verejné
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Všetko ostatné vyžaduje prihlásenie
Route::middleware('auth')->group(function () {

    Route::get('/', fn() => redirect()->route('songs.index'));

    Route::get('select-band', [BandController::class, 'select'])->name('bands.select');
    Route::get('no-band', [BandController::class, 'noBand'])->name('bands.no-band');
    Route::post('switch-band/{band}', [BandController::class, 'switch'])->name('bands.switch');

    Route::resource('songs', SongController::class)->except(['show']);
    Route::get('songs/{song}', [SongController::class, 'show'])->name('songs.show');
    Route::post('songs/attach-from-band', [SongController::class, 'attachFromBand'])->name('songs.attach-from-band');

    Route::get('colors', [ColorController::class, 'index'])->name('colors.index');
    Route::post('colors', [ColorController::class, 'store'])->name('colors.store');
    Route::delete('colors/{color}', [ColorController::class, 'destroy'])->name('colors.destroy');

    Route::resource('setlists', SetlistController::class)->except(['show']);
    Route::get('setlists/{setlist}', [SetlistController::class, 'show'])->name('setlists.show');
    Route::post('setlists/{setlist}/duplicate', [SetlistController::class, 'duplicate'])->name('setlists.duplicate');

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

    Route::get('metronom', fn() => view('metronom.index'))->name('metronom');

    Route::get('settings', [AuthController::class, 'showSettings'])->name('settings');
    Route::patch('settings/password', [AuthController::class, 'updatePassword'])->name('settings.password');
    Route::post('settings/invisible', [AuthController::class, 'toggleInvisible'])->name('settings.invisible');

    // Chat
    Route::get('chat', [ChatController::class, 'index'])->name('chat.index');
    Route::post('chat/send', [ChatController::class, 'send'])->name('chat.send');
    Route::post('chat/read', [ChatController::class, 'markRead'])->name('chat.read');
    Route::get('api/chat/messages', [ChatController::class, 'messages'])->name('chat.messages');
    Route::get('api/chat/online', [ChatController::class, 'online'])->name('chat.online');
    Route::get('api/chat/unread', [ChatController::class, 'unread'])->name('chat.unread');
    Route::get('api/chat/users', [ChatController::class, 'usersList'])->name('chat.users');
    Route::get('api/chat/unread-detail', [ChatController::class, 'unreadDetail'])->name('chat.unread-detail');

    Route::get('api/chords', [ChordDiagramController::class, 'show'])->name('chords.show');
    Route::post('api/chords', [ChordDiagramController::class, 'upsert'])->name('chords.upsert');

    // Admin panel – len pre adminov
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {

        // Správa používateľov
        Route::get('users',             [AdminController::class, 'usersIndex'])->name('users.index');
        Route::get('users/create',      [AdminController::class, 'usersCreate'])->name('users.create');
        Route::post('users',            [AdminController::class, 'usersStore'])->name('users.store');
        Route::get('users/{user}/edit', [AdminController::class, 'usersEdit'])->name('users.edit');
        Route::patch('users/{user}',    [AdminController::class, 'usersUpdate'])->name('users.update');
        Route::delete('users/{user}',   [AdminController::class, 'usersDestroy'])->name('users.destroy');

        // Správa rolí
        Route::get('roles',             [AdminController::class, 'rolesIndex'])->name('roles.index');
        Route::get('roles/create',      [AdminController::class, 'rolesCreate'])->name('roles.create');
        Route::post('roles',            [AdminController::class, 'rolesStore'])->name('roles.store');
        Route::get('roles/{role}/edit', [AdminController::class, 'rolesEdit'])->name('roles.edit');
        Route::patch('roles/{role}',    [AdminController::class, 'rolesUpdate'])->name('roles.update');
        Route::delete('roles/{role}',   [AdminController::class, 'rolesDestroy'])->name('roles.destroy');

        // Správa kapiel
        Route::get('bands',                                           [AdminController::class, 'bandsIndex'])->name('bands.index');
        Route::get('bands/create',                                    [AdminController::class, 'bandsCreate'])->name('bands.create');
        Route::post('bands',                                          [AdminController::class, 'bandsStore'])->name('bands.store');
        Route::get('bands/{band}/edit',                              [AdminController::class, 'bandsEdit'])->name('bands.edit');
        Route::patch('bands/{band}',                                  [AdminController::class, 'bandsUpdate'])->name('bands.update');
        Route::delete('bands/{band}',                                 [AdminController::class, 'bandsDestroy'])->name('bands.destroy');
        Route::post('bands/{band}/users',                             [AdminController::class, 'bandsAttachUser'])->name('bands.attach-user');
        Route::delete('bands/{band}/users/{user}',                    [AdminController::class, 'bandsDetachUser'])->name('bands.detach-user');
        Route::patch('bands/{band}/users/{user}/permissions',         [AdminController::class, 'bandsUpdateUserPermissions'])->name('bands.update-user-permissions');
    });
});
