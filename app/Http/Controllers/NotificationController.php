<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        return Notification::where('user_id', $user->id)
            ->where('workspace_id', session('workspace_id'))
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function markAsRead($id)
{
    $user = auth()->user();

    $notification = Notification::where('id', $id)
        ->where('user_id', $user->id)
        ->firstOrFail();

    $notification->update([
        'is_read' => true
    ]);

    return back();
}

   public function markAllRead()
{
    $user = auth()->user();

    Notification::where('user_id', $user->id)
        ->where('workspace_id', session('workspace_id'))
        ->update([
            'is_read' => true
        ]);

    return back();
}
}
