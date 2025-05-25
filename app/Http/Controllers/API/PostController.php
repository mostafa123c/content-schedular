<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Http\Resources\Post\PostResource;
use App\Models\ActivityLog;
use App\Models\Post;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::with('platforms', 'user')->filter()->paginate(10);

        return response()->json(PostResource::pagination($posts));
    }

    public function getUsersPosts(Request $request)
    {
        $user = Auth::user();
        $posts = $user->posts()->with('platforms')->filter()->paginate(15);
        return response()->json(PostResource::pagination($posts));
    }


    public function store(StorePostRequest $request)
    {
        $data = $request->validated();

        $user = Auth::user();

        if ($user->getRemainingDailyPosts() == 0) {
            return response()->json([
                'message' => 'You have reached your daily posts limit',
                'remaining' => 0
            ], 403);
        }

        $data['image_url'] = $this->uploadImage($request);

        DB::beginTransaction();
        try {
            $post = Post::create([
                'user_id' => $user->id,
                'title' => $data['title'],
                'content' => $data['content'],
                'image_url' => $data['image_url'] ?? null,
                'scheduled_time' => $data['scheduled_time'],
                'status' => $data['status'],
            ]);

            $post->platforms()->sync($data['platforms']);
            $user->platforms()->syncWithoutDetaching($data['platforms']);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create post',
            ], 400);
        }


        return response()->json(PostResource::item($post->load('platforms', 'user'), ['remaining_daily_posts' => $user->getRemainingDailyPosts()]));
    }

    public function repost(Post $originalPost)
    {
        Gate::authorize('repost', $originalPost);

        $user = Auth::user();

        if ($user->getRemainingDailyPosts() == 0) {
            return response()->json([
                'message' => 'You have reached your daily posts limit',
                'remaining' => 0
            ], 403);
        }

        DB::beginTransaction();
        try {
            $newPost = Post::create([
                'user_id' => $user->id,
                'title' => $originalPost->title,
                'content' => $originalPost->content,
                'image_url' => $originalPost->image_url,
                'scheduled_time' => now()->addDays(1),
                'status' => Post::STATUS_DRAFT,
            ]);

            $newPost->platforms()->sync($originalPost->platforms->pluck('id'));
            $user->platforms()->syncWithoutDetaching($originalPost->platforms->pluck('id'));

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to repost post',
            ], 400);
        }

        return response()->json(PostResource::item($newPost->load('platforms', 'user'), ['remaining_daily_posts' => $user->getRemainingDailyPosts()]));
    }

    public function show(Post $post)
    {
        Gate::authorize('view', $post);

        return response()->json(PostResource::item($post->load('platforms', 'user')));
    }


    public function update(UpdatePostRequest $request, Post $post)
    {
        Gate::authorize('update', $post);

        $data = $request->validated();

        if ($post->image_url && isset($data['image_url'])) {
            Storage::disk('public')->delete($post->image_url);
        }

        $data['image_url'] = $this->uploadImage($request);

        $post->update($data);

        if (isset($data['platforms'])) {
            $post->platforms()->sync($data['platforms']);
        }
        return response()->json(PostResource::item($post->load('platforms', 'user')));
    }


    public function destroy(Post $post)
    {
        Gate::authorize('delete', $post);

        if ($post->image_url) {
            Storage::disk('public')->delete($post->image_url);
        }

        $post->platforms()->detach();

        return process($post->delete());
    }


    protected function uploadImage(Request $request)
    {
        if (!$request->hasFile('image_url')) {
            return;
        }

        $file = $request->file('image_url');
        $path = $file->store('uploads/posts', 'public');

        return $path;
    }
}
