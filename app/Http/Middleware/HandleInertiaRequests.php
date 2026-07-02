<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Workspace;

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
        ]);
    }
}
