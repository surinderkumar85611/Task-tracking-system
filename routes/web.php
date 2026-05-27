<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use Illuminate\Foundation\Application;

Route::get('/', fn () => Inertia::render('Welcome'));

Route::get('/login', fn () => Inertia::render('Auth/Login'));
Route::get('/register', fn () => Inertia::render('Auth/Register'));
Route::get('/forgot-password', fn () => Inertia::render('Auth/ForgotPassword'));
