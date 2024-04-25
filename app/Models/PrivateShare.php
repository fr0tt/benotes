<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\PrivateShare
 *
 * @property int $id
 * @property int $collection_id
 * @property int $user_id
 * @method static \Illuminate\Database\Eloquent\Builder|PrivateShare newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PrivateShare newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PrivateShare query()
 * @method static \Illuminate\Database\Eloquent\Builder|PrivateShare whereCollectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrivateShare whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PrivateShare whereUserId($value)
 * @mixin \Eloquent
 */
class PrivateShare extends Model
{

    use HasFactory;

    public $timestamps = false;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'user_id'       => 'integer',
        // because of SQLite
        'created_by'    => 'integer',
        'collection_id' => 'integer',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'created_by',
        'collection_id'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function collection(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Collection::class, 'collection_id');
    }

}
