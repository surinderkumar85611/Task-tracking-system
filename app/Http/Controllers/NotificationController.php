<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        return Notification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
    }
}