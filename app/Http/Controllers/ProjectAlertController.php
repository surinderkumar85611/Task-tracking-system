<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectAlertController extends Controller
{
    public function getUnreadAlerts(Request $request)
    {
        $user = $request->user();

        $alerts = DB::table('project_alerts')
            ->where('user_id', $user->id)
            ->where('is_read', false)
            ->get();

        DB::table('project_alerts')
            ->where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'alerts' => $alerts
        ]);
    }
}