<?php

namespace App\Traits;

use App\Models\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use RuntimeException;

trait Hierarchy
{
    protected static $scope = 'user_id';

    protected static $parent = 'parent_id'; // unused so far

    private static $parentChildMap = [];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    // booted() won't allow accessing the id in created()
    protected static function boot()
    {
        // observers didn't work because you can not
        // pass the $scope parameter
        // however eloquent events listeners should be possible...

        parent::boot();

        static::creating(function ($model) {
            $parent_id = $model->parent_id;
            $highestRight = $model
                ->where('parent_id', $parent_id)
                ->where(static::$scope, $model->{static::$scope})
                ->max('right');

            if ($highestRight === null) {
                $model->left = 1;
            } else if (empty($model->left) || $model->left > $highestRight + 1) {
                $model->left = $highestRight + 1;
            }

            if (empty($parent_id)) {
                $model->depth = 0;
            } else {
                $parent = $model->find($parent_id);
                $model->root_collection_id = $parent->depth === 0
                    ? $parent->id
                    : $parent->root_collection_id;
                $model->depth = $parent->depth + 1;
                $model->{self::$scope} = $parent->{self::$scope};
                if ($highestRight === null && $model->depth > 1)
                    $model->left = $parent->left + 1;
            }

            $model->right = $model->left + 1;

            $model
                ->where('root_collection_id', $model->root_collection_id)
                ->where(static::$scope, $model->{static::$scope})
                ->where('left', '>=', $model->left)
                ->increment('left', 2);
            $model
                ->where('root_collection_id', $model->root_collection_id)
                ->where(static::$scope, $model->{static::$scope})
                ->where('right', '>=', $model->left)
                ->increment('right', 2);

        });

        static::updating(function ($model) {

            $parent_id = $model->parent_id;
            $old_parent_id = $model->getOriginal('parent_id');
            $left = $model->left;
            $old_left = $model->getOriginal('left');
            $old_right = $model->getOriginal('right');
            $old_left_right_dif = $old_right - $old_left;
            $old_root_collection_id = $model->getOriginal('root_collection_id');
            $old_user_id = $model->getOriginal('user_id');

            if ($parent_id === $old_parent_id) {

                if ($old_user_id !== $model->user_id)
                    throw new RuntimeException(
                        "Root can not be moved as root to another user");

                if ($left === $old_left)
                    return;

                $model->checkLeftValue(false, $old_left);

                $moveToTheRight = $left > $old_left;
                if ($moveToTheRight) {
                    $model->where('root_collection_id', $model->root_collection_id)
                          ->where(static::$scope, $model->{static::$scope})
                          ->where('left', '>', $old_left)
                          ->where('left', '<=', $left)
                          ->decrementEach(['left' => 2, 'right' => 2]);
                } else {
                    $model->where('root_collection_id', $model->root_collection_id)
                          ->where(static::$scope, $model->{static::$scope})
                          ->where('left', '>=', $left)
                          ->where('left', '<', $old_left)
                          ->incrementEach(['left' => 2, 'right' => 2]);
                }

                $model->right = $model->left + $old_left_right_dif;

                return;
            }

            if (empty($parent_id)) {
                $model->root_collection_id = null;
                $model->depth = 0;
            } else {
                $parent = $model->find($parent_id);
                $model->root_collection_id = $parent->depth === 0
                    ? $parent->id
                    : $parent->root_collection_id;
                $model->depth = $parent->depth + 1;
                $model->{self::$scope} = $parent->{self::$scope};
            }

            $depth = $model->depth;
            $old_depth = $model->getOriginal('depth');
            $rootCollectionHasChanged =
                $model->root_collection_id !== $old_root_collection_id;

            $model->checkLeftValue($rootCollectionHasChanged, $old_left);

            $right = $model->left + $old_left_right_dif;
            if ($model->depth === 0) {
                $right = $model->left + 1;
            } else if ($model->depth > 0 && $old_depth === 0) {
                if ($model->where('parent_id', $model->id)->exists())
                    $new_left_right_dif = 2 +
                        $model->where('parent_id', $model->id)->max('right') -
                        $model->where('parent_id', $model->id)->min('left');
                else $new_left_right_dif = 1;
                $right = $model->left + $new_left_right_dif;
            }
            $model->right = $right;

            // update left and right of
            // old/new ancestors/siblings/descendants
            $left_right_dif = $right - $model->left;
            $left_dif = $left - $old_left;

            if ($old_depth === 0) {
                $selection_ids = $model
                    ->select('id')
                    ->where('root_collection_id', $model->id)
                    ->pluck('id');
            } else {
                $selection_ids = $model
                    ->select('id')
                    ->where('root_collection_id', $old_root_collection_id)
                    ->where('id', '!=', $model->id)
                    ->where('left', '>', $old_left)
                    ->where('right', '<', $old_right)
                    ->pluck('id');
            }

            if (!$rootCollectionHasChanged) {
                if ($left > $old_left) { // right
                    $model
                        ->where('root_collection_id', $old_root_collection_id)
                        // not sure if this helps
                        ->where('id', '!=', $model->id)
                        ->whereBetween('left', [$old_left, $right])
                        ->decrement('left', $old_left_right_dif + 1);
                    $model
                        ->where('root_collection_id', $old_root_collection_id)
                        ->where('id', '!=', $model->id)
                        ->whereBetween('right', [$old_left, $right])
                        ->decrement('right', $old_left_right_dif + 1);
                } else { // left
                    $model
                        ->where('root_collection_id', $old_root_collection_id)
                        ->where('id', '!=', $model->id)
                        ->whereBetween('left', [$left, $old_right])
                        ->increment('left', $old_left_right_dif + 1);
                    $model
                        ->where('root_collection_id', $old_root_collection_id)
                        ->where('id', '!=', $model->id)
                        ->whereBetween('right', [$left, $old_right])
                        ->increment('right', $old_left_right_dif + 1);
                }

                $model
                    ->whereIn('id', $selection_ids)
                    ->incrementEach([
                        'left' => $left_dif,
                        'right' => $left_dif,
                        'depth' => $depth - $old_depth
                    ]);

            } else {

                $model
                    ->where('root_collection_id', $model->root_collection_id)
                    ->where(static::$scope, $model->{static::$scope})
                    ->where('left', '>=', $left)
                    ->increment('left', $left_right_dif + 1);
                $model
                    ->where('root_collection_id', $model->root_collection_id)
                    ->where(static::$scope, $model->{static::$scope})
                    ->where('right', '>=', $left)
                    ->increment('right', $left_right_dif + 1);

                // update descendants
                $descendants_root_collection_id = $model->depth === 0
                    ? $model->id
                    : $model->root_collection_id;

                if ($model->depth === 0 && $old_depth === 0) {

                    $model
                        ->whereIn('id', $selection_ids)
                        ->update([
                            'root_collection_id' => $descendants_root_collection_id,
                            self::$scope => $model->{self::$scope}
                        ]);

                } else {

                    if ($model->depth === 0)
                        $left_dif = $old_left * -1;
                    else if ($old_depth === 0)
                        $left_dif = $model->left;

                    $model
                        ->whereIn('id', $selection_ids)
                        ->incrementEach([
                            'left' => $left_dif,
                            'right' => $left_dif,
                            'depth' => $depth - $old_depth
                        ], [
                            'root_collection_id' => $descendants_root_collection_id,
                            self::$scope => $model->{self::$scope}
                        ]);
                }

                $model
                    ->where('root_collection_id', $old_root_collection_id)
                    ->where(static::$scope, $old_user_id)
                    ->where('left', '>=', $old_left)
                    // exclude $model because new root_collection is not saved yet
                    ->where('id', '!=', $model->id)
                    ->decrement('left', $old_left_right_dif + 1);
                $model
                    ->where('root_collection_id', $old_root_collection_id)
                    ->where(static::$scope, $old_user_id)
                    ->where('right', '>=', $old_left)
                    ->where('id', '!=', $model->id)
                    ->decrement('right', $old_left_right_dif + 1);
            }

        });

        static::deleted(function ($model) {

            $model->descendants()->delete();

            // fix rest of the tree
            $dif = $model->right - $model->left;
            $model
                ->where('root_collection_id', $model->root_collection_id)
                ->where(static::$scope, $model->{static::$scope})
                ->where('left', '>', $model->left)
                ->decrement('left', $dif + 1);
            $model
                ->where('root_collection_id', $model->root_collection_id)
                ->where(static::$scope, $model->{static::$scope})
                ->where('right', '>', $model->left)
                ->decrement('right', $dif + 1);
        });

        static::restoring(function ($model) {

            if (empty($model->parent()) && $model->depth > 0) {
                throw new RuntimeException("Parent was deleted");
            }

            $old_left = $model->left;
            $old_right = $model->right;
            $old_depth = $model->depth;
            $old_root_collection_id = $model->root_collection_id;
            $left_right_dif = $model->right - $model->left;

            $model->depth = 0;
            $model->root_collection_id = null;
            if ($model->parent()->count() > 0) {
                $parent = $model->parent()->first();
                $model->depth = $parent->depth + 1;
                $model->root_collection_id = empty($parent->root_collection_id)
                    ? $parent->id
                    : $parent->root_collection_id;
                $model->{static::$scope} = $parent->{static::$scope};
            }

            $next_left = $model
                ->where('parent_id', $model->parent_id)
                ->where(static::$scope, $model->{static::$scope})
                ->where('left', '>=', $model->left)
                ->orderBy('left')
                ->first();
            if (!empty($next_left)) {
                $left = $next_left->left;
            } else if ($model->depth <= 1) {
                $left = $model
                    ->where('parent_id', $model->parent_id)
                    ->where(static::$scope, $model->{static::$scope})
                    ->max('right') + 1;
            } else {
                $left = $model
                    ->select('right')
                    ->where('id', $model->parent_id)->first()->right;
            }

            $model->left = $left;
            $model->right = $left + $left_right_dif;

            $model
                ->where('root_collection_id', $model->root_collection_id)
                ->where(static::$scope, $model->{static::$scope})
                ->where('left', '>=', $model->left)
                ->increment('left', $left_right_dif + 1);
            $model
                ->where('root_collection_id', $model->root_collection_id)
                ->where(static::$scope, $model->{static::$scope})
                ->where('right', '>=', $model->left)
                ->increment('right', $left_right_dif + 1);

            // restore descendants
            if ($model->depth === 0) {
                $descendant_ids = $model
                    ->onlyTrashed()
                    ->where('root_collection_id', $model->id)
                    ->pluck('id');
                $model
                    ->onlyTrashed()
                    ->where('root_collection_id', $old_root_collection_id ?? $model->id)
                    ->where('deleted_at', '>=', $model->deleted_at)
                    ->restore();
            } else {
                $descendant_ids = $model
                    ->onlyTrashed()
                    ->where('root_collection_id', $old_root_collection_id)
                    ->where('left', '>', $old_left)
                    ->where('right', '<', $old_right)
                    ->pluck('id');
                $model
                    ->onlyTrashed()
                    ->where('root_collection_id', $old_root_collection_id)
                    ->where('left', '>', $old_left)
                    ->where('right', '<', $old_right)
                    ->where('id', '!=', $model->id)
                    ->where('deleted_at', '>=', $model->deleted_at)
                    ->restore();
            }

            Collection::whereIn('id', $descendant_ids)->incrementEach([
                'left' => $left - $old_left,
                'right' => $left - $old_left,
                'depth' => $model->depth - $old_depth
            ], [
                'root_collection_id' => $model->root_collection_id,
                static::$scope => $model->{static::$scope}
            ]);

            $model->saveQuietly();

        });

    }

