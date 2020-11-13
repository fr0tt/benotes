<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Post extends Model
{
    use SoftDeletes;

    const POST_TYPE_TEXT = 1;
    const POST_TYPE_LINK = 2;


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
        if ($value === self::POST_TYPE_TEXT) {
            return 'text';
        } else if ($value === self::POST_TYPE_LINK) {
            return 'link';
        }
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
            return '/storage/thumbnails/'.$value;
        }
        return $value;
    }

}
