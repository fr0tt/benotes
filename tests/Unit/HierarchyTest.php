<?php

namespace Tests\Unit;

use App\Models\Collection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HierarchyTest extends TestCase
{

    use RefreshDatabase;

    public function testCreateCollection()
    {
        User::factory()->create();
        $collection = Collection::create([
            'name' => 'Collection',
            'user_id' => 1
        ]);
        $this->assertTrue($collection->exists());
        $this->assertEmpty( $collection->parent_id);
        $this->assertEmpty($collection->root_collection_id);
        $this->assertEquals(1, $collection->user_id);
        $this->assertEquals(0, $collection->depth);
        $this->assertEquals(1, $collection->left);
        $this->assertEquals(2, $collection->right);
    }

    public function testCreateNestedCollection()
    {
        User::factory()->create();
        $parentCollection = Collection::factory()->create();
        $collection = Collection::create([
            'name' => 'Collection',
            'user_id' => 1,
            'parent_id' => $parentCollection->id
        ]);
        $this->assertTrue($collection->exists());
        $this->assertEquals($parentCollection->id, $collection->parent_id);
        $this->assertEquals($parentCollection->id, $collection->root_collection_id);
        $this->assertEquals(1, $collection->depth);
        $this->assertEquals(1, $collection->left);
        $this->assertEquals(2, $collection->right);

        $this->assertEquals(1, $parentCollection->left);
        $this->assertEquals(2, $parentCollection->right);
        $this->assertEquals(0, $parentCollection->depth);
    }

    public function testCreateMultipleNestedCollections()
    {
        User::factory()->create();
        $parentCollection = Collection::factory()->create();
        $collection1 = Collection::create([
            'name' => 'CollectionA',
            'user_id' => 1,
            'parent_id' => $parentCollection->id
        ]);
        $collection2 = Collection::create([
            'name' => 'CollectionB',
            'user_id' => 1,
            'parent_id' => $parentCollection->id
        ]);
        $collection3 = Collection::create([
            'name' => 'CollectionC',
            'user_id' => 1,
            'parent_id' => $parentCollection->id
        ]);
        $this->assertTrue($collection3->exists());
        $this->assertEquals($parentCollection->id, $collection3->parent_id);
        $this->assertEquals($parentCollection->id, $collection3->root_collection_id);
        $this->assertEquals(1, $collection3->depth);
        $this->assertEquals(1, $collection1->left);
        $this->assertEquals(3, $collection2->left);
        $this->assertEquals(5, $collection3->left);
        $this->assertEquals(6, $collection3->right);
        $this->assertEquals(1, $parentCollection->left);
        $this->assertEquals(2, $parentCollection->right);
    }

    public function testCreateDeepNestedCollections()
    {
        User::factory()->create();
        $parentCollection = Collection::factory()->create();
        $childCollection = Collection::create([
            'name' => 'CollectionA',
            'user_id' => 1,
            'parent_id' => $parentCollection->id
        ]);
        $collection1 = Collection::create([
            'name' => 'CollectionB',
            'user_id' => 1,
            'parent_id' => $childCollection->id
        ]);
        $collection2 = Collection::create([
            'name' => 'CollectionC',
            'user_id' => 1,
            'parent_id' => $childCollection->id
        ]);

        $parentCollection = Collection::find($parentCollection->id);
        $childCollection = Collection::find($childCollection->id);
        $collection1 = Collection::find($collection1->id);
        $collection2 = Collection::find($collection2->id);

        $this->assertEquals($parentCollection->id, $childCollection->parent_id);
        $this->assertEquals($parentCollection->id, $childCollection->root_collection_id);
        $this->assertEquals($parentCollection->id, $collection1->root_collection_id);
        $this->assertEquals(1, $childCollection->depth);
        $this->assertEquals(2, $collection2->depth);
        $this->assertEquals(1, $parentCollection->left);
        $this->assertEquals(2, $parentCollection->right);

        $this->assertEquals(1, $childCollection->left);
        $this->assertEquals(2, $collection1->left);
        $this->assertEquals(3, $collection1->right);
        $this->assertEquals(4, $collection2->left);
        $this->assertEquals(5, $collection2->right);
        $this->assertEquals(6, $childCollection->right);
    }

    public function testMoveCollectionLeft()
    {
        User::factory()->create();
        $collection1 = Collection::factory()->create();
        $collection2 = Collection::factory()->create();
        $collection3 = Collection::factory()->create();
        $collection4 = Collection::factory()->create();

        $collection4->left = 3;
        $collection4->save();

        $collection1 = Collection::find($collection1->id);
        $collection2 = Collection::find($collection2->id);
        $collection3 = Collection::find($collection3->id);
        $collection4 = Collection::find($collection4->id);

        $this->assertEquals(1, $collection1->left);
        $this->assertEquals(2, $collection1->right);
        $this->assertEquals(3, $collection4->left);
        $this->assertEquals(4, $collection4->right);
        $this->assertEquals(5, $collection2->left);
        $this->assertEquals(6, $collection2->right);
        $this->assertEquals(7, $collection3->left);
        $this->assertEquals(8, $collection3->right);

    }

    public function testMoveCollectionLeftWithWrongValue()
    {
        User::factory()->create();
        $collection1 = Collection::factory()->create();
        $collection2 = Collection::factory()->create();
        $collection3 = Collection::factory()->create();
        $collection4 = Collection::factory()->create();
        $collection4->left = 2;
        $this->expectException("RuntimeException");
        $collection4->save();

    }

    public function testMoveCollectionLeftWithMethod()
    {
        User::factory()->create();
        $collection1 = Collection::factory()->create();
        $collection2 = Collection::factory()->create();
        $collection3 = Collection::factory()->create();
        $collection4 = Collection::factory()->create();

        $collection4->moveTo(null, 2);

        $collection1 = Collection::find($collection1->id);
        $collection2 = Collection::find($collection2->id);
        $collection3 = Collection::find($collection3->id);
        $collection4 = Collection::find($collection4->id);

        $this->assertEquals(1, $collection1->left);
        $this->assertEquals(2, $collection1->right);
        $this->assertEquals(3, $collection4->left);
        $this->assertEquals(4, $collection4->right);
        $this->assertEquals(5, $collection2->left);
        $this->assertEquals(6, $collection2->right);
        $this->assertEquals(7, $collection3->left);
        $this->assertEquals(8, $collection3->right);

    }

    public function testMoveCollectionLeftWithMassAssignment()
    {
        User::factory()->create();
        $collection1 = Collection::factory()->create();
        $collection2 = Collection::factory()->create();
        $collection3 = Collection::factory()->create();
        $collection4 = Collection::factory()->create();

        $collection4->update(['left' => 3]);

        $this->assertEquals(1, Collection::find($collection1->id)->left);
        $this->assertEquals(2, Collection::find($collection1->id)->right);
        $this->assertEquals(3, Collection::find($collection4->id)->left);
        $this->assertEquals(4, Collection::find($collection4->id)->right);
        $this->assertEquals(5, Collection::find($collection2->id)->left);
        $this->assertEquals(6, Collection::find($collection2->id)->right);
        $this->assertEquals(7, Collection::find($collection3->id)->left);
        $this->assertEquals(8, Collection::find($collection3->id)->right);
    }

    public function testMoveCollectionRight()
    {
        User::factory()->create();
        $collection1 = Collection::factory()->create();
        $collection2 = Collection::factory()->create();
        $collection3 = Collection::factory()->create();
        $collection4 = Collection::factory()->create();

        $collection1->left = 5;
        $collection1->save();

        $collection1 = Collection::find($collection1->id);
        $collection2 = Collection::find($collection2->id);
        $collection3 = Collection::find($collection3->id);
        $collection4 = Collection::find($collection4->id);

        $this->assertEquals(1, $collection2->left);
        $this->assertEquals(2, $collection2->right);
        $this->assertEquals(3, $collection3->left);
        $this->assertEquals(4, $collection3->right);
        $this->assertEquals(5, $collection1->left);
        $this->assertEquals(6, $collection1->right);
        $this->assertEquals(7, $collection4->left);
        $this->assertEquals(8, $collection4->right);
    }

    public function testMoveCollectionRightWithMassAssignment()
    {
        User::factory()->create();
        $collection1 = Collection::factory()->create();
        $collection2 = Collection::factory()->create();
        $collection3 = Collection::factory()->create();
        $collection4 = Collection::factory()->create();

        $collection1->update(['left' => 5]);

        $collection1 = Collection::find($collection1->id);
        $collection2 = Collection::find($collection2->id);
        $collection3 = Collection::find($collection3->id);
        $collection4 = Collection::find($collection4->id);

        $this->assertEquals(1, $collection2->left);
        $this->assertEquals(2, $collection2->right);
        $this->assertEquals(3, $collection3->left);
        $this->assertEquals(4, $collection3->right);
        $this->assertEquals(5, $collection1->left);
        $this->assertEquals(6, $collection1->right);
        $this->assertEquals(7, $collection4->left);
        $this->assertEquals(8, $collection4->right);
    }

    public function testMoveCollectionRightWithMethod()
    {
        User::factory()->create();
        $collection1 = Collection::factory()->create();
        $collection2 = Collection::factory()->create();
        $collection3 = Collection::factory()->create();
        $collection4 = Collection::factory()->create();

        $collection2->moveTo(null, 4);

        $collection1 = Collection::find($collection1->id);
        $collection2 = Collection::find($collection2->id);
        $collection3 = Collection::find($collection3->id);
        $collection4 = Collection::find($collection4->id);

        $this->assertEquals(1, $collection1->left);
        $this->assertEquals(2, $collection1->right);
        $this->assertEquals(3, $collection3->left);
        $this->assertEquals(4, $collection3->right);
        $this->assertEquals(5, $collection4->left);
        $this->assertEquals(6, $collection4->right);
        $this->assertEquals(7, $collection2->left);
        $this->assertEquals(8, $collection2->right);

    }

    public function testMoveChildCollectionLeft()
    {
        User::factory()->create();
        $parentCollection = Collection::factory()->create();
        $collection1 = Collection::factory([
            'parent_id' => $parentCollection->id
        ])->create();
        $collection2 = Collection::factory([
            'parent_id' => $parentCollection->id
        ])->create();
        $collection3 = Collection::factory([
            'parent_id' => $parentCollection->id
        ])->create();

        $collection3->update(['left' => 1]);

        $parentCollection = Collection::find($parentCollection->id);
        $collection1 = Collection::find($collection1->id);
        $collection2 = Collection::find($collection2->id);
        $collection3 = Collection::find($collection3->id);

        $this->assertEquals(1, $parentCollection->left);
        $this->assertEquals(2, $parentCollection->right);
        $this->assertEquals(1, $collection3->left);
        $this->assertEquals(2, $collection3->right);
        $this->assertEquals(3, $collection1->left);
        $this->assertEquals(4, $collection1->right);
        $this->assertEquals(5, $collection2->left);
        $this->assertEquals(6, $collection2->right);
    }

    public function testMoveChildCollectionRight()
    {
        User::factory()->create();
        $parentCollection = Collection::factory()->create();
        $collection1 = Collection::factory([
            'parent_id' => $parentCollection->id
        ])->create();
        $collection2 = Collection::factory([
            'parent_id' => $parentCollection->id
        ])->create();
        $collection3 = Collection::factory([
            'parent_id' => $parentCollection->id
        ])->create();

        $collection1->update(['left' => 5]);

        $parentCollection = Collection::find($parentCollection->id);
        $collection1 = Collection::find($collection1->id);
        $collection2 = Collection::find($collection2->id);
        $collection3 = Collection::find($collection3->id);

        $this->assertEquals(1, $parentCollection->left);
        $this->assertEquals(2, $parentCollection->right);
        $this->assertEquals(1, $collection2->left);
        $this->assertEquals(2, $collection2->right);
        $this->assertEquals(3, $collection3->left);
        $this->assertEquals(4, $collection3->right);
        $this->assertEquals(5, $collection1->left);
        $this->assertEquals(6, $collection1->right);

    }

    public function testMoveNestedChildCollectionLeft()
    {
        User::factory()->create();
        $parentCollection = Collection::factory()->create();
        $childCollection = Collection::factory([
            'parent_id' => $parentCollection->id
        ])->create();
        $grandChildCollection1 = Collection::factory([
            'parent_id' => $childCollection->id
        ])->create();
        $grandChildCollection2 = Collection::factory([
            'parent_id' => $childCollection->id
        ])->create();

        $grandChildCollection2->update(['left' => 2]);

        $parentCollection = Collection::find($parentCollection->id);
        $childCollection = Collection::find($childCollection->id);
        $grandChildCollection1 = Collection::find($grandChildCollection1->id);
        $grandChildCollection2 = Collection::find($grandChildCollection2->id);

        $this->assertEquals(1, $parentCollection->left);
        $this->assertEquals(2, $parentCollection->right);
        $this->assertEquals(1, $childCollection->left);
        $this->assertEquals(2, $grandChildCollection2->left);
        $this->assertEquals(3, $grandChildCollection2->right);
        $this->assertEquals(4, $grandChildCollection1->left);
        $this->assertEquals(5, $grandChildCollection1->right);
        $this->assertEquals(6, $childCollection->right);
    }

    public function testMoveRootLeft()
    {
        User::factory()->create();
        $rootCollection1 = Collection::factory()->create();
        $rootCollection2 = Collection::factory()->create();
        $rootCollection3 = Collection::factory()->create();
        $childCollection1 = Collection::factory([
            'parent_id' => $rootCollection3->id
        ])->create();
        $childCollection2 = Collection::factory([
            'parent_id' => $rootCollection3->id
        ])->create();
        $grandChildCollection = Collection::factory([
            'parent_id' => $childCollection2->id
        ])->create();

        $rootCollection3->moveTo(null, 1);

        $rootCollection1 = Collection::find($rootCollection1->id);
        $rootCollection2 = Collection::find($rootCollection2->id);
        $rootCollection3 = Collection::find($rootCollection3->id);

        $this->assertEquals(0, $rootCollection1->depth);
        $this->assertEquals(0, $rootCollection2->depth);
        $this->assertEquals(0, $rootCollection3->depth);
        $this->assertEquals($rootCollection3->id, $childCollection1->root_collection_id);
        $this->assertEquals($rootCollection3->id, $childCollection2->root_collection_id);
        $this->assertEquals(1, $rootCollection3->left);
        $this->assertEquals(2, $rootCollection3->right);
        $this->assertEquals(3, $rootCollection1->left);
        $this->assertEquals(4, $rootCollection1->right);
        $this->assertEquals(5, $rootCollection2->left);
        $this->assertEquals(6, $rootCollection2->right);
    }

    public function testMoveRootRight()
    {
        User::factory()->create();
        $rootCollection1 = Collection::factory()->create();
        $childCollection1 = Collection::factory([
            'parent_id' => $rootCollection1->id
        ])->create();
        $childCollection2 = Collection::factory([
            'parent_id' => $rootCollection1->id
        ])->create();
        $grandChildCollection = Collection::factory([
            'parent_id' => $childCollection2->id
        ])->create();
        $rootCollection2 = Collection::factory()->create();
        $rootCollection3 = Collection::factory()->create();

        $rootCollection1->moveTo(null, 3);

        $rootCollection1 = Collection::find($rootCollection1->id);
        $rootCollection2 = Collection::find($rootCollection2->id);
        $rootCollection3 = Collection::find($rootCollection3->id);

        $this->assertEquals(0, $rootCollection1->depth);
        $this->assertEquals(0, $rootCollection2->depth);
        $this->assertEquals(0, $rootCollection3->depth);
        $this->assertEquals($rootCollection1->id, $childCollection1->root_collection_id);
        $this->assertEquals($rootCollection1->id, $childCollection2->root_collection_id);
        $this->assertEquals(1, $rootCollection2->left);
        $this->assertEquals(2, $rootCollection2->right);
        $this->assertEquals(3, $rootCollection3->left);
        $this->assertEquals(4, $rootCollection3->right);
        $this->assertEquals(5, $rootCollection1->left);
        $this->assertEquals(6, $rootCollection1->right);
    }

    public function testMoveCollectionUp()
    {
        User::factory()->create();
        $parentCollection = Collection::factory()->create();
        $childCollection1 = Collection::factory([
            'parent_id' => $parentCollection->id
        ])->create();
        $childCollection2 = Collection::factory([
            'parent_id' => $parentCollection->id
        ])->create();
        $grandChildCollection = Collection::factory([
            'parent_id' => $childCollection2->id
        ])->create();

        $this->assertEquals(1, $childCollection1->left);
        $this->assertEquals(2, $childCollection1->right);
        $this->assertEquals(3, $childCollection2->left);
        $this->assertEquals(4, $grandChildCollection->left);
        $this->assertEquals(5, $grandChildCollection->right);
        $this->assertEquals(6, Collection::find($childCollection2->id)->right);

        $this->assertEquals(5,
            $grandChildCollection->computeNewLeft($parentCollection->id));
        $grandChildCollection->moveTo($parentCollection->id);

        $parentCollection = Collection::find($parentCollection->id);
        $childCollection1 = Collection::find($childCollection1->id);
        $childCollection2 = Collection::find($childCollection2->id);
        $grandChildCollection = Collection::find($grandChildCollection->id);

        $this->assertEquals($parentCollection->id, $grandChildCollection->parent_id);
        $this->assertEquals($parentCollection->id, $grandChildCollection->root_collection_id);
        $this->assertEquals(1, $grandChildCollection->depth);
        $this->assertEquals(1, $parentCollection->left);
        $this->assertEquals(2, $parentCollection->right);
        $this->assertEquals(1, $childCollection1->left);
        $this->assertEquals(2, $childCollection1->right);
        $this->assertEquals(3, $childCollection2->left);
        $this->assertEquals(4, $childCollection2->right);
        $this->assertEquals(5, $grandChildCollection->left);
        $this->assertEquals(6, $grandChildCollection->right);
    }

    public function testMoveCollectionUpToTheEndWithMethod()
    {
        User::factory()->create();
        $parentCollection = Collection::factory()->create();
        $childCollection1 = Collection::factory([
            'parent_id' => $parentCollection->id
        ])->create();
        $childCollection2 = Collection::factory([
            'parent_id' => $parentCollection->id
        ])->create();
        $grandChildCollection = Collection::factory([
            'parent_id' => $childCollection2->id
        ])->create();

        $grandChildCollection->appendTo($parentCollection->id);

        $parentCollection = Collection::find($parentCollection->id);
        $childCollection1 = Collection::find($childCollection1->id);
        $childCollection2 = Collection::find($childCollection2->id);
        $grandChildCollection = Collection::find($grandChildCollection->id);

        $this->assertEquals($parentCollection->id, $grandChildCollection->parent_id);
        $this->assertEquals($parentCollection->id, $grandChildCollection->root_collection_id);
        $this->assertEquals(1, $grandChildCollection->depth);
        $this->assertEquals(1, $parentCollection->left);
        $this->assertEquals(2, $parentCollection->right);
        $this->assertEquals(1, $childCollection1->left);
        $this->assertEquals(2, $childCollection1->right);
        $this->assertEquals(3, $childCollection2->left);
        $this->assertEquals(4, $childCollection2->right);
        $this->assertEquals(5, $grandChildCollection->left);
        $this->assertEquals(6, $grandChildCollection->right);
    }

    public function testMoveCollectionUpAndKeepLowestDepthIntact()
    {
        User::factory()->create();
        $parentCollection = Collection::factory()->create();
        $childCollection1 = Collection::factory([
            'parent_id' => $parentCollection->id
        ])->create(); // left: 1, right: 2
        $childCollection2 = Collection::factory([
            'parent_id' => $parentCollection->id
        ])->create(); // left: 3, right: 12
        $grandChildCollection1 = Collection::factory([
            'parent_id' => $childCollection2->id
        ])->create(); // left: 4, right: 5
        $grandChildCollection2 = Collection::factory([
            'parent_id' => $childCollection2->id
        ])->create(); // left: 6, right: 7
        $grandChildCollection3 = Collection::factory([
            'parent_id' => $childCollection2->id
        ])->create(); // left: 8, right: 9
        $grandChildCollection4 = Collection::factory([
            'parent_id' => $childCollection2->id
        ])->create(); // left: 10, right: 11

        $grandChildCollection3->moveTo($parentCollection->id);

        $parentCollection = Collection::find($parentCollection->id);
        $childCollection1 = Collection::find($childCollection1->id);
        $childCollection2 = Collection::find($childCollection2->id);
        $grandChildCollection1 = Collection::find($grandChildCollection1->id);
        $grandChildCollection2 = Collection::find($grandChildCollection2->id);
        $grandChildCollection3 = Collection::find($grandChildCollection3->id);
        $grandChildCollection4 = Collection::find($grandChildCollection4->id);

        $this->assertEquals($parentCollection->id, $grandChildCollection3->parent_id);
        $this->assertEquals($parentCollection->id, $grandChildCollection3->root_collection_id);
        $this->assertEquals(2, $grandChildCollection2->depth);
        $this->assertEquals(1, $grandChildCollection3->depth);
        $this->assertEquals(2, $grandChildCollection4->depth);

        $this->assertEquals(1, $childCollection1->left);
        $this->assertEquals(2, $childCollection1->right);
        $this->assertEquals(3, $childCollection2->left);
        $this->assertEquals(4, $grandChildCollection1->left);
        $this->assertEquals(5, $grandChildCollection1->right);
        $this->assertEquals(6, $grandChildCollection2->left);
        $this->assertEquals(7, $grandChildCollection2->right);
        $this->assertEquals(8, $grandChildCollection4->left);
        $this->assertEquals(9, $grandChildCollection4->right);
        $this->assertEquals(10, $childCollection2->right);
        $this->assertEquals(11, $grandChildCollection3->left);
        $this->assertEquals(12, $grandChildCollection3->right);
    }

    public function testMoveCollectionUpAndLeft()
    {
        User::factory()->create();
        $rootCollection = Collection::factory()->create();
        $childCollection1 = Collection::factory([
            'parent_id' => $rootCollection->id
        ])->create(); // left: 1, right: 2
        $childCollection2 = Collection::factory([
            'parent_id' => $rootCollection->id
        ])->create(); // left: 3, right: 6
        $grandChildCollection = Collection::factory([
            'parent_id' => $childCollection2->id
        ])->create(); // left: 4, right: 5

        $grandChildCollection->update([
            'parent_id' => $rootCollection->id,
            'left' => 1
        ]);

        $rootCollection = Collection::find($rootCollection->id);
        $childCollection1 = Collection::find($childCollection1->id);
        $childCollection2 = Collection::find($childCollection2->id);
        $grandChildCollection = Collection::find($grandChildCollection->id);

        $this->assertEquals($rootCollection->id, $grandChildCollection->parent_id);
        $this->assertEquals($rootCollection->id, $grandChildCollection->root_collection_id);
        $this->assertEquals(1, $grandChildCollection->depth);
        $this->assertEquals(1, $childCollection1->depth);
        $this->assertEquals(1, $childCollection2->depth);

        $this->assertEquals(1, $grandChildCollection->left);
        $this->assertEquals(2, $grandChildCollection->right);
        $this->assertEquals(3, $childCollection1->left);
        $this->assertEquals(4, $childCollection1->right);
        $this->assertEquals(5, $childCollection2->left);
        $this->assertEquals(6, $childCollection2->right);
    }

    public function testMoveCollectionDownToTheEnd()
    {
        User::factory()->create();
        $parentCollection = Collection::factory()->create();
        $childCollection1 = Collection::factory([
            'parent_id' => $parentCollection->id
        ])->create();
        $childCollection2 = Collection::factory([
            'parent_id' => $parentCollection->id
        ])->create();
        $grandChildCollection = Collection::factory([
            'parent_id' => $childCollection2->id
        ])->create();

        $this->assertEquals(4,
            $childCollection1->computeNewLeft($childCollection2->id));
        $childCollection1->moveTo($childCollection2->id);

        $parentCollection = Collection::find($parentCollection->id);
        $childCollection1 = Collection::find($childCollection1->id);
        $childCollection2 = Collection::find($childCollection2->id);
        $grandChildCollection = Collection::find($grandChildCollection->id);

        $this->assertEquals($childCollection2->id, $childCollection1->parent_id);
        $this->assertEquals($parentCollection->id, $childCollection1->root_collection_id);
        $this->assertEquals(1, $childCollection2->depth);
        $this->assertEquals(2, $childCollection1->depth);
        $this->assertEquals(2, $grandChildCollection->depth);

        $this->assertEquals(1, $childCollection2->left);
        $this->assertEquals(2, $grandChildCollection->left);
        $this->assertEquals(3, $grandChildCollection->right);
        $this->assertEquals(4, $childCollection1->left);
        $this->assertEquals(5, $childCollection1->right);
        $this->assertEquals(6, $childCollection2->right);

    }

    public function testMoveCollectionDown()
    {
        User::factory()->create();
        $parentCollection = Collection::factory()->create();
        $childCollection1 = Collection::factory([
            'parent_id' => $parentCollection->id
        ])->create();
        $childCollection2 = Collection::factory([
            'parent_id' => $parentCollection->id
        ])->create();
        $grandChildCollection1 = Collection::factory([
            'parent_id' => $childCollection2->id
        ])->create();
        $grandChildCollection2 = Collection::factory([
            'parent_id' => $childCollection2->id
        ])->create();

        $this->assertEquals(4,
            $childCollection1->computeNewLeft($childCollection2->id, 2));
        $childCollection1->update([
            'parent_id' => $childCollection2->id, 'left' => 4]);

        $parentCollection = Collection::find($parentCollection->id);
        $childCollection1 = Collection::find($childCollection1->id);
        $childCollection2 = Collection::find($childCollection2->id);
        $grandChildCollection1 = Collection::find($grandChildCollection1->id);
        $grandChildCollection2 = Collection::find($grandChildCollection2->id);

        $this->assertEquals($childCollection2->id, $childCollection1->parent_id);
        $this->assertEquals($parentCollection->id, $childCollection1->root_collection_id);
        $this->assertEquals(1, $childCollection2->depth);
        $this->assertEquals(2, $childCollection1->depth);
        $this->assertEquals(2, $grandChildCollection1->depth);

        $this->assertEquals(1, $childCollection2->left);
        $this->assertEquals(2, $grandChildCollection1->left);
        $this->assertEquals(3, $grandChildCollection1->right);
        $this->assertEquals(4, $childCollection1->left);
        $this->assertEquals(5, $childCollection1->right);
        $this->assertEquals(6, $grandChildCollection2->left);
        $this->assertEquals(7, $grandChildCollection2->right);
        $this->assertEquals(8, $childCollection2->right);
    }

    public function testMoveCollectionDownWithMethod()
    {
        User::factory()->create();
        $parentCollection = Collection::factory()->create();
        $childCollection1 = Collection::factory([
            'parent_id' => $parentCollection->id
        ])->create();
        $childCollection2 = Collection::factory([
            'parent_id' => $parentCollection->id
        ])->create(); // left: 3, right: 8
        $grandChildCollection1 = Collection::factory([
            'parent_id' => $childCollection2->id
        ])->create(); // left: 4, right : 5
        $grandChildCollection2 = Collection::factory([
            'parent_id' => $childCollection2->id
        ])->create(); // left: 6, right : 7

        $this->assertEquals(4,
            $childCollection1->computeNewLeft($childCollection2->id, 2));
        $childCollection1->moveTo($childCollection2->id, 2);

        $parentCollection = Collection::find($parentCollection->id);
        $childCollection1 = Collection::find($childCollection1->id);
        $childCollection2 = Collection::find($childCollection2->id);
        $grandChildCollection1 = Collection::find($grandChildCollection1->id);
        $grandChildCollection2 = Collection::find($grandChildCollection2->id);

        $this->assertEquals($childCollection2->id, $childCollection1->parent_id);
        $this->assertEquals($parentCollection->id, $childCollection1->root_collection_id);
        $this->assertEquals(1, $childCollection2->depth);
        $this->assertEquals(2, $childCollection1->depth);
        $this->assertEquals(2, $grandChildCollection1->depth);

        $this->assertEquals(1, $childCollection2->left);
        $this->assertEquals(2, $grandChildCollection1->left);
        $this->assertEquals(3, $grandChildCollection1->right);
        $this->assertEquals(4, $childCollection1->left);
        $this->assertEquals(5, $childCollection1->right);
        $this->assertEquals(6, $grandChildCollection2->left);
        $this->assertEquals(7, $grandChildCollection2->right);
        $this->assertEquals(8, $childCollection2->right);
    }

    public function testMoveCollectionDownAndLeft()
    {
        User::factory()->create();
        $parentCollection = Collection::factory()->create();
        $childCollection1 = Collection::factory([
            'parent_id' => $parentCollection->id
        ])->create();
        $childCollection2 = Collection::factory([
            'parent_id' => $parentCollection->id
        ])->create();
        $grandChildCollection = Collection::factory([
            'parent_id' => $childCollection2->id
        ])->create();

        $childCollection1->update([
            'parent_id' => $childCollection2->id,
            'left' => 2
        ]);

        $parentCollection = Collection::find($parentCollection->id);
        $childCollection1 = Collection::find($childCollection1->id);
        $childCollection2 = Collection::find($childCollection2->id);
        $grandChildCollection = Collection::find($grandChildCollection->id);

        $this->assertEquals($childCollection2->id, $childCollection1->parent_id);
        $this->assertEquals($parentCollection->id, $childCollection1->root_collection_id);
        $this->assertEquals(1, $childCollection2->depth);
        $this->assertEquals(2, $childCollection1->depth);
        $this->assertEquals(2, $grandChildCollection->depth);

        $this->assertEquals(1, $childCollection2->left);
        $this->assertEquals(2, $childCollection1->left);
        $this->assertEquals(3, $childCollection1->right);
        $this->assertEquals(4, $grandChildCollection->left);
        $this->assertEquals(5, $grandChildCollection->right);
        $this->assertEquals(6, $childCollection2->right);

    }

    public function testMoveCollectionDownAndLeftWithMethod()
    {
        User::factory()->create();
        $parentCollection = Collection::factory()->create();
        $childCollection1 = Collection::factory([
            'parent_id' => $parentCollection->id
        ])->create();
        $childCollection2 = Collection::factory([
            'parent_id' => $parentCollection->id
        ])->create();
        $grandChildCollection = Collection::factory([
            'parent_id' => $childCollection2->id
        ])->create();

        $this->assertEquals(2,
            $childCollection1->computeNewLeft($childCollection2->id, 1));
        $childCollection1->moveTo($childCollection2->id, 1);

        $parentCollection = Collection::find($parentCollection->id);
        $childCollection1 = Collection::find($childCollection1->id);
        $childCollection2 = Collection::find($childCollection2->id);
        $grandChildCollection = Collection::find($grandChildCollection->id);

        $this->assertEquals($childCollection2->id, $childCollection1->parent_id);
        $this->assertEquals($parentCollection->id, $childCollection1->root_collection_id);
        $this->assertEquals(1, $childCollection2->depth);
        $this->assertEquals(2, $childCollection1->depth);
        $this->assertEquals(2, $grandChildCollection->depth);

        $this->assertEquals(1, $childCollection2->left);
        $this->assertEquals(2, $childCollection1->left);
        $this->assertEquals(3, $childCollection1->right);
        $this->assertEquals(4, $grandChildCollection->left);
        $this->assertEquals(5, $grandChildCollection->right);
        $this->assertEquals(6, $childCollection2->right);

    }

    public function testMoveNestedCollectionToOtherRootToTheEnd()
    {
        User::factory()->create();
        $rootCollection1 = Collection::factory()->create();
        $rootCollection2 = Collection::factory()->create();
        $rootCollection3 = Collection::factory()->create();
        $childCollection1 = Collection::factory([
            'parent_id' => $rootCollection3->id
        ])->create();
        $childCollection2 = Collection::factory([
            'parent_id' => $rootCollection3->id
        ])->create();
        $grandChildCollection1 = Collection::factory([
            'parent_id' => $childCollection1->id
        ])->create();
        $grandChildCollection2 = Collection::factory([
            'parent_id' => $childCollection1->id
        ])->create();

        $childCollection1 = Collection::find($childCollection1->id);
        $childCollection2 = Collection::find($childCollection2->id);

        $this->assertEquals(1, $childCollection1->left);
        $this->assertEquals(6, $childCollection1->right);
        $this->assertEquals(7, $childCollection2->left);
        $this->assertEquals(8, $childCollection2->right);

        $childCollection1->moveTo($rootCollection1->id);

        $rootCollection1 = Collection::find($rootCollection1->id);
        $rootCollection2 = Collection::find($rootCollection2->id);
        $rootCollection3 = Collection::find($rootCollection3->id);
        $childCollection1 = Collection::find($childCollection1->id);
        $childCollection2 = Collection::find($childCollection2->id);
        $grandChildCollection1 = Collection::find($grandChildCollection1->id);
        $grandChildCollection2 = Collection::find($grandChildCollection2->id);

        $this->assertEquals($rootCollection1->id, $childCollection1->root_collection_id);
        $this->assertEquals($rootCollection3->id, $childCollection2->root_collection_id);
        $this->assertEquals($rootCollection1->id, $grandChildCollection1->root_collection_id);
        $this->assertEquals($rootCollection1->id, $grandChildCollection2->root_collection_id);
        $this->assertEquals(1, $childCollection1->depth);

        $this->assertEquals(1, $rootCollection1->left);
        $this->assertEquals(2, $rootCollection1->right);
        $this->assertEquals(3, $rootCollection2->left);
        $this->assertEquals(4, $rootCollection2->right);
        $this->assertEquals(5, $rootCollection3->left);
        $this->assertEquals(6, $rootCollection3->right);

        $this->assertEquals(1, $childCollection2->left);
        $this->assertEquals(2, $childCollection2->right);
        $this->assertEquals(1, $childCollection1->left);
        $this->assertEquals(2, $grandChildCollection1->left);
        $this->assertEquals(3, $grandChildCollection1->right);
        $this->assertEquals(4, $grandChildCollection2->left);
        $this->assertEquals(5, $grandChildCollection2->right);
        $this->assertEquals(6, $childCollection1->right);

    }

    public function testMoveNestedCollectionToOtherRootAndInBetween()
    {
        User::factory()->create();
        $rootCollection1 = Collection::factory()->create();
        $rootCollection2 = Collection::factory()->create();
        $rootCollection3 = Collection::factory()->create();
        $childCollection3 = Collection::factory([
            'parent_id' => $rootCollection3->id
        ])->create(); // left: 1, right: 6
        $childCollection1 = Collection::factory([
            'parent_id' => $rootCollection1->id
        ])->create(); // left: 1, right: 2
        $childCollection2 = Collection::factory([
            'parent_id' => $rootCollection1->id
        ])->create(); // left: 3, right: 6
        $grandChildCollection1 = Collection::factory([
            'parent_id' => $childCollection3->id
        ])->create(); // left: 2, right: 3
        $grandChildCollection2 = Collection::factory([
            'parent_id' => $childCollection3->id
        ])->create(); // left: 4, right: 5
        $collectionOfChild2 = Collection::factory([
            'parent_id' => $childCollection2->id
        ])->create(); // left: 4, right: 5

        $childCollection3 = Collection::find($childCollection3->id);
        $childCollection2 = Collection::find($childCollection2->id);

        $this->assertEquals(1, $childCollection3->left);
        $this->assertEquals(6, $childCollection3->right);
        $this->assertEquals(3, $childCollection2->left);
        $this->assertEquals(6, $childCollection2->right);

        $this->assertEquals(3, $childCollection3
            ->computeNewLeft($rootCollection1->id, 2));
        $childCollection3->update([
            'parent_id' => $rootCollection1->id, 'left' => 3]);

        $rootCollection1 = Collection::find($rootCollection1->id);
        $rootCollection3 = Collection::find($rootCollection3->id);
        $childCollection3 = Collection::find($childCollection3->id);
        $childCollection1 = Collection::find($childCollection1->id);
        $childCollection2 = Collection::find($childCollection2->id);
        $grandChildCollection1 = Collection::find($grandChildCollection1->id);
        $grandChildCollection2 = Collection::find($grandChildCollection2->id);
        $collectionOfChild2 = Collection::find($collectionOfChild2->id);

        $this->assertEquals($rootCollection1->id, $childCollection3->root_collection_id);
        $this->assertEquals($rootCollection1->id, $childCollection1->root_collection_id);
        $this->assertEquals($rootCollection1->id, $grandChildCollection1->root_collection_id);
        $this->assertEquals($rootCollection1->id, $grandChildCollection2->root_collection_id);
        $this->assertEquals($rootCollection1->id, $collectionOfChild2->root_collection_id);
        $this->assertEquals(1, $childCollection3->depth);
        $this->assertEquals(2, $grandChildCollection1->depth);
        $this->assertEquals(2, $collectionOfChild2->depth);

        $this->assertEquals(1, $rootCollection1->left);
        $this->assertEquals(2, $rootCollection1->right);
        $this->assertEquals(5, $rootCollection3->left);
        $this->assertEquals(6, $rootCollection3->right);

        $this->assertEquals(1, $childCollection1->left);
        $this->assertEquals(2, $childCollection1->right);
        $this->assertEquals(3, $childCollection3->left);
        $this->assertEquals(4, $grandChildCollection1->left);
        $this->assertEquals(5, $grandChildCollection1->right);
        $this->assertEquals(6, $grandChildCollection2->left);
        $this->assertEquals(7, $grandChildCollection2->right);
        $this->assertEquals(8, $childCollection3->right);
        $this->assertEquals(9, $childCollection2->left);
        $this->assertEquals(10, $collectionOfChild2->left);
        $this->assertEquals(11, $collectionOfChild2->right);
        $this->assertEquals(12, $childCollection2->right);

    }

    public function testMoveNestedCollectionToRoot()
    {
        User::factory()->create();
        $rootCollection1 = Collection::factory()->create();
        $rootCollection3 = Collection::factory()->create();
        $childCollection1 = Collection::factory([
            'parent_id' => $rootCollection3->id
        ])->create();
        $childCollection2 = Collection::factory([
            'parent_id' => $rootCollection3->id
        ])->create();
        $grandChildCollection1 = Collection::factory([
            'parent_id' => $childCollection1->id
        ])->create();
        $grandChildCollection2 = Collection::factory([
            'parent_id' => $childCollection1->id
        ])->create();

        $newLeft = 3;
        $this->assertEquals($newLeft, $childCollection1
            ->computeNewLeft(null, 2));
        Collection::find($childCollection1->id)->update([
            'parent_id' => null,
            'left' => $newLeft
        ]);

        $rootCollection1 = Collection::find($rootCollection1->id);
        $rootCollection3 = Collection::find($rootCollection3->id);
        $childCollection1 = Collection::find($childCollection1->id);
        $childCollection2 = Collection::find($childCollection2->id);
        $grandChildCollection1 = Collection::find($grandChildCollection1->id);
        $grandChildCollection2 = Collection::find($grandChildCollection2->id);

        $this->assertEmpty($childCollection1->root_collection_id);
        $this->assertEquals($childCollection1->id, $grandChildCollection1->root_collection_id);
        $this->assertEquals($childCollection1->id, $grandChildCollection2->root_collection_id);
        $this->assertEquals($rootCollection3->id, $childCollection2->root_collection_id);
        $this->assertEquals(0, $childCollection1->depth);
        $this->assertEquals(1, $childCollection2->depth);
        $this->assertEquals(1, $grandChildCollection1->depth);
        $this->assertEquals(1, $grandChildCollection2->depth);

        $this->assertEquals(1, $rootCollection1->left);
        $this->assertEquals(2, $rootCollection1->right);
        $this->assertEquals(3, $childCollection1->left);
        $this->assertEquals(4, $childCollection1->right);
        $this->assertEquals(5, $rootCollection3->left);
        $this->assertEquals(6, $rootCollection3->right);

        $this->assertEquals(1, $childCollection2->left);
        $this->assertEquals(2, $childCollection2->right);
        $this->assertEquals(1, $grandChildCollection1->left);
        $this->assertEquals(2, $grandChildCollection1->right);
        $this->assertEquals(3, $grandChildCollection2->left);
        $this->assertEquals(4, $grandChildCollection2->right);

    }

    public function testMoveNestedCollectionToRootComplex()
    {
        User::factory()->create();
        $rootCollection1 = Collection::factory()->create();
        $rootCollection3 = Collection::factory()->create();
        $childCollection1 = Collection::factory([
            'parent_id' => $rootCollection1->id
        ])->create(); // left: 1, right: 2
        $childCollection2 = Collection::factory([
            'parent_id' => $rootCollection1->id
        ])->create(); // left: 3, right: 8
        $childCollection3 = Collection::factory([
            'parent_id' => $rootCollection1->id
        ])->create(); // left: 9, right: 12
        $grandChildCollection1 = Collection::factory([
            'parent_id' => $childCollection2->id
        ])->create(); // left: 4, right: 6
        $grandChildCollection2 = Collection::factory([
            'parent_id' => $childCollection2->id
        ])->create(); // left: 6, right: 7
        $collectionOfChild3 = Collection::factory([
            'parent_id' => $childCollection3->id
        ])->create(); // left: 10, right: 11

        $childCollection2 = Collection::find($childCollection2->id);
        $this->assertEquals(3, $childCollection2->left);
        $this->assertEquals(8, $childCollection2->right);

        $left = 3;
        $this->assertEquals($left,
            Collection::find($childCollection2->id)
                ->computeNewLeft(null, 2));
        $childCollection2->update([
            'parent_id' => null, 'left' => $left]);

        $rootCollection1 = Collection::find($rootCollection1->id);
        $rootCollection3 = Collection::find($rootCollection3->id);
        $childCollection2 = Collection::find($childCollection2->id);
        $childCollection1 = Collection::find($childCollection1->id);
        $childCollection3 = Collection::find($childCollection3->id);
        $grandChildCollection1 = Collection::find($grandChildCollection1->id);
        $grandChildCollection2 = Collection::find($grandChildCollection2->id);
        $collectionOfChild3 = Collection::find($collectionOfChild3->id);

        $this->assertEmpty($childCollection2->root_collection_id);
        $this->assertEquals($rootCollection1->id, $childCollection1->root_collection_id);
        $this->assertEquals($childCollection2->id, $grandChildCollection1->root_collection_id);
        $this->assertEquals($childCollection2->id, $grandChildCollection2->root_collection_id);
        $this->assertEquals($rootCollection1->id, $collectionOfChild3->root_collection_id);
        $this->assertEquals(0, $childCollection2->depth);
        $this->assertEquals(1, $grandChildCollection1->depth);
        $this->assertEquals(2, $collectionOfChild3->depth);

        $this->assertEquals(1, $rootCollection1->left);
        $this->assertEquals(2, $rootCollection1->right);
        $this->assertEquals($left, $childCollection2->left);
        $this->assertEquals(4, $childCollection2->right); //<--
        $this->assertEquals(5, $rootCollection3->left);
        $this->assertEquals(6, $rootCollection3->right);

        $this->assertEquals(1, $childCollection1->left);
        $this->assertEquals(2, $childCollection1->right);
        $this->assertEquals(3, $childCollection3->left);
        $this->assertEquals(4, $collectionOfChild3->left);
        $this->assertEquals(5, $collectionOfChild3->right);
        $this->assertEquals(6, $childCollection3->right);

        $this->assertEquals(1, $grandChildCollection1->left);
        $this->assertEquals(2, $grandChildCollection1->right);
        $this->assertEquals(3, $grandChildCollection2->left);
        $this->assertEquals(4, $grandChildCollection2->right);

    }

    public function testMoveNestedCollectionFromRoot()
    {
        User::factory()->create();
        $rootCollection1 = Collection::factory()->create();
        $rootCollection2 = Collection::factory()->create();
        $firstChildOfRoot1 = Collection::factory([
            'parent_id' => $rootCollection1->id
        ])->create(); // left: 1, right: 2
        $secondChildOfRoot1 = Collection::factory([
            'parent_id' => $rootCollection1->id
        ])->create(); // left: 3, right: 4
        $firstChildOfRoot2 = Collection::factory([
            'parent_id' => $rootCollection2->id
        ])->create(); // left: 1, right: 2

        $this->assertEquals(2,
            $rootCollection1->computeNewLeft($firstChildOfRoot2->id, 1));
        $rootCollection1->moveTo($firstChildOfRoot2->id, 1);

        $rootCollection1 = Collection::find($rootCollection1->id);
        $rootCollection2 = Collection::find($rootCollection2->id);
        $firstChildOfRoot1 = Collection::find($firstChildOfRoot1->id);
        $secondChildOfRoot1 = Collection::find($secondChildOfRoot1->id);
        $firstChildOfRoot2 = Collection::find($firstChildOfRoot2->id);

        $this->assertEmpty($rootCollection2->root_collection_id);
        $this->assertEquals($rootCollection2->id, $rootCollection1->root_collection_id);
        $this->assertEquals($rootCollection2->id, $secondChildOfRoot1->root_collection_id);
        $this->assertEquals(1, $firstChildOfRoot2->depth);
        $this->assertEquals(2, $rootCollection1->depth);
        $this->assertEquals(3, $firstChildOfRoot1->depth);
        $this->assertEquals(3, $secondChildOfRoot1->depth);

        $this->assertEquals(1, $rootCollection2->left);
        $this->assertEquals(2, $rootCollection2->right);
        $this->assertEquals(1, $firstChildOfRoot2->left);
        $this->assertEquals(2, $rootCollection1->left);
        $this->assertEquals(3, $firstChildOfRoot1->left);
        $this->assertEquals(4, $firstChildOfRoot1->right);
        $this->assertEquals(5, $secondChildOfRoot1->left);
        $this->assertEquals(6, $secondChildOfRoot1->right);
        $this->assertEquals(7, $rootCollection1->right);
        $this->assertEquals(8, $firstChildOfRoot2->right);

    }

    public function testMoveNestedCollectionFromRootComplex()
    {
        User::factory()->create();
        $rootCollection1 = Collection::factory()->create();
        $rootCollection2 = Collection::factory()->create();
        $firstChildOfRoot1 = Collection::factory([
            'parent_id' => $rootCollection1->id
        ])->create(); // left: 1, right: 2
        $secondChildOfRoot1 = Collection::factory([
            'parent_id' => $rootCollection1->id
        ])->create(); // left: 3, right: 4
        $thirdChildOfRoot1 = Collection::factory([
            'parent_id' => $rootCollection1->id
        ])->create(); // left: 5, right: 8
        $firstChildOfRoot2 = Collection::factory([
            'parent_id' => $rootCollection2->id
        ])->create(); // left: 1, right: 4
        $grandChildOfRoot1 = Collection::factory([
            'parent_id' => $thirdChildOfRoot1->id
        ])->create(); // left: 6, right: 7
        $grandChildOfRoot2 = Collection::factory([
            'parent_id' => $firstChildOfRoot2->id
        ])->create(); // left: 2, right: 3

        $this->assertEquals(2,
            $rootCollection1->computeNewLeft($firstChildOfRoot2->id, 1));
        $rootCollection1->moveTo($firstChildOfRoot2->id, 1);

        $rootCollection1 = Collection::find($rootCollection1->id);
        $rootCollection2 = Collection::find($rootCollection2->id);
        $firstChildOfRoot1 = Collection::find($firstChildOfRoot1->id);
        $secondChildOfRoot1 = Collection::find($secondChildOfRoot1->id);
        $thirdChildOfRoot1 = Collection::find($thirdChildOfRoot1->id);
        $firstChildOfRoot2 = Collection::find($firstChildOfRoot2->id);
        $grandChildOfRoot1 = Collection::find($grandChildOfRoot1->id);
        $grandChildOfRoot2 = Collection::find($grandChildOfRoot2->id);

        $this->assertEmpty($rootCollection2->root_collection_id);
        $this->assertEquals($rootCollection2->id, $rootCollection1->root_collection_id);
        $this->assertEquals($rootCollection2->id, $secondChildOfRoot1->root_collection_id);
        $this->assertEquals($rootCollection2->id, $thirdChildOfRoot1->root_collection_id);
        $this->assertEquals($rootCollection2->id, $grandChildOfRoot1->root_collection_id);
        $this->assertEquals(1, $firstChildOfRoot2->depth);
        $this->assertEquals(2, $grandChildOfRoot2->depth);
        $this->assertEquals(2, $rootCollection1->depth);
        $this->assertEquals(3, $firstChildOfRoot1->depth);
        $this->assertEquals(3, $secondChildOfRoot1->depth);
        $this->assertEquals(4, $grandChildOfRoot1->depth);

        $this->assertEquals(1, $rootCollection2->left);
        $this->assertEquals(2, $rootCollection2->right);
        $this->assertEquals(1, $firstChildOfRoot2->left);
        $this->assertEquals(2, $rootCollection1->left);
        $this->assertEquals(3, $firstChildOfRoot1->left);
        $this->assertEquals(4, $firstChildOfRoot1->right);
        $this->assertEquals(5, $secondChildOfRoot1->left);
        $this->assertEquals(6, $secondChildOfRoot1->right);
        $this->assertEquals(7, $thirdChildOfRoot1->left);
        $this->assertEquals(8, $grandChildOfRoot1->left);
        $this->assertEquals(9, $grandChildOfRoot1->right);
        $this->assertEquals(10, $thirdChildOfRoot1->right);
        $this->assertEquals(11, $rootCollection1->right);
        $this->assertEquals(12, $grandChildOfRoot2->left);
        $this->assertEquals(13, $grandChildOfRoot2->right);
        $this->assertEquals(14, $firstChildOfRoot2->right);

    }

    public function testMoveNestedCollectionToOtherUser()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $rootCollection1 = Collection::factory()->create();
        $rootCollection2 = Collection::factory()->create();
        $childCollection1 = Collection::factory([
            'parent_id' => $rootCollection1->id
        ])->create(); // left: 1, right: 2
        $childCollection2 = Collection::factory([
            'parent_id' => $rootCollection1->id
        ])->create(); // left: 3, right: 6
        $collectionOfChild2 = Collection::factory([
            'parent_id' => $childCollection2->id
        ])->create(); // left: 4, right: 5

        $rootCollection3 = Collection::factory([
            'user_id' => $user2->id
        ])->create();
        $childCollection3 = Collection::factory([
            'parent_id' => $rootCollection3->id,
            'user_id' => $user2->id
        ])->create(); // left: 1, right: 6
        $firstCollectionOfChild3 = Collection::factory([
            'parent_id' => $childCollection3->id,
            'user_id' => $user2->id
        ])->create(); // left: 2, right: 3
        $secondCollectionOfChild3 = Collection::factory([
            'parent_id' => $childCollection3->id,
            'user_id' => $user2->id
        ])->create(); // left: 4, right: 5

        $childCollection3 = Collection::find($childCollection3->id);

        $this->assertEquals(3, $childCollection3
            ->computeNewLeft($rootCollection1->id, 2));
        $childCollection3->update([
            'parent_id' => $rootCollection1->id, 'left' => 3]);

        $rootCollection1 = Collection::find($rootCollection1->id);
        $rootCollection3 = Collection::find($rootCollection3->id);
        $childCollection3 = Collection::find($childCollection3->id);
        $childCollection1 = Collection::find($childCollection1->id);
        $childCollection2 = Collection::find($childCollection2->id);
        $firstCollectionOfChild3 = Collection::find($firstCollectionOfChild3->id);
        $secondCollectionOfChild3 = Collection::find($secondCollectionOfChild3->id);
        $collectionOfChild2 = Collection::find($collectionOfChild2->id);

        $this->assertEquals($rootCollection1->id, $childCollection3->root_collection_id);
        $this->assertEquals($rootCollection1->id, $childCollection1->root_collection_id);
        $this->assertEquals($rootCollection1->id, $firstCollectionOfChild3->root_collection_id);
        $this->assertEquals($rootCollection1->id, $secondCollectionOfChild3->root_collection_id);
        $this->assertEquals($rootCollection1->id, $collectionOfChild2->root_collection_id);
        $this->assertEquals(1, $childCollection3->depth);
        $this->assertEquals(2, $firstCollectionOfChild3->depth);
        $this->assertEquals(2, $collectionOfChild2->depth);
        $this->assertEquals($user1->id, $childCollection3->user_id);
        $this->assertEquals($user1->id, $firstCollectionOfChild3->user_id);
        $this->assertEquals($user1->id, $secondCollectionOfChild3->user_id);

        $this->assertEquals(1, $rootCollection1->left);
        $this->assertEquals(2, $rootCollection1->right);
        $this->assertEquals(3, $rootCollection2->left);
        $this->assertEquals(4, $rootCollection2->right);
        $this->assertEquals(1, $rootCollection3->left);
        $this->assertEquals(2, $rootCollection3->right);

        $this->assertEquals(1, $childCollection1->left);
        $this->assertEquals(2, $childCollection1->right);
        $this->assertEquals(3, $childCollection3->left);
        $this->assertEquals(4, $firstCollectionOfChild3->left);
        $this->assertEquals(5, $firstCollectionOfChild3->right);
        $this->assertEquals(6, $secondCollectionOfChild3->left);
        $this->assertEquals(7, $secondCollectionOfChild3->right);
        $this->assertEquals(8, $childCollection3->right);
        $this->assertEquals(9, $childCollection2->left);
        $this->assertEquals(10, $collectionOfChild2->left);
        $this->assertEquals(11, $collectionOfChild2->right);
        $this->assertEquals(12, $childCollection2->right);

    }

    public function testMoveNestedCollectionToOtherUserAndKeepCoincidentallyPosition()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $rootCollection1 = Collection::factory()->create();
        $rootCollection2 = Collection::factory()->create();
        $childCollection1 = Collection::factory([
            'parent_id' => $rootCollection1->id
        ])->create(); // left: 1, right: 2
        $childCollection2 = Collection::factory([
            'parent_id' => $rootCollection1->id
        ])->create(); // left: 3, right: 6
        $collectionOfChild2 = Collection::factory([
            'parent_id' => $childCollection2->id
        ])->create(); // left: 4, right: 5

        $rootCollection3 = Collection::factory([
            'user_id' => $user2->id
        ])->create();
        $childCollection3 = Collection::factory([
            'parent_id' => $rootCollection3->id,
            'user_id' => $user2->id
        ])->create();
        $childCollection4 = Collection::factory([
            'parent_id' => $rootCollection3->id,
            'user_id' => $user2->id
        ])->create(); // left: 3, right: 8
        $firstCollectionOfChild4 = Collection::factory([
            'parent_id' => $childCollection4->id,
            'user_id' => $user2->id
        ])->create(); // left: 4, right: 5
        $secondCollectionOfChild4 = Collection::factory([
            'parent_id' => $childCollection4->id,
            'user_id' => $user2->id
        ])->create(); // left: 6, right: 7

        $childCollection4 = Collection::find($childCollection4->id);

        $this->assertEquals(3, $childCollection4
            ->computeNewLeft($rootCollection1->id, 2));
        $childCollection4->update([
            'parent_id' => $rootCollection1->id, 'left' => 3]);

        $rootCollection1 = Collection::find($rootCollection1->id);
        $rootCollection3 = Collection::find($rootCollection3->id);
        $childCollection1 = Collection::find($childCollection1->id);
        $childCollection2 = Collection::find($childCollection2->id);
        $childCollection3 = Collection::find($childCollection3->id);
        $childCollection4 = Collection::find($childCollection4->id);
        $firstCollectionOfChild4 = Collection::find($firstCollectionOfChild4->id);
        $secondCollectionOfChild4 = Collection::find($secondCollectionOfChild4->id);
        $collectionOfChild2 = Collection::find($collectionOfChild2->id);

        $this->assertEquals($rootCollection1->id, $childCollection4->root_collection_id);
        $this->assertEquals($rootCollection1->id, $childCollection1->root_collection_id);
        $this->assertEquals($rootCollection1->id, $firstCollectionOfChild4->root_collection_id);
        $this->assertEquals($rootCollection1->id, $secondCollectionOfChild4->root_collection_id);
        $this->assertEquals($rootCollection1->id, $collectionOfChild2->root_collection_id);
        $this->assertEquals(1, $childCollection4->depth);
        $this->assertEquals(2, $firstCollectionOfChild4->depth);
        $this->assertEquals(2, $collectionOfChild2->depth);
        $this->assertEquals($user2->id, $childCollection3->user_id);
        $this->assertEquals($user1->id, $childCollection4->user_id);
        $this->assertEquals($user1->id, $firstCollectionOfChild4->user_id);
        $this->assertEquals($user1->id, $secondCollectionOfChild4->user_id);

        $this->assertEquals(1, $rootCollection1->left);
        $this->assertEquals(2, $rootCollection1->right);
        $this->assertEquals(3, $rootCollection2->left);
        $this->assertEquals(4, $rootCollection2->right);
        $this->assertEquals(1, $rootCollection3->left);
        $this->assertEquals(2, $rootCollection3->right);

        $this->assertEquals(1, $childCollection1->left);
        $this->assertEquals(2, $childCollection1->right);
        $this->assertEquals(3, $childCollection4->left);
        $this->assertEquals(4, $firstCollectionOfChild4->left);
        $this->assertEquals(5, $firstCollectionOfChild4->right);
        $this->assertEquals(6, $secondCollectionOfChild4->left);
        $this->assertEquals(7, $secondCollectionOfChild4->right);
        $this->assertEquals(8, $childCollection4->right);
        $this->assertEquals(9, $childCollection2->left);
        $this->assertEquals(10, $collectionOfChild2->left);
        $this->assertEquals(11, $collectionOfChild2->right);
        $this->assertEquals(12, $childCollection2->right);

        $this->assertEquals(1, $childCollection3->left);
        $this->assertEquals(2, $childCollection3->right);

    }

    public function testMoveNestedCollectionToRootOfOtherUser()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $rootCollection1 = Collection::factory([
            'user_id' => $user2->id
        ])->create();
        $rootCollection2 = Collection::factory([
            'user_id' => $user2->id
        ])->create();
        $rootCollection3 = Collection::factory()->create();
        $childCollection1 = Collection::factory([
            'parent_id' => $rootCollection3->id
        ])->create();
        $childCollection2 = Collection::factory([
            'parent_id' => $rootCollection3->id
        ])->create();
        $grandChildCollection1 = Collection::factory([
            'parent_id' => $childCollection1->id
        ])->create();
        $grandChildCollection2 = Collection::factory([
            'parent_id' => $childCollection1->id
        ])->create();

        $newLeft = 3;
        $this->assertEquals($newLeft,
            $childCollection1->computeNewLeft(null, 2, $user2->id));
        Collection::find($childCollection1->id)->update([
            'parent_id' => null,
            'left' => $newLeft,
            'user_id' => $user2->id
        ]);

        $rootCollection1 = Collection::find($rootCollection1->id);
        $rootCollection2 = Collection::find($rootCollection2->id);
        $rootCollection3 = Collection::find($rootCollection3->id);
        $childCollection1 = Collection::find($childCollection1->id);
        $childCollection2 = Collection::find($childCollection2->id);
        $grandChildCollection1 = Collection::find($grandChildCollection1->id);
        $grandChildCollection2 = Collection::find($grandChildCollection2->id);

        $this->assertEmpty($childCollection1->root_collection_id);
        $this->assertEquals($childCollection1->id, $grandChildCollection1->root_collection_id);
        $this->assertEquals($childCollection1->id, $grandChildCollection2->root_collection_id);
        $this->assertEquals($rootCollection3->id, $childCollection2->root_collection_id);
        $this->assertEquals(0, $childCollection1->depth);
        $this->assertEquals(1, $childCollection2->depth);
        $this->assertEquals(1, $grandChildCollection1->depth);
        $this->assertEquals(1, $grandChildCollection2->depth);
        $this->assertEquals($user2->id, $rootCollection1->user_id);
        $this->assertEquals($user2->id, $rootCollection2->user_id);
        $this->assertEquals($user1->id, $rootCollection3->user_id);
        $this->assertEquals($user2->id, $childCollection1->user_id);
        $this->assertEquals($user1->id, $childCollection2->user_id);
        $this->assertEquals($user2->id, $grandChildCollection1->user_id);
        $this->assertEquals($user2->id, $grandChildCollection2->user_id);

        $this->assertEquals(1, $rootCollection1->left);
        $this->assertEquals(2, $rootCollection1->right);
        $this->assertEquals(3, $childCollection1->left);
        $this->assertEquals(4, $childCollection1->right);
        $this->assertEquals(5, $rootCollection2->left);
        $this->assertEquals(6, $rootCollection2->right);
        $this->assertEquals(1, $rootCollection3->left);
        $this->assertEquals(2, $rootCollection3->right);

        $this->assertEquals(1, $grandChildCollection1->left);
        $this->assertEquals(2, $grandChildCollection1->right);
        $this->assertEquals(3, $grandChildCollection2->left);
        $this->assertEquals(4, $grandChildCollection2->right);
        $this->assertEquals(1, $childCollection2->left);
        $this->assertEquals(2, $childCollection2->right);

    }

    public function testMoveNestedCollectionToOtherUserAsOnlyRoot()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $rootCollection = Collection::factory()->create();
        $collection1 = Collection::factory([
            'parent_id' => $rootCollection->id
        ])->create();
        $grandChildCollection1 = Collection::factory([
            'parent_id' => $collection1->id
        ])->create();
        $grandChildCollection2 = Collection::factory([
            'parent_id' => $collection1->id
        ])->create();
        $collection2 = Collection::factory([
            'parent_id' => $rootCollection->id
        ])->create();

        $collection1 = Collection::find($collection1->id);

        $this->assertEquals(
            1,
            $collection1->computeNewLeft(null, 1, $user2->id)
        );
        $collection1->moveTo(null, 1, $user2->id);

        $rootCollection = Collection::find($rootCollection->id);
        $collection1 = Collection::find($collection1->id);
        $collection2 = Collection::find($collection2->id);
        $grandChildCollection1 = Collection::find($grandChildCollection1->id);
        $grandChildCollection2 = Collection::find($grandChildCollection2->id);

        $this->assertEquals(0, $collection1->depth);
        $this->assertEquals(1, $grandChildCollection1->depth);
        $this->assertEquals(1, $grandChildCollection2->depth);
        $this->assertEquals($user1->id, $rootCollection->user_id);
        $this->assertEquals($user1->id, $collection2->user_id);
        $this->assertEquals($user2->id, $collection1->user_id);
        $this->assertEquals($user2->id, $grandChildCollection1->user_id);
        $this->assertEquals($user2->id, $grandChildCollection2->user_id);

        $this->assertEquals(1, $rootCollection->left);
        $this->assertEquals(1, $collection1->left);
        $this->assertEquals(2, $collection1->right);
        $this->assertEquals(1, $grandChildCollection1->left);
        $this->assertEquals(2, $grandChildCollection1->right);
        $this->assertEquals(3, $grandChildCollection2->left);
        $this->assertEquals(4, $grandChildCollection2->right);

        $this->assertEquals(1, $collection2->left);
        $this->assertEquals(2, $collection2->right);
        $this->assertEquals(2,
            Collection::where('user_id', $user1->id)->count());
    }

    public function testMoveNestedCollectionFromRootOfOtherUser()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $rootCollection1 = Collection::factory([
            'user_id' => $user1->id
        ])->create();
        $firstChildOfRoot1 = Collection::factory([
            'parent_id' => $rootCollection1->id
        ])->create(); // left: 1, right: 2
        $secondChildOfRoot1 = Collection::factory([
            'parent_id' => $rootCollection1->id
        ])->create(); // left: 3, right: 4
        $rootCollection2 = Collection::factory([
            'user_id' => $user2->id
        ])->create();
        $firstChildOfRoot2 = Collection::factory([
            'parent_id' => $rootCollection2->id,
            'user_id' => $user2->id
        ])->create(); // left: 1, right: 2

        $this->assertEquals(2,
            $rootCollection1->computeNewLeft($firstChildOfRoot2->id, 1));
        $rootCollection1->moveTo($firstChildOfRoot2->id, 1);

        $rootCollection1 = Collection::find($rootCollection1->id);
        $rootCollection2 = Collection::find($rootCollection2->id);
        $firstChildOfRoot1 = Collection::find($firstChildOfRoot1->id);
        $secondChildOfRoot1 = Collection::find($secondChildOfRoot1->id);
        $firstChildOfRoot2 = Collection::find($firstChildOfRoot2->id);

        $this->assertEmpty($rootCollection2->root_collection_id);
        $this->assertEquals($rootCollection2->id, $rootCollection1->root_collection_id);
        $this->assertEquals($rootCollection2->id, $secondChildOfRoot1->root_collection_id);
        $this->assertEquals(1, $firstChildOfRoot2->depth);
        $this->assertEquals(2, $rootCollection1->depth);
        $this->assertEquals(3, $firstChildOfRoot1->depth);
        $this->assertEquals(3, $secondChildOfRoot1->depth);
        $this->assertEquals($user2->id, $firstChildOfRoot2->user_id);
        $this->assertEquals($user2->id, $rootCollection1->user_id);
        $this->assertEquals($user2->id, $firstChildOfRoot1->user_id);
        $this->assertEquals($user2->id, $secondChildOfRoot1->user_id);

        $this->assertEquals(1, $rootCollection2->left);
        $this->assertEquals(2, $rootCollection2->right);
        $this->assertEquals(1, $firstChildOfRoot2->left);
        $this->assertEquals(2, $rootCollection1->left);
        $this->assertEquals(3, $firstChildOfRoot1->left);
        $this->assertEquals(4, $firstChildOfRoot1->right);
        $this->assertEquals(5, $secondChildOfRoot1->left);
        $this->assertEquals(6, $secondChildOfRoot1->right);
        $this->assertEquals(7, $rootCollection1->right);
        $this->assertEquals(8, $firstChildOfRoot2->right);

    }

    public function testDeleteRootCollection()
    {
        User::factory()->create();
        $collection1 = Collection::factory()->create();
        $collection2 = Collection::factory()->create();
        $collection3 = Collection::factory()->create();
        $collection4 = Collection::factory()->create();
        $childCollection = Collection::factory([
            'parent_id' => $collection2->id
        ])->create();

        Collection::find($collection2->id)->delete();

        $collection1 = Collection::find($collection1->id);
        $collection3 = Collection::find($collection3->id);
        $collection4 = Collection::find($collection4->id);

        $this->assertTrue(Collection::withTrashed()->find($collection2->id)->trashed());
        $this->assertTrue(Collection::withTrashed()->find($childCollection->id)->trashed());
        $this->assertEquals(1, $collection1->left);
        $this->assertEquals(2, $collection1->right);
        $this->assertEquals(3, $collection3->left);
        $this->assertEquals(4, $collection3->right);
        $this->assertEquals(5, $collection4->left);
        $this->assertEquals(6, $collection4->right);
    }

    public function testDeleteNestedCollection()
    {
        User::factory()->create();
        $rootCollection = Collection::factory()->create();
        $collection1 = Collection::factory([
            'parent_id' => $rootCollection
        ])->create();
        $collection2 = Collection::factory([
            'parent_id' => $rootCollection
        ])->create();
        $collection3 = Collection::factory([
            'parent_id' => $rootCollection
        ])->create();
        $grandChildCollection = Collection::factory([
            'parent_id' => $collection1->id
        ])->create();

        Collection::find($collection1->id)->delete();

        $collection2 = Collection::find($collection2->id);
        $collection3 = Collection::find($collection3->id);

        $this->assertTrue(Collection::withTrashed()->find($collection1->id)->trashed());
        $this->assertTrue(Collection::withTrashed()->find($grandChildCollection->id)->trashed());
        $this->assertEquals(1, Collection::find($rootCollection->id)->left);
        $this->assertEquals(1, $collection2->left);
        $this->assertEquals(2, $collection2->right);
        $this->assertEquals(3, $collection3->left);
        $this->assertEquals(4, $collection3->right);
    }

    public function testDeleteNestedDeeperCollection()
    {
        User::factory()->create();
        $rootCollection = Collection::factory()->create();
        $collection1 = Collection::factory([
            'parent_id' => $rootCollection->id
        ])->create();
        $collection2 = Collection::factory([
            'parent_id' => $rootCollection->id
        ])->create();
        $grandChildCollection = Collection::factory([
            'parent_id' => $collection1->id
        ])->create();
        $grandChildCollection2 = Collection::factory([
            'parent_id' => $collection1->id
        ])->create();
        $greatGrandChildCollection = Collection::factory([
            'parent_id' => $grandChildCollection->id
        ])->create();

        Collection::find($grandChildCollection->id)->delete();

        $collection1 = Collection::find($collection1->id);
        $collection2 = Collection::find($collection2->id);
        $grandChildCollection = Collection::onlyTrashed()->find($grandChildCollection->id);
        $grandChildCollection2 = Collection::find($grandChildCollection2->id);
        $greatGrandChildCollection = Collection::onlyTrashed()->find($greatGrandChildCollection->id);

        $this->assertTrue($grandChildCollection->trashed());
        $this->assertTrue($greatGrandChildCollection->trashed());
        $this->assertEquals(1, $rootCollection->left);
        $this->assertEquals(1, $collection1->left);
        $this->assertEquals(2, $grandChildCollection2->left);
        $this->assertEquals(3, $grandChildCollection2->right);
        $this->assertEquals(4, $collection1->right);
        $this->assertEquals(5, $collection2->left);
        $this->assertEquals(6, $collection2->right);
    }

    public function testRestoreCollection()
    {
        User::factory()->create();
        $collection1 = Collection::factory()->create();
        $collection2 = Collection::factory()->create();
        $collection3 = Collection::factory()->create();
        $collection4 = Collection::factory()->create();

        $collection2->delete();
        $collection2->restore();

        $this->assertFalse(Collection::withTrashed()->find($collection2->id)->trashed());
        $this->assertEquals(1, Collection::find($collection1->id)->left);
        $this->assertEquals(3, Collection::find($collection2->id)->left);
        $this->assertEquals(5, Collection::find($collection3->id)->left);
        $this->assertEquals(7, Collection::find($collection4->id)->left);
    }

    public function testRestoreNestedCollection()
    {
        User::factory()->create();
        $parentCollection = Collection::factory()->create();
        $collection1 = Collection::factory([
            'parent_id' => $parentCollection->id
        ])->create();
        $collection2 = Collection::factory([
            'parent_id' => $parentCollection->id
        ])->create();
        $collection3 = Collection::factory([
            'parent_id' => $parentCollection->id
        ])->create();
        $grandChildCollection = Collection::factory([
            'parent_id' => $collection1->id
        ])->create();

        $collection1 = Collection::find($collection1->id);
        $collection1->delete();
        $this->assertEquals(1, Collection::find($collection2->id)->left);
        $collection1->restore();

        $collection1 = Collection::find($collection1->id);
        $collection2 = Collection::find($collection2->id);
        $collection3 = Collection::find($collection3->id);
        $grandChildCollection = Collection::find($grandChildCollection->id);

        $this->assertFalse($collection1->trashed());
        $this->assertFalse($grandChildCollection->trashed());
        $this->assertEquals(1, Collection::find($parentCollection->id)->left);
        $this->assertEquals(1, $collection1->left);
        $this->assertEquals(2, $grandChildCollection->left);
        $this->assertEquals(3, $grandChildCollection->right);
        $this->assertEquals(4, $collection1->right);
        $this->assertEquals(5, $collection2->left);
        $this->assertEquals(6, $collection2->right);
        $this->assertEquals(7, $collection3->left);
    }

    public function testRestoreNestedCollectionDisregardingOlderDeletes()
    {
        User::factory()->create();
        $parentCollection = Collection::factory()->create();
        $collection1 = Collection::factory([
            'parent_id' => $parentCollection->id
        ])->create();
        $collection2 = Collection::factory([
            'parent_id' => $parentCollection->id
        ])->create();
        $collection3 = Collection::factory([
            'parent_id' => $parentCollection->id
        ])->create();
        $grandChildCollection = Collection::factory([
            'parent_id' => $collection1->id
        ])->create();
        $grandChildCollection2 = Collection::factory([
            'parent_id' => $collection1->id
        ])->create();

        // earlier delete that should stay deleted
        $grandChildCollection->delete();
        $grandChildCollection->deleted_at = '1970-10-10 10:10:10';
        $grandChildCollection->save();

        $collection1 = Collection::find($collection1->id);
        $collection1->delete();
        $collection1->restore();

        $collection1 = Collection::find($collection1->id);
        $collection2 = Collection::find($collection2->id);
        $collection3 = Collection::find($collection3->id);
        $grandChildCollection = Collection::withTrashed()->find($grandChildCollection->id);
        $grandChildCollection2 = Collection::find($grandChildCollection2->id);

        $this->assertFalse($collection1->trashed());
        $this->assertFalse($grandChildCollection2->trashed());
        $this->assertTrue($grandChildCollection->trashed());
        $this->assertEquals(1, Collection::find($parentCollection->id)->left);
        $this->assertEquals(1, $collection1->left);
        $this->assertEquals(2, $grandChildCollection2->left);
        $this->assertEquals(3, $grandChildCollection2->right);
        $this->assertEquals(4, $collection1->right);
        $this->assertEquals(5, $collection2->left);
        $this->assertEquals(6, $collection2->right);
        $this->assertEquals(7, $collection3->left);
    }

    public function testRestoreNestedCollectionWithNewOwner()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $rootCollection = Collection::factory()->create();
        $collection1 = Collection::factory([
            'parent_id' => $rootCollection->id
        ])->create();
        $collection2 = Collection::factory([
            'parent_id' => $rootCollection->id
        ])->create();
        $collection3 = Collection::factory([
            'parent_id' => $rootCollection->id
        ])->create();
        $childCollection = Collection::factory([
            'parent_id' => $collection1->id
        ])->create();
        $grandChildCollection = Collection::factory([
            'parent_id' => $collection1->id
        ])->create();

        $childCollection = Collection::find($childCollection->id);
        $this->assertEquals($user1->id, $childCollection->user_id);

        $childCollection->delete();

        Collection::find($collection1->id)->moveTo(null, 1, $user2->id);

        $this->assertEquals(1, Collection::find($collection2->id)->left);
        Collection::withTrashed()->find($childCollection->id)->restore();

        $rootCollection = Collection::find($rootCollection->id);
        $collection1 = Collection::find($collection1->id);
        $collection2 = Collection::find($collection2->id);
        $collection3 = Collection::find($collection3->id);
        $childCollection = Collection::withTrashed()->find($childCollection->id);
        $grandChildCollection = Collection::withTrashed()->find($grandChildCollection->id);

        $this->assertFalse($childCollection->trashed());
        $this->assertFalse($grandChildCollection->trashed());
        $this->assertEquals($user1->id, $collection2->user_id);
        $this->assertEquals($user1->id, $collection3->user_id);
        $this->assertEquals($user2->id, $collection1->user_id);
        $this->assertEquals($user2->id, $childCollection->user_id);
        $this->assertEquals($user2->id, $grandChildCollection->user_id);

        $this->assertEquals(1, $rootCollection->left);
        $this->assertEquals(1, $collection2->left);
        $this->assertEquals(2, $collection2->right);
        $this->assertEquals(3, $collection3->left);
        $this->assertEquals(4, $collection3->right);

        $this->assertEquals(1, $collection1->left);
        $this->assertEquals(2, $collection1->right);
        $this->assertEquals(1, $childCollection->left);
        $this->assertEquals(2, $childCollection->right);
        $this->assertEquals(3, $grandChildCollection->left);
        $this->assertEquals(4, $grandChildCollection->right);
    }

    public function testGenerateHierarchWithMultipleUsers()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $rootCollection1 = Collection::factory([
            'user_id' => $user1->id
        ])->create();
        $firstChildOfRoot1 = Collection::factory([
            'parent_id' => $rootCollection1->id,
            'user_id' => $user1->id
        ])->create();
        $secondChildOfRoot1 = Collection::factory([
            'parent_id' => $rootCollection1->id,
            'user_id' => $user1->id
        ])->create();
        $thirdChildOfRoot1 = Collection::factory([
            'parent_id' => $rootCollection1->id,
            'user_id' => $user1->id
        ])->create();
        $firstGrandChildOfRoot1 = Collection::factory([
            'parent_id' => $firstChildOfRoot1->id,
            'user_id' => $user1->id
        ])->create();
        $secondGrandChildOfRoot1 = Collection::factory([
            'parent_id' => $firstChildOfRoot1->id,
            'user_id' => $user1->id
        ])->create();
        $rootCollection2 = Collection::factory([
            'user_id' => $user1->id
        ])->create();

        $rootCollection3 = Collection::factory([
            'user_id' => $user2->id
        ])->create();
        $rootCollection4 = Collection::factory([
            'user_id' => $user2->id
        ])->create();
        $firstChildOfRoot4 = Collection::factory([
            'parent_id' => $rootCollection4->id,
            'user_id' => $user2->id
        ])->create();
        $secondChildOfRoot4 = Collection::factory([
            'parent_id' => $rootCollection4->id,
            'user_id' => $user2->id
        ])->create();
        $grandChildOfRoot2 = Collection::factory([
            'parent_id' => $secondChildOfRoot4->id,
            'user_id' => $user2->id
        ])->create();

        Collection::all()->every(function ($collection) {
            $collection->left = 0;
            $collection->right = 0;
            $collection->depth = 0;
            $collection->root_collection_id = null;
            $collection->saveQuietly();
        });

        $this->assertEquals(0, Collection::find($rootCollection1->id)->left);

        Collection::rebuildHierarchy();

        $rootCollection1 = Collection::find($rootCollection1->id);
        $rootCollection2 = Collection::find($rootCollection2->id);
        $rootCollection3 = Collection::find($rootCollection3->id);
        $rootCollection4 = Collection::find($rootCollection4->id);
        $firstChildOfRoot1 = Collection::find($firstChildOfRoot1->id);
        $secondChildOfRoot1 = Collection::find($secondChildOfRoot1->id);
        $thirdChildOfRoot1 = Collection::find($thirdChildOfRoot1->id);
        $firstChildOfRoot4 = Collection::find($firstChildOfRoot4->id);
        $secondChildOfRoot4 = Collection::find($secondChildOfRoot4->id);
        $firstGrandChildOfRoot1 = Collection::find($firstGrandChildOfRoot1->id);
        $secondGrandChildOfRoot1 = Collection::find($secondGrandChildOfRoot1->id);
        $grandChildOfRoot2 = Collection::find($grandChildOfRoot2->id);

        Collection
            ::where('root_collection_id', '<=', $rootCollection2)
            ->orWhere('id', '<=', $rootCollection2)
            ->get()
            ->every(function ($collection) use ($user1) {
            $this->assertEquals($user1->id, $collection->user_id);
        });
        Collection
            ::where('root_collection_id', '>=', $rootCollection3)
            ->orWhere('id', '>=', $rootCollection3)
            ->get()
            ->every(function ($collection) use ($user2) {
                $this->assertEquals($user2->id, $collection->user_id);
            });

        $this->assertEquals(5, $rootCollection1->descendants()->count());
        $this->assertEquals(0, $rootCollection3->descendants()->count());
        $this->assertEquals(3, $rootCollection4->descendants()->count());
        $this->assertEquals(3, $rootCollection1->children()->count());
        $this->assertEquals(2, $rootCollection4->children()->count());

        $this->assertEquals(1, Collection::min('left'));
        $this->assertEquals(2, Collection::min('right'));
        $this->assertEquals(4,
            Collection
                ::where('user_id', $user1->id)
                ->where('parent_id', null)
                ->max('right'));
        $this->assertEquals(4,
            Collection
                ::where('user_id', $user2->id)
                ->where('parent_id', null)
                ->max('right')
        );
        $this->assertEquals(4,
            Collection
                ::where('parent_id', null)
                ->max('right'));

        $this->assertEquals(10,
            Collection
                ::where('user_id', $user1->id)
                ->max('right')
        );
        $this->assertEquals(6,
            Collection
                ::where('user_id', $user2->id)
                ->max('right')
        );

        $this->assertEquals(3,
            Collection
                ::where('user_id', $user1->id)
                ->where('depth', 1)
                ->count()
        );
        $this->assertEquals(2,
            Collection
                ::where('user_id', $user1->id)
                ->where('depth', 2)
                ->count()
        );

        $this->assertEquals(2,
            Collection
                ::where('user_id', $user2->id)
                ->where('depth', 1)
                ->count()
        );
        $this->assertEquals(1,
            Collection
                ::where('user_id', $user2->id)
                ->where('depth', 2)
                ->count()
        );
