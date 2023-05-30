<?php

namespace App\Jobs;

use App\Models\Post;
use App\Services\PostService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

class ProcessMissingThumbnail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $post, $service;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @param  App\Models\Post  $post
     * @return void
     */
    public function __construct(Post $post)
    {
        $this->post = $post->withoutRelations();
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
        if (!empty($this->post->deleted_at)) {
            return;
        }
        if (@get_headers($this->post->url) == false) {
            return;
        }

        $filename = $this->service->generateThumbnailFilename($this->post->url, $this->post->id);
        $path = $this->service->getThumbnailPath($filename);
        $this->service->screenshot($filename, $path, $this->post->url);

        if (file_exists($path)) {
            $this->post->image_path = $filename;
            $this->post->save();
        }
    }
}
