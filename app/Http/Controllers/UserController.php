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
    ]);
}
}