    public function checkLeftValue($rootCollectionHasChanged, $oldLeft): void
    {
        if ($this->depth > 1 && $this->left === 1) {
            throw new RuntimeException("Left value is not possible");
        }

        if ($this->left === 1) {
            return;
        }

        $left_boundary = 1;
        $right_boundary = 2;
        if ($this->depth <= 1) {
            $left_boundary = $this
                ->where('parent_id', $this->parent_id)
                ->where(static::$scope, $this->{static::$scope})
                ->min('left');
            $right_boundary = $this
                ->where('parent_id', $this->parent_id)
                ->where(static::$scope, $this->{static::$scope})
                ->max('right') + 1;
            if (empty($left_boundary)) {
                $left_boundary = 1;
                $right_boundary = 1;
            }
        } else {
            $parent = $this->parent()->first();
            $left_boundary = $parent->left + 1;
            $right_boundary = $parent->right;
            if (!$rootCollectionHasChanged && $this->left > $oldLeft) {
                $left_right_dif = $this->right - $oldLeft;
                $left_boundary = $left_boundary - ($left_right_dif + 1);
                $right_boundary = $right_boundary - ($left_right_dif + 1);
            }
        }

        if ($this->left < $left_boundary || $this->left > $right_boundary) {
            throw new RuntimeException("Left value is not possible");
        }

        $sibling = $this
            ->where('parent_id', $this->parent_id)
            ->where(static::$scope, $this->{static::$scope})
            ->where('id', '!=', $this->id)
            ->first();

        $sibling_is_even = empty($sibling)
            ? $this->find($this->parent_id)->left % 2 !== 0
            : $sibling->left % 2 === 0;
        $is_even = $this->left % 2 === 0;
        if ($is_even !== $sibling_is_even) {
            throw new RuntimeException("Left value is not possible");
        }
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function nested()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function siblings()
    {
        return $this
            ->where(static::$scope, $this->{static::$scope})
            ->where('parent_id', $this->parent_id)
            ->where('id', '!=', $this->id);
    }

    public function descendants()
    {
        if ($this->depth === 0) {
            return $this->where('root_collection_id', $this->id);
        }

        return $this
            ->where('root_collection_id', $this->root_collection_id)
            ->where('left', '>', $this->left)
            ->where('right', '<', $this->right);
    }

    public function descendantsAndSelf()
    {
        return $this->descendants()->orWhere('id', $this->id);
    }

    public function ancestors()
    {
        if (empty($this->parent_id))
            return collect();
        $ids = [];
        $traverse = function ($parent) use (&$traverse, &$ids) {
            if (!empty($parent->parent_id))
                $ids[] = ($traverse($parent->parent()))->pluck('id');
            return $parent;
        };
        $traverse($this);
        return $this
            ->whereIn('id', $ids)
            ->orWhere('id', $this->root_collection_id);
    }

    public function ancestorsAndSelf()
    {
        $ancestors = $this->ancestors();
        if ($ancestors->count() === 0)
            return $this->where('id', $this->id);
        return $ancestors->orWhere('id', $this->id);
    }

    public function isChildOf(Model $parent): bool
    {
        return $this->parent_id === $parent->id;
    }

    public function isDescendantOf(Model $parent): bool
    {
        if (in_array(self::class, class_uses($parent)))
            throw new ModelNotFoundException();

        if ($parent->depth === 0 && $parent->id === $this->root_collection_id)
            return true;

        if ($parent->depth === 0 && $parent->id !== $this->root_collection_id)
            return false;

        if ($parent->root_collection_id !== $this->root_collection_id)
            return false;

        return $parent->left < $this->left
            && $parent->right > $this->right;
    }

    public function isSelfOrDescendantOf(Model $parent): bool
    {
        if ($this->id === $parent->id) {
            return true;
        }
        return $this->isDescendantOf($parent);
    }

    public function appendTo($parent_id, $user_id = -1): void
    {
        $this->moveTo($parent_id, -1, $user_id);
    }

    public function moveTo($parent_id, $local_order = -1, $user_id = -1): void
    {
        if ($local_order <= 0 && $this->parent_id === $parent_id) {
            if ($user_id <= 0 || $this->{static::$scope} === $user_id)
                return;
        }
        if ($user_id > 0)
            $this->{static::$scope} = $user_id;
        $this->left = $this->computeNewLeft($parent_id, $local_order, $user_id);
        $this->parent_id = $parent_id;
        $this->save();
    }

    public function computeNewLeft($parent_id, $local_order = -1, $user_id = -1): int
    {
        $scope = $user_id <= 0
            ? $this->getScopeValue($parent_id)
            : $user_id;
        $positions = $this->select(['left', 'right', 'root_collection_id'])
            ->where(static::$scope, $scope)
            ->where('parent_id', $parent_id)
            ->orderBy('left')
            ->get();

        if ($local_order > $positions->count() + 1) {
            throw new RuntimeException("Value is not possible");
        }

        if ($positions->count() === 0) {
            if (empty($parent_id))
                return 1;
            $parent = $this->find($parent_id);
            if ($parent->depth === 0)
                return 1;
            if ($this->left < $parent->left &&
                $this->root_collection_id === $parent->root_collection_id)
                return $parent->left - ($this->right - $this->left);
            return $parent->left + 1;
        }

        $rootCollectionHasChanged =
            $positions->first()->root_collection_id !== $this->root_collection_id;

        if ($local_order <= 0 || ($positions->count() + 1) === $local_order) {
            return $rootCollectionHasChanged
                ? $positions->last()->right + 1
                : $positions->last()->right - ($this->right - $this->left);
        }

        $left = $positions->skip($local_order - 1)->first()->left;
        if ($rootCollectionHasChanged || $this->parent_id === $parent_id)
            return $left;
        if ($this->left < $left) {
            $left_right_dif = $this->right - $this->left;
            return $left - ($left_right_dif + 1);
        }
        return $left;
    }

    public function getLocalOrder(): int
    {
        return $this
            ->where(static::$scope, $this->{static::$scope})
            ->where('parent_id', $this->parent_id)
            ->where('left', '<=', $this->left)
            ->count();
    }

    public function makeRoot($left = -1)
    {
        $this->root_collection_id = null;
        $this->depth = 0;
        $this->left = $left < 0
            ? $this
                ->where('parent_id', null)
                ->where(static::$scope, $this->{static::$scope})
                ->max('right') + 1
            : $left;
        $this->right = $this->left + 1;
        $this->saveQuietly();
    }

    private function getScopeValue($id)
    {
        if (empty($id))
            return $this->{static::$scope};
        return $this->find($id)->{static::$scope};
    }

    public static function rebuildHierarchy(): void
    {
        $scopeValues =
            self::select(self::$scope)->distinct()->pluck(self::$scope);
        foreach ($scopeValues as $scopeValue) {
            $tree = self::toNestedWithParentId(
                self::where(self::$scope, $scopeValue)
                    ->orderBy('root_collection_id')
                    ->orderBy('depth')
                    ->orderBy('left')
                    ->orderBy('name')
                    ->get()
            );

            $left = 1;
            foreach ($tree as $root) {
                $col = Collection::find($root['id']);
                $col->makeRoot($left);
                $left = $left + 2;
                self::rebuildChildNodes($root['nested'], $root['id'], 1, 1);
            }
        }
    }

    private static function rebuildChildNodes($nodes, $rootCollectionId, $depth, $left)
    {
        if (count($nodes) === 0)
            return $left;
        foreach ($nodes as $node) {
            $col = Collection::find($node['id']);
            $col->root_collection_id = $rootCollectionId;
            $col->depth = $depth;
            $col->left = $left;
            $right = self::rebuildChildNodes($node['nested'], $rootCollectionId, $depth + 1, $left + 1);
            $col->right = $right;
            $col->saveQuietly();
            $left = $right + 1;
        }
        return $left;
    }

    public static function toNestedWithParentId(EloquentCollection|\Illuminate\Support\Collection $collection): array
    {
        $roots = [];
        $keys = [];
        foreach ($collection->pluck('id') as $id) {
            $keys[$id] = true;
        }
        foreach ($collection as $node) {
            // if ($node->parent_id === null)
            if (!array_key_exists($node->parent_id, $keys))
                $roots[] = $node->toArray();
            else {
                static::$parentChildMap[$node->parent_id][] = $node->toArray();
            }
        }

        return array_map(function ($root) {
            $root['nested'] = static::assignChildren($root);
            return $root;
        }, $roots);

    }

    private static function assignChildren($node)
    {
        if (!array_key_exists($node['id'], static::$parentChildMap)) {
            return [];
        }
        foreach (static::$parentChildMap[$node['id']] as $child) {
            $child['nested'] = static::assignChildren($child);
            $node['nested'][] = $child;
        }
        return $node['nested'];
    }

    public static function allNestedByScope($scopeValue): array
    {
        return static::toNested(
            self::where(self::$scope, $scopeValue)
                ->orderBy('root_collection_id')
                ->orderBy('depth')
                ->orderBy('left')
                ->get()
        );
    }

    // order is not always correct !
    public static function toNestedRecursive($collections): array
    {
        $children_ids = [];
        $traverse = function ($nodes) use (&$traverse, &$children_ids) {
            foreach ($nodes as $node) {
                $children = $node->nested;
                $children_ids = array_merge(
                    $children_ids,
                    $children->pluck('id')->toArray()
                );
                $traverse($children)->toArray();
            }
            return $nodes;
        };

        $tree = $traverse($collections)
            ->filter(function ($collection) use ($children_ids) {
                return !in_array($collection->id, $children_ids);
            })
            ->toArray();
        return array_values($tree);
    }

    public static function toNested(EloquentCollection|\Illuminate\Support\Collection $collections): array
    {
        // both work
        return self::toNestedWithParentId($collections);
        // return self::toNestedRecursive($collections);
    }

}
