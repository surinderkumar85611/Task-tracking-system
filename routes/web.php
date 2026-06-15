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

use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LeaderDashboardController;
use App\Models\Member;

use App\Http\Controllers\DashboardController;


Route::get('/', function () {
    if (auth()->check()) return redirect('/dashboard');
    return redirect('/login');
});

Route::get('/login', fn() => Inertia::render('Auth/Login'))->name('login');
Route::get('/register', fn() => Inertia::render('Auth/Register'));
Route::get('/forgot-password', fn() => Inertia::render('Auth/ForgotPassword'));

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/forgot-password', function (Request $request) {
    $request->validate(['email' => 'required|email']);
    $status = Password::sendResetLink($request->only('email'));
    return back()->with(['status' => $status]);
});

Route::get('/reset-password', fn() => redirect('/login'));

Route::get('/reset-password/{token}', function ($token, Request $request) {
    if (!$token || !$request->email) return redirect('/login');

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

    Route::get('/dashboard', function () {

        $user = auth()->user();
        $member = Member::where('email', $user->email)->first();

        if ($member && $member->role === 'TL') {
            return app(LeaderDashboardController::class)->index();
        }

        return app(AdminController::class)->dashboard();

    })->name('dashboard');

    Route::get('/member', function () {
        $user = auth()->user();
        $member = Member::where('email', $user->email)->first();
        if ($member && $member->role !== 'ADMIN') abort(403);
        return app(MemberController::class)->index();
    });

    Route::post('/workspace', function () {
        $user = auth()->user();
        $member = Member::where('email', $user->email)->first();
        if ($member && $member->role !== 'ADMIN') abort(403);
        return app(WorkspaceController::class)->store(request());
    });

    Route::post('/workspace/select', [WorkspaceController::class, 'select']);

    Route::get('/project', function () {
        $user = auth()->user();
        $member = Member::where('email', $user->email)->first();
        if ($member && $member->role !== 'ADMIN') abort(403);
        return app(ProjectController::class)->index();
    });

    Route::post('/project', function () {
        $user = auth()->user();
        $member = Member::where('email', $user->email)->first();
        if ($member && $member->role !== 'ADMIN') abort(403);
        return app(ProjectController::class)->store(request());
    });

    Route::put('/project/{project}', [ProjectController::class, 'update']);
    Route::delete('/project/{project}', [ProjectController::class, 'destroy']);

    Route::post('/task', [TaskController::class, 'store']);
    Route::put('/task/{task}', [TaskController::class, 'update'])->name('task.update');
    Route::delete('/task/{id}', [TaskController::class, 'destroy'])->name('task.destroy');

    Route::post('/logout', function (Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    });

});

Route::middleware(['auth'])->group(function () {

    Route::get('/settings', function () {

        $user = auth()->user();
        $member = Member::where('email', $user->email)->first();

        if (!$member) abort(403);

        if ($member->role === 'TL') {
            return app(\App\Http\Controllers\Leader\SettingsController::class)->index();
        }

        return app(SettingsController::class)->index();

    })->name('settings');

});

Route::middleware(['auth'])->group(function () {

    Route::get('/projects', function () {

        $user = auth()->user();
        $member = Member::where('email', $user->email)->first();

        if (!$member || $member->role !== 'TL') abort(403);

        return app(LprojectController::class)->index();

    });

    Route::get('/team', function () {

        $user = auth()->user();
        $member = Member::where('email', $user->email)->first();

        if (!$member || $member->role !== 'TL') abort(403);

        return app(TeamController::class)->index();

    });

});

Route::post('/user/change-password', [UserController::class, 'changePassword']);
Route::get('/user/profile', [UserController::class, 'profile']);

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

Route::get('/task-fields', [TaskController::class, 'getTaskFields']);
Route::post('/task/import', [TaskController::class, 'import'])->name('task.import');

Route::get('/two-factor-challenge', [AuthController::class, 'showTwoFactorChallenge'])->name('two-factor.challenge');
Route::post('/two-factor-challenge', [AuthController::class, 'verifyTwoFactorChallenge']);

Route::middleware(['auth'])->group(function () {

    Route::get('/user/notification-preferences', [\App\Http\Controllers\Leader\SettingsController::class, 'index']);

    Route::put('/user/notification-preferences', [\App\Http\Controllers\Leader\SettingsController::class, 'updateNotifications']);

    Route::get('/user/unread-alerts', [\App\Http\Controllers\ProjectAlertController::class, 'getUnreadAlerts']);
});

Route::middleware(['auth'])->prefix('leader')->group(function () {

    Route::get('/2fa/generate', [TwoFactorController::class, 'generateSecret']);
    Route::post('/2fa/enable', [TwoFactorController::class, 'enable']);
    Route::post('/2fa/disable', [TwoFactorController::class, 'disable']);

});

Route::get('/task-fields', [TaskController::class, 'getTaskFields']);
Route::post('/task/import', [TaskController::class, 'import'])
    ->name('task.import');

Route::get('/two-factor-challenge', [\App\Http\Controllers\AuthController::class, 'showTwoFactorChallenge'])->name('two-factor.challenge');
Route::post('/two-factor-challenge', [\App\Http\Controllers\AuthController::class, 'verifyTwoFactorChallenge']);

Route::post(
    '/dashboard/widgets/reorder',
    [DashboardController::class, 'reorderWidgets']
)->middleware('auth');

Route::post('/editor/upload', [EditorController::class, 'upload']);
