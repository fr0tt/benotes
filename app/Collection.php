<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Collection extends Model
{
    public $timestamps = false;
    use SoftDeletes;

    const UNCATEGORIZED = 0;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'user_id'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'user_id', 'deleted_at'
    ];

    public static function getCollectionId($value)
    {
        return ($value == 0) ? null : $value;
    }

}
