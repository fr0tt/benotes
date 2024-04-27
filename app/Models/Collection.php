<?php

namespace App\Models;

use App\Traits\Hierarchy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Collection
 *
 * @property int $id
 * @property string $name
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $icon_id
 * @property int|null $parent_id
 * @property int|null $left
 * @property int|null $right
 * @property int|null $root_collection_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Collection> $children
 * @property-read int|null $children_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Collection> $nested
 * @property-read int|null $nested_count
 * @property-read Collection|null $parent
 * @method static \Database\Factories\CollectionFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Collection newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Collection newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Collection onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Collection query()
 * @method static \Illuminate\Database\Eloquent\Builder|Collection whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Collection whereIconId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Collection whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Collection whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Collection whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Collection whereRootId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Collection whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Collection withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Collection withoutTrashed()
 * @mixin \Eloquent
 */
class Collection extends Model
{
    use SoftDeletes, HasFactory, Hierarchy;

    public $timestamps = false;
    const IMPORTED_COLLECTION_NAME = 'Imported Bookmarks';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'user_id'         => 'integer',
        'is_being_shared' => 'boolean',
        // because of SQLite
        'icon_id'         => 'integer',
        'parent_id'       => 'integer',
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
        'is_being_shared',
        'left'
        // root_collection_id and depth should not be needed
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'deleted_at',
        'root_collection_id',
        'depth',
        'local_order',
    ];

    public static function getCollectionId($id, $is_uncategorized = false)
    {
        return $is_uncategorized || $id === null ? null : intval($id);
    }

    public static function getOwner(int|null $id)
    {
        if ($id === null)
            return auth()->user()->id;
        return Collection::find($id)->user_id;
    }

}
