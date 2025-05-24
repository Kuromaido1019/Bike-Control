<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\BikeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\BikeController as AdminBikeController;

// Redirección inicial
Route::redirect('/', '/login');

// Endpoint para bicicletas por RUT (AJAX)
Route::get('/api/bicicletas-por-rut/{rut}', [\App\Http\Controllers\AccessController::class, 'getBikesByRut']);

// Endpoint para asociar bicicleta (AJAX)
Route::post('/api/asociar-bicicleta', [\App\Http\Controllers\BikeController::class, 'associateBike']);

//--------------------------------------------------------------------------
// Rutas de Autenticación
//--------------------------------------------------------------------------
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', fn() => view('auth.register'))->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    Route::get('/forgot-password', fn() => view('auth.passwords.email'))->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

//--------------------------------------------------------------------------
// Rutas Protegidas (requieren login)
//--------------------------------------------------------------------------
Route::middleware('auth')->group(function () {
    Route::get('/home', [\App\Http\Controllers\DashboardController::class, 'index'])->name('home');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::resource('bikes', BikeController::class)->except(['destroy']);
    Route::post('/bicicletas', [BikeController::class, 'store'])->name('bike.store');
    Route::delete('/bicicletas/{bike}', [BikeController::class, 'destroy'])->name('bike.destroy');

    // Rutas para ADMIN
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('bikes', AdminBikeController::class);

        Route::get('/dashboard', fn() => view('admin.dashboard'))->name('dashboard');

        Route::get('/control-acceso', [\App\Http\Controllers\AccessController::class, 'index'])->name('control-acceso');
        Route::post('/control-acceso', [\App\Http\Controllers\AccessController::class, 'store'])->name('control-acceso.store');
        Route::put('/control-acceso/{access}', [\App\Http\Controllers\AccessController::class, 'update'])->name('control-acceso.update');
        Route::delete('/control-acceso/{access}', [\App\Http\Controllers\AccessController::class, 'destroy'])->name('control-acceso.destroy');
        Route::post('/control-acceso/quick', [\App\Http\Controllers\AccessController::class, 'quickAccess'])->name('control-acceso.quick');
        Route::post('/control-acceso/quick/user', [\App\Http\Controllers\AccessController::class, 'quickUser'])->name('control-acceso.quick.user');
        Route::post('/control-acceso/quick/bike', [\App\Http\Controllers\AccessController::class, 'quickBike'])->name('control-acceso.quick.bike');
        Route::delete('/control-acceso/quick/cancel/{user}', [\App\Http\Controllers\AccessController::class, 'quickCancel']);

        // Ruta para generación de reportes PDF desde el dashboard admin
        Route::get('/report', [\App\Http\Controllers\DashboardController::class, 'report'])->name('report');
    });

    // Rutas para Guardias
    Route::get('/guard/control-acceso', [\App\Http\Controllers\AccessController::class, 'index'])->name('guard.control-acceso');
    Route::post('/guard/control-acceso', [\App\Http\Controllers\AccessController::class, 'store'])->name('guard.control-acceso.store');
    Route::put('/guard/control-acceso/{access}', [\App\Http\Controllers\AccessController::class, 'update'])->name('guard.control-acceso.update');
    Route::post('/guard/control-acceso/quick', [\App\Http\Controllers\AccessController::class, 'quickAccess'])->name('guard.control-acceso.quick');
    Route::post('/guard/control-acceso/quick/user', [\App\Http\Controllers\AccessController::class, 'quickUser'])->name('guard.control-acceso.quick.user');
    Route::post('/guard/control-acceso/quick/bike', [\App\Http\Controllers\AccessController::class, 'quickBike'])->name('guard.control-acceso.quick.bike');
    Route::delete('/guard/control-acceso/quick/cancel/{user}', [\App\Http\Controllers\AccessController::class, 'quickCancel']);

    // Rutas para Visitantes
    Route::middleware('can:isVisitor')->group(function () {
        Route::get('/profile', fn() => view('profile'))->name('profile');

    });

    // Ruta protegida para mostrar la ficha del usuario logeado
    Route::get('/mi-usuario', function () {
        return view('visitante.mis-datos');
    })->name('visitante.mis-datos')->middleware('auth');

    // Rutas comunes para todos
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
});

// Fallback para 404
Route::fallback(fn() => view('errors.404'))->name('404');
