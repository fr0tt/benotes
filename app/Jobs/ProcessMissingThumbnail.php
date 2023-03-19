<?php

namespace App\Jobs;

use App\Models\Post;
use App\Services\PostService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

class ProcessMissingThumbnail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $post, $service; // @TODO needs to e public or not ?


    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    // public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @param  App\Models\Post  $post
     * @return void
     */
    public function __construct(Post $post)
    {
        $this->post = $post->withoutRelations(); // @TODO not sure if necessary
        $this->service = new PostService();
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware()
    {
        return [(new WithoutOverlapping($this->post->id))->releaseAfter(60)];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->post->type === Post::POST_TYPE_TEXT) {
            return;
        }
        if (!empty($this->post->image_path)) {
            return;
        }
        if (@get_headers($this->post->url) == false) {
            return;
        }

        $filename = 'thumbnail_' . md5($this->post->url) . '_' . $this->post->id . '.jpg';
        $path = storage_path('app/public/thumbnails/' . $filename);
        $this->service->screenshot($path, $this->post->url, 400, 210);

        if (file_exists($path)) {
            $this->post->image_path = $filename;
            $this->post->save();
        }
    }
}
