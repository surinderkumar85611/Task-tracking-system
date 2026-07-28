<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function changePassword(Request $request)
    {
        $user = auth()->user();

        if (!Hash::check(
            $request->current_password,
            $user->password
        )) {

            return response()->json([
                'message' => 'Current password is incorrect'
            ], 422);
        }

        $user->password = Hash::make(
            $request->password
        );

        $user->save();

        return response()->json([
            'message' => 'Password updated successfully'
        ]);
    }
    public function profile()
    {
        $user = auth()->user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'department' => $user->department,
            'role' => $user->role,
            'two_factor_enabled' => $user->two_factor_enabled,
            'notification_preferences' => $user->notification_preferences,
        ]);
    }

    public function updateNotificationPreferences(Request $request)
    {
        $user = auth()->user();
        
        $user->notification_preferences = [
            'email' => (bool) $request->input('email', true),
            'tasks' => (bool) $request->input('tasks', true),
            'projects' => (bool) $request->input('projects', true),
            'reports' => (bool) $request->input('reports', false),
        ];
        
        $user->save();

        return response()->json([
            'message' => 'Notification preferences updated successfully'
        ]);
    }
    public function updateProfile(Request $request)
{
    $user = auth()->user();

    $request->validate([
        'name'   => 'required|string|max:255',
        'avatar' => 'nullable|image|mimes:jpeg,png,webp|max:5120', // 5MB
    ]);

    $user->name = $request->name;

    if ($request->hasFile('avatar')) {
        $path = $request->file('avatar')->store('avatars', 'public');
        $user->avatar_url = asset('storage/' . $path);
    }

    $user->save();

    return response()->json([
        'message'    => 'Profile updated successfully',
        'avatar_url' => $user->avatar_url,
    ]);
}
} 

