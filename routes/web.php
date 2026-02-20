<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\BookingApiController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BookingCheckController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\MyBookingController;
use App\Http\Controllers\AgendaController;

/*
|--------------------------------------------------------------------------
| Public Route
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Temporary Seeder Route (Production Safe with Key)
|--------------------------------------------------------------------------
| 1) Add SEED_KEY in Railway Variables (meeting-room service)
| 2) Run: https://your-domain/run-seed?key=SEED_KEY_VALUE
| 3) After success, REMOVE this route and push again
*/
Route::get('/run-seed', function () {
    abort_unless(request('key') === env('SEED_KEY'), 403);

    Artisan::call('db:seed', [
        '--class' => 'Database\\Seeders\\RoomSeeder',
        '--force' => true,
    ]);

    return 'Room seeded!';
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', fn() => redirect()->route('calendar'))
        ->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Calendar
    |--------------------------------------------------------------------------
    */

    Route::get('/calendar', [CalendarController::class, 'index'])
        ->name('calendar');

    Route::get('/api/bookings', [BookingApiController::class, 'index'])
        ->name('api.bookings');

    /*
    |--------------------------------------------------------------------------
    | Conflict Check API (All Auth Users)
    |--------------------------------------------------------------------------
    */

    Route::prefix('api/bookings')->group(function () {
        Route::get('/check-conflict', [BookingCheckController::class, 'checkConflict'])
            ->name('api.bookings.check-conflict');

        Route::get('/available-slots', [BookingCheckController::class, 'availableSlots'])
            ->name('api.bookings.available-slots');
    });

    /*
    |--------------------------------------------------------------------------
    | PIC Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware(['role:PIC'])->group(function () {

        Route::post('/bookings', [BookingController::class, 'store'])
            ->name('bookings.store');

        Route::get('/my-bookings', [MyBookingController::class, 'index'])
            ->name('my_bookings.index');

        Route::get('/agenda', [AgendaController::class, 'index'])
            ->name('agenda');

        Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])
            ->name('bookings.cancel');
    });

    /*
    |--------------------------------------------------------------------------
    | TU Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware(['role:TU'])->group(function () {

        Route::get('/approvals', [ApprovalController::class, 'index'])
            ->name('approvals.index');

        Route::post('/approvals/{booking}/approve', [ApprovalController::class, 'approve'])
            ->name('approvals.approve');

        Route::post('/approvals/{booking}/reject', [ApprovalController::class, 'reject'])
            ->name('approvals.reject');
    });

    /*
    |--------------------------------------------------------------------------
    | Profile (Breeze)
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Display Monitor Routes (Public - no auth required)
|--------------------------------------------------------------------------
*/

Route::get('/display', [\App\Http\Controllers\DisplayController::class, 'show'])->name('display');
Route::get('/display/{roomId}', [\App\Http\Controllers\DisplayController::class, 'show'])->name('display.room');

// Public API for display monitor (no auth)
Route::get('/api/display-bookings', [BookingApiController::class, 'index'])->name('api.display.bookings');

require __DIR__ . '/auth.php';