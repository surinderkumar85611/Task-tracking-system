<?php
use App\Http\Controllers\InvitationController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\GoogleController;
Route::get('/', function () {

    if (auth()->check()) {
        return redirect('/dashboard');
    }

    return redirect('/login');
});

Route::get('/login', fn() => Inertia::render('Auth/Login'))
    ->name('login');

Route::get('/register', fn() => Inertia::render('Auth/Register'));

Route::get('/forgot-password', fn() => Inertia::render('Auth/ForgotPassword'));

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

Route::get('/reset-password', function () {
    return redirect('/login');
});

Route::get('/reset-password/{token}', function ($token, Request $request) {

    if (!$token || !$request->email) {
        return redirect('/login');
    }

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
        $request->only(
            'email',
            'password',
            'password_confirmation',
            'token'
        ),
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

    Route::get('/dashboard', [AdminController::class, 'dashboard'])
        ->name('dashboard');

    Route::get('/member', [MemberController::class, 'index']);
    Route::post('/member', [MemberController::class, 'store']);

    Route::put(
        '/members/{member}/assign',
        [MemberController::class, 'assignMember']
    );

    Route::post('/workspace', [WorkspaceController::class, 'store']);
    Route::post('/workspace/select', [WorkspaceController::class, 'select']);

    Route::get('/project', [ProjectController::class, 'index']);
    Route::post('/project', [ProjectController::class, 'store']);

    Route::delete(
        '/project/{project}',
        [ProjectController::class, 'destroy']
    );

    Route::post('/logout', function (Request $request) {

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');

    })->name('logout');
});

Route::post('/invite/generate', [InvitationController::class, 'generate']);
Route::get('/invite/accept/{token}', [InvitationController::class, 'accept']);

Route::get('/auth/google/redirect', [GoogleController::class, 'redirect']);
Route::get('/auth/google/callback', [GoogleController::class, 'callback']);