//        $this->assertEquals(1, $firstChildOfRoot1->left);
//        $this->assertEquals(2, $firstGrandChildOfRoot1->left);
//        $this->assertEquals(3, $firstGrandChildOfRoot1->right);
//        $this->assertEquals(4, $secondGrandChildOfRoot1->left);
//        $this->assertEquals(5, $secondGrandChildOfRoot1->right);
//        $this->assertEquals(6, $firstChildOfRoot1->right);
//        $this->assertEquals(7, $secondChildOfRoot1->left);
//        $this->assertEquals(8, $secondChildOfRoot1->right);
//        $this->assertEquals(9, $thirdChildOfRoot1->left);
//        $this->assertEquals(10, $thirdChildOfRoot1->right);
//
//        $this->assertEquals(1, $firstChildOfRoot4->left);
//        $this->assertEquals(2, $firstChildOfRoot4->right);
//        $this->assertEquals(2, $secondChildOfRoot4->left);
//        $this->assertEquals(3, $grandChildOfRoot2->right);
//        $this->assertEquals(4, $grandChildOfRoot2->left);
//        $this->assertEquals(5, $secondChildOfRoot4->right);

    }

    public function testRelationshipMethods()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $rootCollection1 = Collection::factory()->create();
        $collection1 = Collection::factory([
            'parent_id' => $rootCollection1
        ])->create();
        $grandChildCollection1 = Collection::factory([
            'parent_id' => $collection1->id
        ])->create();
        $greatGrandChildCollection = Collection::factory([
            'parent_id' => $grandChildCollection1->id
        ])->create();
        $grandChildCollection2 = Collection::factory([
            'parent_id' => $collection1->id
        ])->create();
        $collection2 = Collection::factory([
            'parent_id' => $rootCollection1
        ])->create();

        $rootCollection2 = Collection::factory()->create();
        $collection3 = Collection::factory([
            'parent_id' => $rootCollection2
        ])->create();

        $rootCollection3 = Collection::factory([
            'user_id' => $user2->id
        ])->create();
        $collection4 = Collection::factory([
            'user_id' => $user2->id,
            'parent_id' => $rootCollection3
        ])->create();


        $rootCollection1 = Collection::find($rootCollection1->id);
        $rootCollection2 = Collection::find($rootCollection2->id);
        $collection1 = Collection::find($collection1->id);
        $collection2 = Collection::find($collection2->id);
        $collection3 = Collection::find($collection3->id);
        $grandChildCollection1 = Collection::find($grandChildCollection1->id);
        $grandChildCollection2 = Collection::find($grandChildCollection2->id);
        $greatGrandChildCollection = Collection::find($greatGrandChildCollection->id);

        $this->assertEquals(
            0,
            $rootCollection1->parent()->count()
        );
        $this->assertEquals(
            collect([$rootCollection1->id]),
            $collection1->parent()->pluck('id')->flatten()
        );

        $this->assertEquals(
            collect([$rootCollection2->id]),
            $rootCollection1->siblings()->pluck('id')->flatten()
        );
        $this->assertEquals(
            collect([$collection2->id]),
            $collection1->siblings()->pluck('id')->flatten()
        );

        $this->assertEquals(
            [$collection1->id, $collection2->id],
            $rootCollection1->children()->pluck('id')->toArray()
        );
        $this->assertEquals(
            [$grandChildCollection1->id, $grandChildCollection2->id],
            $collection1->children()->pluck('id')->toArray()
        );
        $this->assertEquals(0,
            $greatGrandChildCollection->descendants()->count()
        );

        $this->assertEquals(
            [$collection1->id, $collection2->id, $grandChildCollection1->id, $grandChildCollection2->id, $greatGrandChildCollection->id],
            $rootCollection1
                ->descendants()
                ->orderBy('depth')
                ->pluck('id')
                ->toArray()
        );

        $this->assertEquals(
            [
                $grandChildCollection1->id,
                $grandChildCollection2->id,
                $greatGrandChildCollection->id
            ],
            $collection1
                ->descendants()
                ->orderBy('depth')
                ->pluck('id')
                ->toArray()
        );

        $this->assertEquals(
            [
                $collection1->id,
                $grandChildCollection1->id,
                $grandChildCollection2->id,
                $greatGrandChildCollection->id
            ],
            $collection1
                ->descendantsAndSelf()
                ->orderBy('depth')
                ->pluck('id')
                ->toArray()
        );

        $this->assertEquals(0, $rootCollection1->ancestors()->count());
        $this->assertEquals(1, $rootCollection1->ancestorsAndSelf()->count());

        $this->assertEquals(
            [
                $rootCollection1->id,
                $collection1->id
            ],
            $grandChildCollection1
                ->ancestors()
                ->orderBy('depth')
                ->pluck('id')
                ->toArray()
        );

        $this->assertEquals(
            [
                $rootCollection1->id,
                $collection1->id,
                $grandChildCollection1->id
            ],
            $grandChildCollection1
                ->ancestorsAndSelf()
                ->orderBy('depth')
                ->pluck('id')
                ->toArray()
        );

        $this->assertFalse($rootCollection1->isDescendantOf($collection1));
        $this->assertFalse($collection1->isDescendantOf($rootCollection2));
        $this->assertTrue($collection1->isChildOf($rootCollection1));
        $this->assertTrue($collection1->isDescendantOf($rootCollection1));
        $this->assertTrue($collection2->isDescendantOf($rootCollection1));
        $this->assertTrue($collection3->isDescendantOf($rootCollection2));
        $this->assertFalse($grandChildCollection2->isDescendantOf($rootCollection2));
        $this->assertTrue($grandChildCollection2->isDescendantOf($collection1));
        $this->assertTrue($grandChildCollection2->isDescendantOf($rootCollection1));

    }

}
