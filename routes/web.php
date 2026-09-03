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
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TaskAttachmentController;
use App\Http\Controllers\EditorController;
use App\Http\Controllers\SuperAdmin\AuthController as SuperAdminAuthController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\Member\DashboardController as MemberDashboardController;
use App\Http\Controllers\Member\TaskController as MemberTaskController;
use App\Http\Controllers\Member\ProjectController as MemberProjectController;
use App\Http\Controllers\Member\SettingsController as MemberSettingsController;
use App\Http\Controllers\SuperAdminController;

Route::prefix('super-admin')->name('super-admin.')->group(function () {

    Route::group([], function () {

        Route::get('/login', [SuperAdminAuthController::class, 'showLogin'])
            ->name('login');

        Route::post('/login', [SuperAdminAuthController::class, 'login']);
    });

    Route::middleware('auth:super_admin')->group(function () {

        Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])
            ->name('dashboard');

        Route::post('/logout', [SuperAdminAuthController::class, 'logout'])
            ->name('logout');
    });
});

Route::get('/', function () {
    if (Auth::check()) return redirect('/dashboard');
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

Route::middleware(['auth', 'no-cache'])->group(function () {

    Route::get('/dashboard', function () {
        $user = Auth::user();
        $member = Member::where('email', $user->email)->first();

        if ($member && $member->role === 'TL') {
            return app(LeaderDashboardController::class)->index();
        }

        return app(AdminController::class)->dashboard();
    })->name('dashboard');

    Route::get('/member', function () {
        $user = Auth::user();
        $member = Member::where('email', $user->email)->first();
        if ($member && $member->role !== 'ADMIN') abort(403);
        return app(MemberController::class)->index();
    });

    Route::post('/member', function (Request $request) {
        $user = Auth::user();
        $member = Member::where('email', $user->email)->first();

        if ($member && $member->role !== 'ADMIN') abort(403);

        return app(MemberController::class)->store($request);
    });

    Route::post('/workspace', function () {
        $user = Auth::user();
        $member = Member::where('email', $user->email)->first();
        if ($member && $member->role !== 'ADMIN') abort(403);
        return app(WorkspaceController::class)->store(request());
    });

    Route::post('/workspace/select', [WorkspaceController::class, 'select']);

    Route::get('/project', function () {
        $user = Auth::user();
        $member = Member::where('email', $user->email)->first();
        if ($member && $member->role !== 'ADMIN') abort(403);
        return app(ProjectController::class)->index();
    });

    Route::post('/project', function () {
        $user = Auth::user();
        $member = Member::where('email', $user->email)->first();
        if ($member && $member->role !== 'ADMIN') abort(403);
        return app(ProjectController::class)->store(request());
    });

    Route::put('/project/{project}', [ProjectController::class, 'update']);
    Route::delete('/project/{project}', [ProjectController::class, 'destroy']);
    Route::get('/project/{project}/export', [ProjectController::class, 'export'])
        ->name('project.export');

    Route::middleware(['auth', 'no-cache'])->prefix('task')->group(function () {
        Route::post('/', [TaskController::class, 'store']);
        Route::put('/{task}', [TaskController::class, 'update'])->name('task.update');
        Route::delete('/{id}', [TaskController::class, 'destroy'])->name('task.destroy');
    });

    Route::post('/task', [TaskController::class, 'store']);
    Route::put('/task/{task}', [TaskController::class, 'update'])->name('task.update');
    Route::delete('/task/{id}', [TaskController::class, 'destroy'])->name('task.destroy');

    Route::post('/logout', function (Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->to('/login');
    });

    Route::get('/settings', function () {
        $user = Auth::user();

        if ($user->role === 'ADMIN') {
            return app(SettingsController::class)->index();
        }

        $member = Member::where('email', $user->email)->first();

        if ($member && $member->role === 'TL') {
            return app(\App\Http\Controllers\Leader\SettingsController::class)->index();
        }

        abort(403, 'Unauthorized access');
    })->name('settings');

    Route::get('/projects', function () {
        $user = Auth::user();
        $member = Member::where('email', $user->email)->first();

        if (!$member || $member->role !== 'TL') abort(403);

        return app(LprojectController::class)->index();
    });

    Route::get('/team', function () {
        $user = Auth::user();
        $member = Member::where('email', $user->email)->first();

        if (!$member || $member->role !== 'TL') abort(403);

        return app(TeamController::class)->index();
    });
    Route::post('/team/request', [TeamRequestController::class, 'store'])->name('team.request.store');
});

Route::post('/user/change-password', [UserController::class, 'changePassword']);
Route::get('/user/profile', [UserController::class, 'profile']);
Route::post('/user/profile', [UserController::class, 'updateProfile'])->middleware(['auth', 'no-cache']);

Route::middleware('auth')->prefix('member')->group(function () {
    Route::get('/me', [MemberController::class, 'me']);
    Route::put('/{member}', [MemberController::class, 'update']);
    Route::put('/{member}/assign', [MemberController::class, 'assignMember']);
    Route::put('/{member}/assign-workspace', [MemberController::class, 'assignWorkspace']);
    Route::put('/{member}/independent', [MemberController::class, 'makeIndependent'])->name('members.make-independent');
    Route::delete('/{member}', [MemberController::class, 'destroy']);
});

Route::post('/invite/generate', [InvitationController::class, 'generate']);
Route::get('/invite/accept/{token}', [InvitationController::class, 'accept']);

Route::get('/complete-profile', [AuthController::class, 'showCompleteProfile']);
Route::post('/complete-profile', [AuthController::class, 'completeProfile']);

Route::middleware(['auth', 'no-cache'])->prefix('workspaces')->group(function () {
    Route::get('/', [WorkspaceController::class, 'index']);
    Route::get('/{workspace}', [WorkspaceController::class, 'show']);
    Route::put('/{workspace}', [WorkspaceController::class, 'update']);
    Route::delete('/{workspace}', [WorkspaceController::class, 'destroy']);
});

Route::get('/two-factor-challenge', [AuthController::class, 'showTwoFactorChallenge'])->name('two-factor.challenge');
Route::post('/two-factor-challenge', [AuthController::class, 'verifyTwoFactorChallenge']);

Route::middleware(['auth', 'no-cache'])->group(function () {
    Route::get('/user/unread-alerts', [\App\Http\Controllers\ProjectAlertController::class, 'getUnreadAlerts']);
});

Route::middleware(['auth', 'no-cache'])->prefix('leader')->group(function () {

    Route::get('/2fa/generate', [TwoFactorController::class, 'generateSecret']);
    Route::post('/2fa/enable', [TwoFactorController::class, 'enable']);
    Route::post('/2fa/disable', [TwoFactorController::class, 'disable']);
});

Route::get('/task-fields', [TaskController::class, 'getTaskFields']);
Route::post('/task/import', [TaskController::class, 'import'])->name('task.import');

Route::post(
    '/dashboard/widgets/reorder',
    [DashboardController::class, 'reorderWidgets']
)->middleware(['auth', 'no-cache']);

Route::post('/editor/upload', [EditorController::class, 'upload']);

Route::prefix('notifications')->middleware(['auth', 'no-cache'])->group(function () {
    Route::get('/', [NotificationController::class, 'index']);
    Route::put('/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::put('/read-all', [NotificationController::class, 'markAllRead']);
});

Route::post('/api/v1/task-attachments', [TaskAttachmentController::class, 'upload']);

Route::post(
    '/ckeditor/upload',
    [LProjectController::class, 'uploadEditorImage']
)->middleware(['auth', 'no-cache']);

Route::middleware(['auth'])->group(function () {

    Route::put('/user/notification-preferences', [UserController::class, 'updateNotificationPreferences']);

    Route::put('/notifications/{id}/read', function ($id) {
        \App\Models\Notification::where('id', $id)->where('user_id', Auth::id())->update(['is_read' => 1]);
        return back();
    });

    Route::put('/notifications/read-all', function () {
        \App\Models\Notification::where('user_id', Auth::id())->update(['is_read' => 1]);
        return back();
    });
});

Route::put('/user/notification-preferences', [UserController::class, 'updateNotificationPreferences']);

Route::post(
    '/tasks/{id}/reply',
    [TaskController::class, 'reply']
);

Route::post(
    '/tasks/{id}/react',
    [TaskController::class, 'react']
);

Route::put(
    '/notifications/{id}/read',
    [NotificationController::class, 'markAsRead']
)->name('notifications.read');

Route::put(
    '/notifications/read-all',
    [NotificationController::class, 'markAllRead']
)->name('notifications.readAll');

Route::put(
    '/notifications/{id}/read',
    [NotificationController::class, 'markAsRead']
)->middleware(['auth', 'no-cache']);

Route::middleware(['auth', 'no-cache'])
    ->prefix('member')
    ->name('member.')
    ->group(function () {

        Route::get('/dashboard', [MemberDashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/tasks', [MemberTaskController::class, 'index'])
            ->name('tasks');

        Route::get('/projects', [MemberProjectController::class, 'index'])
            ->name('projects');

        Route::get('/settings', [MemberSettingsController::class, 'index'])
            ->name('settings');
    });

Route::prefix('super-admin')->middleware(['auth', 'super_admin'])->group(function () {
    // ...your existing routes (dashboard, login, logout, admin, teams/{team}/members/{member})...

    Route::get('/teams', [SuperAdminController::class, 'teams']);
    Route::delete('/teams/{team}', [SuperAdminController::class, 'destroyTeam']);
    Route::delete('/admin/{user}', [SuperAdminController::class, 'destroyAdmin']);

    Route::get('/projects', [SuperAdminController::class, 'projects']);
    Route::patch('/projects/{project}', [SuperAdminController::class, 'updateProjectProgress']); 
    Route::get('/workspaces', [SuperAdminController::class, 'workspaces']);
    Route::post('/workspaces', [SuperAdminController::class, 'storeWorkspace']);
    Route::put('/workspaces/{workspace}', [SuperAdminController::class, 'updateWorkspace']);
    Route::delete('/workspaces/{workspace}', [SuperAdminController::class, 'destroyWorkspace']);

    Route::get('/settings', [SuperAdminController::class, 'settings']);
    Route::get('/profile', [SuperAdminController::class, 'getProfile']);
    Route::post('/profile', [SuperAdminController::class, 'updateProfile']);
    Route::post('/change-password', [SuperAdminController::class, 'changePassword']);
});
