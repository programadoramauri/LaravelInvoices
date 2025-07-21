<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', fn () => 'Login Page')->name('login');
    Route::get('/register', fn () => 'Register Page')->name('register');
    Route::get('/forgot-password', fn () => 'Forgot password page')->name('password.request');
    Route::get('/reset-password/{token}', fn () => "Reset page for token {$token}")->name('password.reset');
    Route::get('/two-factor-challenge', fn () => '2FA challenge page')->name('two-factor.login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', fn () => 'Logged user dashboard');

    Route::middleware('role:admin')->group(function () {
        Route::get('/admin', fn () => 'Admin area');
        Route::get('/admin/config', fn () => 'Admin configs');
    });

    Route::middleware('role:admin|editor')->group(function () {
        Route::get('/panel', fn () => 'Admin or editor panel');
    });
});
