<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations;

class Collection extends Model
{
    public $timestamps = false;
    use SoftDeletes, HasFactory;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'user_id'   => 'integer',
        // because of SQLite
        'icon_id'   => 'integer',
        'parent_id' => 'integer',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'user_id',
        'icon_id',
        'parent_id',
        'root_id'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'user_id',
        'deleted_at'
    ];

    public static function getCollectionId($id, $is_uncategorized = false)
    {
        return $is_uncategorized || $id === null ? null : intval($id);
    }

    public function parent(): Relations\BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): Relations\HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function nested(): Relations\HasMany
    {
        return $this->children()->with('nested');
    }
}
