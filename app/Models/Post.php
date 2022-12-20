<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Post extends Model
{
    use SoftDeletes, HasFactory;

    const POST_TYPE_TEXT = 1;
    const POST_TYPE_LINK = 2;


    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'user_id' => 'integer', // because of SQLite
        'order' => 'integer', // because of SQLite
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'content', 'type', 'url', 'base_url', 'title', 'description',
        'color', 'image_path', 'user_id', 'collection_id', 'order'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'user_id', 'deleted_at'
    ];

    public function getTypeAttribute($value)
    {
        if (intval($value) === self::POST_TYPE_LINK) {
            return 'link';
        }
        return 'text';
    }

    public static function getTypeFromString($value)
    {
        if ($value === 'text') {
            return self::POST_TYPE_TEXT;
        } else if ($value === 'link') {
            return self::POST_TYPE_LINK;
        }
    }

    public function getImagePathAttribute($value)
    {

        if (Str::startsWith($value, 'thumbnail_')) {
            return Storage::url('thumbnails/' . $value);
        }

        return $value;
    }

    public function setImagePathAttribute($value)
    {
        $this->attributes['image_path'] = (strlen($value) > 512) ? null : $value;
    }

    /**
     * Tags that belong to the post.
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function seedIntroData($user_id, $collection_id = null)
    {
        $i = 1;

        Post::create([
            'title'         => 'GitHub - fr0tt/benotes: An open source self hosted web app for your notes and bookmarks.',
            'content'       => 'https://github.com/fr0tt/benotes',
            'type'          => self::POST_TYPE_LINK,
            'url'           => 'https://github.com/fr0tt/benotes',
            'color'         => '#1e2327',
            'image_path'    => 'https://opengraph.githubassets.com/9c1b74a8cc5eeee5c5c9f62701c42e1356595422d840d2e209bceb836deb5ffb/fr0tt/benotes',
            'base_url'      => 'https://github.com',
            'collection_id' => $collection_id,
            'user_id'       => $user_id,
            'order'         => $i++,
        ]);

        Post::create([
            'title'         => 'Also...',
            'content'       => '<p>you can save (or paste, if your browser allows it) bookmarks ! ➡️</p>',
            'type'          => self::POST_TYPE_TEXT,
            'description'   => null,
            'collection_id' => $collection_id,
            'user_id'       => $user_id,
            'order'         => $i++
        ]);

        Post::create([
            'title'         => 'Text Post 📝',
            'content'       => '<p>This post demonstrates different features of Benotes. <br>' .
                'You can write <strong>bold</strong>, ' .
                '<em>italic</em> or <strong>combine <em>them</em></strong><em>.</em></p>' .
                '<blockquote><p>Blockquotes are also a thing</p></blockquote>' .
                '<hr> <p>You can also use Markdown in order to type faster. <br>' .
                'If you are not familiar with the syntax have a look at</p>' .
                '<unfurling-link href="https://www.markdownguide.org/cheat-sheet/" ' .
                'data-title="Markdown Cheat Sheet | Markdown Guide"></unfurling-link>',
            'type'          => self::POST_TYPE_TEXT,
            'description'   => null,
            'collection_id' => $collection_id,
            'user_id'       => $user_id,
            'order'         => $i++
        ]);
    }
}
