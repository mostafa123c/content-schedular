<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityLog\ActivityLogResource;
use App\Http\Resources\JsonResource;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{

    public function index()
    {
        $user = Auth::user();
        $activityLogs = $user->activityLogs()->with('user')->filter()->latest()->paginate(10);

        return response()->json(ActivityLogResource::pagination($activityLogs));
    }

    public function getActionTypes()
    {
        $actionTypes = ActivityLog::distinct()->pluck('action');
        return response()->json(['actionLogTypes' => $actionTypes]);
    }
}
