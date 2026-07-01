<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Member;
use Symfony\Component\HttpFoundation\Response;

class MemberMiddleware
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {

        $member = Member::where(
            'email',
            auth()->user()->email
        )->first();

        if (
            !$member ||
            $member->role !== 'Member'
        ) {
            abort(403, 'Access Denied');
        }

        return $next($request);
    }
}