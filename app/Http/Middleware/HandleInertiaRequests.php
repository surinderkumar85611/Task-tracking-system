<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Workspace;
use App\Models\Notification;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */

    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [

            'workspaces' => fn() =>
            auth()->check()
                ? Workspace::where('owner_id', auth()->id())
                ->orderBy('name')
                ->get()
                : collect(),

            'currentWorkspace' =>
            session('workspace_id'),
            'showWorkspaceModal' =>
            session('show_workspace_modal', false),

            'flash' => [
                'success' => fn() => session('success'),
                'error' => fn() => session('error'),
                'invite_link' => fn() => session('invite_link'),
            ],

            // Unread extension request notifications for the logged-in user
            'extensionNotifications' => fn() =>
            auth()->check()
                ? Notification::where('user_id', auth()->id())
                ->where('is_read', false)
                ->whereIn('type', ['extension_requested', 'extension_approved', 'extension_rejected'])
                ->latest()
                ->take(20)
                ->get()
                ->map(fn($n) => [
                    'id'         => $n->id,
                    'type'       => str_replace('extension_', '', $n->type),
                    'message'    => $n->message,
                    'project_id' => $n->data['project_id'] ?? null,
                ])
                ->values()
                : [],
        ]);
    }
}