<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'role'     => ['required', Rule::in(['ADMIN', 'TL'])],
            'name'     => ['required', 'string', 'min:2', 'max:100'],
            'email'    => ['required', 'email', 'unique:users,email', 'unique:members,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        DB::transaction(function () use ($validated) {

            // `role` is not in User::$fillable, so it is set directly
            $user = new User();
            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->password = $validated['password']; // hashed by the model cast
            $user->role = $validated['role'];
            $user->save();

            // Team leaders also need a members row, because the app finds them by email there
            if ($validated['role'] === 'TL') {
                [$first, $last] = array_pad(explode(' ', trim($validated['name']), 2), 2, '');

                Member::create([
                    'workspace_id' => null,   // admin assigns a workspace later
                    'first_name'   => $first,
                    'last_name'    => $last,
                    'email'        => $validated['email'],
                    'phone'        => '',
                    'department'   => '',
                    'role'         => 'TL',
                    'level'        => 1,
                ]);
            }
        });

        return back()->with(
            'success',
            $validated['role'] === 'TL' ? 'Team Leader created.' : 'Administrator created.'
        );
    }
}