<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use App\User;
use App\Collection;
use App\Post;

class ExportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export a collection';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $this->info('Export a collection:');
        $collection_id = $this->ask('Collection Id');
        $email         = $this->ask('Email address of its owner');

        $validator = Validator::make([
            'collection_id' => $collection_id,
            'email'         => $email
        ], [
            'collection_id' => 'integer',
            'email'         => 'email',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return;
        }

        $user = User::where('email', $email)->firstOrFail();

        $collection = Collection::where('id', $collection_id)->where('user_id', $user->id)->firstOrFail();

        $posts = Post::select('title', 'content')
            ->where('collection_id', $collection_id)
            ->whereNull('deleted_at')
            ->orderBy('order', 'desc')
            ->get();

        $filename = date('Y-m-d') . '_' . $collection->name . '.json';
        file_put_contents('database/data/' . $filename, json_encode($posts, JSON_PRETTY_PRINT));

        $this->line(PHP_EOL);
        $this->info('Export completed.');
    }
}
