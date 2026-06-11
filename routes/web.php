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
use App\Http\Controllers\TaskController;
use App\Http\Controllers\Leader\LprojectController;
use App\Http\Controllers\Leader\TeamController;
use App\Http\Controllers\TeamRequestController;
use App\Http\Controllers\Leader\TwoFactorController;

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

    Route::put(
        '/project/{project}',
        [ProjectController::class, 'update']
    );

    Route::delete(
        '/project/{project}',
        [ProjectController::class, 'destroy']
    );

    Route::post(
        '/task',
        [TaskController::class, 'store']
    );

    Route::put('/task/{task}', [TaskController::class, 'update'])->name('task.update');
    Route::delete('/task/{id}', [TaskController::class, 'destroy'])->name('task.destroy');

    Route::post('/logout', function (Request $request) {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    })->name('logout');


});
use App\Http\Controllers\SettingsController;

Route::middleware(['auth'])->group(function () {

    Route::get('/settings', [SettingsController::class, 'index'])
        ->name('settings');

});


use App\Http\Controllers\LeaderDashboardController;
Route::get(
    '/leader-dashboard',
    [LeaderDashboardController::class, 'index']
)->middleware([
    'auth',
    'tl'
]);
Route::get('/leader/projects', function () {
    return Inertia::render('Leader/Projects');
})->middleware(['auth', 'tl']);


Route::get('/leader/settings', function () {
    return Inertia::render('Leader/Settings');
})->middleware(['auth', 'tl']);


use App\Http\Controllers\UserController;

Route::post(
    '/user/change-password',
    [UserController::class, 'changePassword']
);


Route::get(
    '/user/profile',
    [UserController::class, 'profile']
);
Route::get('/member/me', [MemberController::class, 'me']);
Route::put('/member/{member}', [MemberController::class, 'update']);
Route::post('/invite/generate', [InvitationController::class, 'generate']);
Route::get('/invite/accept/{token}', [InvitationController::class, 'accept']);

Route::get('/complete-profile', [AuthController::class, 'showCompleteProfile']);
Route::post('/complete-profile', [AuthController::class, 'completeProfile']);
Route::get('/workspaces', [WorkspaceController::class, 'index']);
Route::get('/workspaces/{workspace}', [WorkspaceController::class, 'show']);
Route::put('/workspaces/{workspace}', [WorkspaceController::class, 'update']);
Route::delete('/workspaces/{workspace}', [WorkspaceController::class, 'destroy']);

Route::get('/leader/projects', [LProjectController::class, 'index'])
    ->middleware(['auth', 'tl'])
    ->name('leader.projects');

Route::middleware(['auth', 'tl'])->group(function () {
    Route::get('/leader/team', [TeamController::class, 'index'])
        ->name('leader.team');
});

Route::post('/team/request', [TeamRequestController::class, 'store']);
Route::get('/team/request', [TeamRequestController::class, 'index']);

Route::middleware(['auth', 'tl'])->prefix('leader')->group(function () {

    Route::get('/settings', [\App\Http\Controllers\Leader\SettingsController::class, 'index']);

    Route::put('/settings/profile', [\App\Http\Controllers\Leader\SettingsController::class, 'updateProfile']);

    Route::post('/settings/password', [\App\Http\Controllers\Leader\SettingsController::class, 'updatePassword']);

    Route::put('/settings/notifications', [\App\Http\Controllers\Leader\SettingsController::class, 'updateNotifications']);
});

Route::middleware(['auth'])->prefix('leader')->group(function () {

    Route::get('/2fa/generate', [TwoFactorController::class, 'generateSecret']);
    Route::post('/2fa/enable', [TwoFactorController::class, 'enable']);
    Route::post('/2fa/disable', [TwoFactorController::class, 'disable']);

});

Route::get('/task-fields', [TaskController::class, 'getTaskFields']);
Route::post('/task/import', [TaskController::class, 'import'])
    ->name('task.import');

