<?php

namespace App\Console\Commands;

use App\Jobs\PublishPostsJob;
use App\Models\ActivityLog;
use App\Models\Post;
use App\Services\PostPublishingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessScheduledPosts extends Command
{
    protected $postPublishingService;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process:due-posts {--limit=50}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process posts scheduled for publication';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');

        $posts  = Post::duePosts()->limit($limit)->get();

        if ($posts->isEmpty()) {
            $this->info('No posts to process');
            return;
        }

        $this->info('Processing ' . $posts->count() . ' posts');

        $this->info('Dispatching ' . $posts->count() . ' jobs');

        foreach ($posts as $post) {
            PublishPostsJob::dispatch($post);
        }

        $this->info('Done');
    }
}
