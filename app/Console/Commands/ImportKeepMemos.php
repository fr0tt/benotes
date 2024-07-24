<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Collection;
use App\Models\Post;
use App\Models\Tag;

class ImportKeepMemos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:importKeepMemos {collection} {path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'import google keep data as json';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $collectionName = $this->argument('collection');
        $collection = Collection::where('name', '=', $collectionName)->first();
        if(is_null($collection)) {
            echo "Can't find specified collection...\n";
            exit;
        }

        $path = $this->argument('path');
        if(!is_dir($path)) {
            echo "The specified path is not directory path...\n";
        }

        $files = glob($path . '/*.json');
        $i = 1;
        foreach ($files as $file) {
            $json = file_get_contents($file);
            $data = json_decode($json, true);
            if($data['isTrashed'] || $data['isArchived']) {
                echo $file."\n";
                continue;
            }

            echo $data['title']."\n";

            $tags = [];
            if(array_key_exists('labels', $data)) {
                $labels = $data['labels'];
                foreach ($labels as $label) {
                    $tag = Tag::where('name', '=', $this->convertKana($label['name']),)->first();
                    if(is_null($tag)) {
                        $tag = Tag::create([
                            'name' => $this->convertKana($label['name']),
                            'user_id' => 1,
                        ]);
                    }
                    $tags[] = $tag;
                }
            }

            $date = new \DateTime();
            $date->setTimezone(new \DateTimeZone('Asia/Tokyo'));
            $date->setTimestamp($data['createdTimestampUsec']/1000000);
            $created = $date->format('Y-m-d H:i:s');
            $date->setTimestamp($data['userEditedTimestampUsec']/1000000);
            $edited = $date->format('Y-m-d H:i:s');
            $post = Post::create([
                'title' => $this->convertKana($data['title']),
                'content' => $data['textContentHtml'],
                'type' => Post::POST_TYPE_TEXT,
                'description'   => null,
                'collection_id' => $collection->id,
                'user_id'       => 1,
                'order'         => $i++,
            ]);
            $post->created_at = $created;
            $post->updated_at = $edited;
            $post->save();

            foreach($tags as $tag) {
                $post->tags()->attach($tag->id);
            }
        }

        return Command::SUCCESS;
    }

    protected function convertKana($value)
    {
        //  タイトルなどは正規化すること　数字・アルファベット・スペース→半角　カタカナ・記号→全角
        return trim(mb_convert_kana($value,'asKV'));
    }
}
