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

// Redirección inicial
Route::redirect('/', '/login');
Route::prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', UserController::class)->except(['create']);
});
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

    Route::get('/forgot-password', fn() => view('auth.forgot-password'))->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

//--------------------------------------------------------------------------
// Rutas Protegidas (requieren login)
//--------------------------------------------------------------------------
Route::middleware('auth')->group(function () {
    Route::get('/home', fn() => view('index'))->name('home');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::resource('bikes', BikeController::class)->except(['destroy']);

    // Rutas para ADMIN - dejar abierta durante pruebas (sin 'can:isAdmin')
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', fn() => view('admin.dashboard'))->name('dashboard');
        Route::resource('users', UserController::class); // <- esto crea admin.users.index, etc.
    });

    // Rutas para Guardias
    Route::middleware('can:isGuard')->group(function () {
        Route::get('/guard/dashboard', fn() => view('guard.dashboard'))->name('guard.dashboard');
    });

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
