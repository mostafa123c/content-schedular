<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\Post\PostsPerPlatformResource;
use App\Http\Resources\Post\UpcomingPostsResource;
use App\Models\PlatformPost;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    private const CACHE_TTL = 3600;

    public function analytics(Request $request)
    {
        $user = Auth::user();
        return response()->json($this->getUserAnalytics($user));
    }

    private function getUserAnalytics($user)
    {
        $cacheKey = "user_analytics_{$user->id}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
            return [
                'posts_count' => $this->getPostsCount($user),
                'posts_by_status' => $this->getPostsByStatus($user),
                'posts_per_platform' => $this->getPostsPerPlatform($user),
                'upcoming_posts' => $this->getUpcomingPosts($user),
                'publish_success_rate' => $this->getPublishSuccessRate($user),
            ];
        });
    }

    private function getPostsCount($user)
    {
        return $user->posts()->count();
    }

    private function getPostsByStatus($user)
    {
        $counts = $user->posts()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        return [
            'draft' => $counts[Post::STATUS_DRAFT] ?? 0,
            'scheduled' => $counts[Post::STATUS_SCHEDULED] ?? 0,
            'published' => $counts[Post::STATUS_PUBLISHED] ?? 0,
        ];
    }

    private function getPostsPerPlatform($user)
    {
        $postsPerPlatform = $user->platforms()
            ->with(['posts' => function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->select('posts.id', 'posts.title', 'posts.scheduled_time', 'posts.status');
            }])
            ->withCount(['posts' => function ($query) use ($user) {
                $query->where('user_id', $user->id);
            }])
            ->limit(5)
            ->get();

        return PostsPerPlatformResource::collection($postsPerPlatform);
    }

    private function getUpcomingPosts($user)
    {
        $upcomingPosts = $user->posts()
            ->scheduled()
            ->with('platforms:id,name')
            ->get();

        return UpcomingPostsResource::collection($upcomingPosts);
    }

    private function getPublishSuccessRate($user)
    {
        $statusCounts = $this->getPostsByStatus($user);

        $totalPosts = array_sum($statusCounts);
        $publishedPosts = $statusCounts['published'] ?? 0;

        return $totalPosts > 0 ? round(($publishedPosts / $totalPosts) * 100, 2) : 0;
    }

    public static function clearCache($userId)
    {
        Cache::forget("user_analytics_{$userId}");
    }
}
