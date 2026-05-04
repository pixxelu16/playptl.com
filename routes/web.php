<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\LeagueController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Route::get('/gallery', function () {
    return view('gallery');
})->name('gallery');

Route::get('/charity', function () {
    return view('charity');
})->name('charity');

Route::get('/league', [LeagueController::class, 'index'])->name('league');
Route::get('/league/group/{slug}', [LeagueController::class, 'show'])->name('league.group');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardRedirectController::class)->name('dashboard');

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');
    });

    Route::middleware('role:organiser')->prefix('organiser')->name('organiser.')->group(function () {
        Route::get('/dashboard', function () {
            return view('organiser.dashboard');
        })->name('dashboard');
    });

    Route::middleware('role:player')->prefix('player')->name('player.')->group(function () {
        Route::get('/dashboard', function () {
            return view('player.dashboard');
        })->name('dashboard');
    });

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
