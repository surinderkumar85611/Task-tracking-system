<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MemberController;

Route::get('/', fn () => Inertia::render('Dashboard'));

Route::get('/login', fn () => Inertia::render('Auth/Login'));
Route::get('/register', fn () => Inertia::render('Auth/Register'));
Route::get('/forgot-password', fn () => Inertia::render('Auth/ForgotPassword'));

Route::get('/project', fn () => Inertia::render('Projects'));

Route::get('/member', [MemberController::class, 'index']);
Route::post('/member', [MemberController::class, 'store']);
Route::put(
    '/members/{member}/assign',
    [MemberController::class, 'assignMember']
);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/forgot-password', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
    ]);

    $status = Password::sendResetLink(
        $request->only('email')
    );

    return back()->with([
        'status' => $status
    ]);
});

Route::get('/reset-password/{token}', function ($token, Request $request) {
    return Inertia::render('Auth/ResetPassword', [
        'token' => $token,
        'email' => $request->email,
    ]);
})->name('password.reset');

Route::post('/reset-password', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required|min:6|confirmed',
        'token' => 'required',
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password),
            ])->save();

            event(new PasswordReset($user));
        }
    );

    return $status === Password::PASSWORD_RESET
        ? redirect('/login')->with('success', 'Password reset successful')
        : back()->withErrors(['email' => [__($status)]]);
});


Route::middleware(['auth'])->group(function () {
    
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])
        ->name('admin.dashboard');

});