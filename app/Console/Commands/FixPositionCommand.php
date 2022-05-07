<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\User;
use App\Post;
use App\Collection;
use stdClass;

class FixPositionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix-position';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix position of your posts';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $users = User::select('id', 'name')->get();
        foreach ($users as $user) {
            $user_id = $user->id;
            $collections = Collection::select('id', 'name')
                ->where('user_id', $user_id)
                ->get();
            $uncategorized = new stdClass;
            $uncategorized->id = null;
            $uncategorized->name = 'Uncategorized';
            $collections->push($uncategorized);
            foreach ($collections as $collection) {
                echo '------- ' . $collection->name . ' by ' . $user->name . ':' . ' -------' . PHP_EOL;
                $collection_id = $collection->id;
                $posts = Post::where('user_id', $user_id)
                    ->where('collection_id', $collection_id)
                    ->where('deleted_at', null)
                    ->orderBy('order')
                    ->get();
                for ($i = 0; $i < $posts->count(); $i++) {
                    $post = $posts[$i];
                    $post->order = $i + 1;
                    $post->save();
                    echo $post['order'] . ': ' . $post['title'] . PHP_EOL;
                };
            }
        }

        $this->info('completed.');

    }

}
