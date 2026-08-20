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
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminRoomController;


/*
|--------------------------------------------------------------------------
| Public Route
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api/maintenance-rooms', function () {
    return \App\Models\Room::where('maintenance', true)->pluck('id');
});

/*
|--------------------------------------------------------------------------
| Temporary Seeder Route (Production Safe with Key)
|--------------------------------------------------------------------------
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

    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
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

        Route::post('/approvals/{booking}/cancel-approve', [ApprovalController::class, 'cancelApprove'])
            ->name('approvals.cancelApprove');
    });

    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware(['role:Admin'])->prefix('admin')->name('admin.')->group(function () {

        Route::get('/users', [AdminUserController::class, 'index'])
            ->name('users.index');

        Route::post('/users', [AdminUserController::class, 'store'])
            ->name('users.store');

        Route::patch('/users/{user}/role', [AdminUserController::class, 'updateRole'])
            ->name('users.updateRole');

        Route::patch('/users/{user}/room', [AdminUserController::class, 'updateRoom'])
            ->name('users.updateRoom');

        Route::patch('/users/{user}/profile', [AdminUserController::class, 'updateProfile'])
            ->name('users.updateProfile');

        Route::patch('/users/{user}/password', [AdminUserController::class, 'updatePassword'])
            ->name('users.updatePassword');

        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])
            ->name('users.destroy');
        Route::patch('/rooms/{room}/maintenance', [AdminRoomController::class, 'toggleMaintenance'])
            ->name('rooms.maintenance');
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

Route::get('/display', [\App\Http\Controllers\DisplayController::class, 'show'])
    ->name('display');

Route::get('/display/{roomId}', [\App\Http\Controllers\DisplayController::class, 'show'])
    ->name('display.room');

Route::get('/api/display-bookings', [BookingApiController::class, 'index'])
    ->name('api.display.bookings');

require __DIR__ . '/auth.php';