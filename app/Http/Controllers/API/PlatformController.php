<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\UpdateSettingsrequest;
use App\Http\Resources\JsonResource;
use App\Http\Resources\Platform\PlatformResource;
use App\Http\Resources\Platform\UserPlatformResource;
use App\Models\ActivityLog;
use App\Models\Platform;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class PlatformController extends Controller
{
    private const CACHE_TTL = 3600;


    public function index()
    {
        $platforms = Cache::remember('platforms_list', self::CACHE_TTL, fn() => Platform::active()->get());
        return response()->json(PlatformResource::collection($platforms));
    }


    public function userPlatforms()
    {
        $user = Auth::user();
        $platforms = Platform::active()->get();
        $activePlatforms = $user->platforms()->wherePivot('is_active', true)->pluck('platform_id');


        $platforms->map(function ($platform) use ($activePlatforms) {
            $platform->is_active_for_user = $activePlatforms->contains($platform->id);
        });

        return response()->json(UserPlatformResource::collection($platforms->load('settingskeys')));
    }

    public function togglePlatform(Request $request, Platform $platform)
    {
        $user = $request->user();

        $pivot = $user->platforms()->where('platform_id', $platform->id)->first()?->pivot;
        $isActive = $pivot ? !$pivot->is_active : true;

        $user->platforms()->syncWithoutDetaching([
            $platform->id => ['is_active' => $isActive],
        ]);

        $platform->is_active_for_user = $isActive;

        ActivityLog::log($user->id, 'platform_toggled', $platform->name . ' is toggled to ' . ($isActive ? 'active' : 'inactive'));

        return response()->json(JsonResource::noResourceItem($platform, ['id', 'name', 'type', 'is_active_for_user']));
    }


    public function getSettings(Platform $platform)
    {
        $user = Auth::user();
        $pivot = $user->platforms()->where('platform_id', $platform->id)->first()?->pivot;
        if (!$pivot || !$pivot->is_active) {
            return response()->json([
                'message' => 'Platform is not active, please activate it first'
            ], 404);
        }

        return response()->json(JsonResource::noResourceItem($pivot, ['settings']));
    }


    public function updateSettings(UpdateSettingsrequest $request, Platform $platform)
    {
        $data = $request->validated();

        $user = Auth::user();

        $pivot = $user->platforms()->where('platform_id', $platform->id)->first()?->pivot;
        if (!$pivot || !$pivot->is_active) {
            return response()->json([
                'message' => 'Platform is not active, please activate it first'
            ], 404);
        }

        $user->platforms()->updateExistingPivot($platform->id, [
            'settings' => $data['settings'],
        ]);

        ActivityLog::log($user->id, 'platform_settings_updated', $platform->name . ' settings updated');

        return response()->json([
            'message' => 'Platform settings updated successfully',
            'settings' => $pivot->refresh()->settings
        ]);
    }
}
