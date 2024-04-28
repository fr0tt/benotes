<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use App\Models\Collection;
use App\Models\PrivateShare;

class PrivateShareTest extends TestCase
{

    use RefreshDatabase;

    public function testCreateShare()
    {
        $owner = User::factory()->create();
        $user = User::factory()->create();
        $collection = Collection::factory([
            'user_id' => $owner->id
        ])->create();

        $response = $this->actingAs($owner)->json('POST', 'api/shares/private', [
            'user_id'       => $user->id,
            'collection_id' => $collection->id,
        ]);
        $this->assertEquals(Response::HTTP_CREATED, $response->status());
        $share = $response->getData()->data;
        $this->assertEquals($collection->id, $share->root_collection_id);
        $this->assertEquals($user->id, $share->user->id);
        $this->assertEquals($owner->id, $share->created_by);

        // try to create it a second time
        $response = $this->actingAs($owner)->json('POST', 'api/shares/private', [
            'user_id'       => $user->id,
            'collection_id' => $collection->id,
        ]);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->status());
    }

    public function testCreateNestedShare()
    {

        $owner = User::factory()->create();
        $user = User::factory()->create();
        $collectionByOwner = Collection::factory([
            'user_id' => $owner->id
        ])->create();
        $collectionByUser = Collection::factory([
            'user_id' => $user->id
        ])->create();

        $response = $this->actingAs($owner)->json('POST', 'api/shares/private', [
            'user_id'       => $owner->id,
            'collection_id' => $collectionByOwner->id,
        ]);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->status());

        $response = $this->actingAs($owner)->json('POST', 'api/shares/private', [
            'user_id'       => $user->id,
            'collection_id' => $collectionByUser->id,
        ]);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->status());

        $response = $this->actingAs(User::factory()->create())
            ->json('POST', 'api/shares/private', [
                'user_id'       => $user->id,
                'collection_id' => $collectionByOwner->id,
            ]);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->status());

        $response = $this->actingAs($owner)->json('POST', 'api/shares/private', [
            'user_id'       => $user->id,
            'collection_id' => $collectionByOwner->id,
        ]);
        $this->assertEquals(Response::HTTP_CREATED, $response->status());
        $share = $response->getData()->data;
        $this->assertEquals($collectionByOwner->id, $share->root_collection_id);
        $this->assertEquals($user->id, $share->user->id);
        $this->assertEquals($owner->id, $share->created_by);
        $collectionByOwner = Collection::find($collectionByOwner->id);
        $this->assertTrue($collectionByOwner->is_being_shared);

    }

    public function testAccessShare()
    {
        $owner = User::factory()->create();
        $user = User::factory()->create();
        $foreignUser = User::factory()->create();
        $collection = Collection::factory([
            'user_id' => $owner->id
        ])->create();

        $response = $this->actingAs($owner)->json('GET', 'api/shares/private/', [
            'collection_id' => $collection->id,
        ]);
        $this->assertEquals(Response::HTTP_OK, $response->status());
        $this->assertEquals(count([]), count($response->getData()->data));

        $response = $this->actingAs($owner)->json('POST', 'api/shares/private', [
            'user_id'       => $user->id,
            'collection_id' => $collection->id,
        ]);
        $this->assertEquals(201, $response->status());
        $share = $response->getData()->data;

        $response = $this->actingAs($foreignUser)->json('GET', 'api/shares/private/', [
            'collection_id' => $collection->id,
        ]);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->status());

        $response = $this->actingAs($user)->json('GET', 'api/shares/private/', [
            'collection_id' => $collection->id,
        ]);
        $this->assertEquals(Response::HTTP_OK, $response->status());
        $this->assertEquals(1, count($response->getData()->data));

        $response = $this->actingAs($user)->json('GET', 'api/shares/private/' . $share->id);
        $this->assertEquals(Response::HTTP_OK, $response->status());
        $share = $response->getData()->data;
        $this->assertEquals($collection->id, $share->root_collection_id);
        $this->assertEquals($user->id, $share->user->id);
        $this->assertEquals($owner->id, $share->created_by);

        $response = $this->actingAs($owner)->json('GET', 'api/shares/private/' . $share->id);
        $this->assertEquals(Response::HTTP_OK, $response->status());
        $share = $response->getData()->data;
        $this->assertEquals($collection->id, $share->root_collection_id);
        $this->assertEquals($user->id, $share->user->id);
        $this->assertEquals($owner->id, $share->created_by);

        $response = $this->actingAs($owner)->json('GET', 'api/shares/private/', [
            'collection_id' => $collection->id,
        ]);
        $this->assertEquals(Response::HTTP_OK, $response->status());
        $share = $response->getData()->data[0];
        $this->assertEquals($collection->id, $share->root_collection_id);
        $this->assertEquals($user->id, $share->user->id);
        $this->assertEquals($owner->id, $share->created_by);

        $response = $this->actingAs($user)->json('GET', 'api/shares/private');
        $shares = $response->getData()->data;
        $this->assertEquals(1, count($shares));

    }

    private function setupNestedShare(): object
    {
        $owner = User::factory()->create();
        $user = User::factory()->create();
        $foreignUser = User::factory()->create();
        $collection = Collection::factory([
            'user_id' => $owner->id
        ])->create();
        $childCollection = Collection::factory([
            'parent_id' => $collection->id,
            'user_id'   => $owner->id
        ])->create();
        $grandChildCollection = Collection::factory([
            'parent_id' => $childCollection->id,
            'user_id'   => $owner->id
        ])->create();

        $share = $this->makeShare($childCollection, $user, $owner);

        return (object) [
            'owner'                => $owner,
            'user'                 => $user,
            'collection'           => $collection,
            'childCollection'      => $childCollection,
            'grandChildCollection' => $grandChildCollection,
            'share'                => $share,
            'foreignUser'          => $foreignUser
        ];
    }

    public function testAccessNestedShare()
    {
        $s = $this->setupNestedShare();

        $response = $this->actingAs($s->user)->json('GET', 'api/shares/private/', [
            'collection_id' => $s->collection->id
        ]);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->status());

        $response = $this->actingAs($s->foreignUser)->json('GET', 'api/shares/private/', [
            'collection_id' => $s->childCollection->id
        ]);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->status());

        $response = $this->actingAs($s->user)->json('GET', 'api/shares/private/', [
            'collection_id' => $s->childCollection->id
        ]);
        $this->assertEquals(200, $response->status());

        $response = $this->actingAs($s->user)->json('GET', 'api/shares/private/', [
            'collection_id' => $s->grandChildCollection->id
        ]);
        $this->assertEquals(200, $response->status());
        $share = $response->getData()->data[0];
        $this->assertEquals($s->childCollection->id, $share->root_collection_id);
        $this->assertEquals($s->user->id, $share->user->id);
        $this->assertEquals($s->owner->id, $share->created_by);
    }

    public function testGetCollectionInNestedShare()
    {
        $s = $this->setupNestedShare();

        $response = $this->actingAs($s->user)->json(
            'GET',
            'api/collections/' .
            $s->collection->id
        );
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->status());

        $response = $this->actingAs($s->foreignUser)->json(
            'GET',
            'api/collections/' .
            $s->childCollection->id
        );
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->status());

        $response = $this->actingAs($s->user)->json(
            'GET',
            'api/collections/' .
            $s->grandChildCollection->id
        );
        $this->assertEquals(Response::HTTP_OK, $response->status());
        $collection = $response->getData()->data;
        $this->assertEquals($s->grandChildCollection->id, $collection->id);
        //dd(Collection::find($s->grandChildCollection->id)->is_being_shared);
        $this->assertTrue($collection->is_being_shared); // @TODO fails

        $response = $this->actingAs($s->user)->json(
            'GET',
            'api/collections/' .
            $s->childCollection->id
        );
        $this->assertEquals(Response::HTTP_OK, $response->status());
        $collection = $response->getData()->data;
        $this->assertEquals($s->childCollection->id, $collection->id);
        $this->assertTrue($collection->is_being_shared);

        $response = $this->actingAs($s->owner)->json(
            'GET',
            'api/collections/' .
            $s->childCollection->id
        );
        $this->assertEquals(Response::HTTP_OK, $response->status());

    }

    public function testGetCollectionsInNestedShare()
    {
        $s = $this->setupNestedShare();

        $response = $this->actingAs($s->user)->json('GET', 'api/collections?withShared=1');
        $this->assertEquals(Response::HTTP_OK, $response->status());
        $collections = $response->getData()->data->shared_collections;
        $this->assertEquals(2, count($collections));
        $this->assertEquals($s->childCollection->id, $collections[0]->id);
        $this->assertEquals($s->grandChildCollection->id, $collections[1]->id);
        $this->assertTrue($collections[0]->is_being_shared);
        $this->assertTrue($collections[1]->is_being_shared);


        $response = $this->actingAs($s->foreignUser)->json('GET', 'api/collections');
        $collections = $response->getData()->data;
        $this->assertEquals(0, count($collections));

    }

    public function testGetCollectionsInNestedShareWithDedicatedEndpoint()
    {
        $s = $this->setupNestedShare();

        $response = $this->actingAs($s->user)->json('GET', 'api/collections/shared');
        $this->assertEquals(Response::HTTP_OK, $response->status());
        $collections = $response->getData()->data;
        $this->assertEquals(2, count($collections));
        $this->assertEquals($s->childCollection->id, $collections[0]->id);
        $this->assertEquals($s->grandChildCollection->id, $collections[1]->id);
        $this->assertTrue($collections[0]->is_being_shared);
        $this->assertTrue($collections[1]->is_being_shared);
    }

    public function testGetNestedCollectionsInNestedShareWithDedicatedEndpoint()
    {
        $s = $this->setupNestedShare();

        $response = $this->actingAs($s->user)->json('GET', 'api/collections/shared?nested=1');
        $this->assertEquals(Response::HTTP_OK, $response->status());
        $nestedCollections = $response->getData()->data;
        $this->assertCount(1, $nestedCollections);
        $this->assertEquals($s->childCollection->id, $nestedCollections[0]->id);
        $this->assertEquals($s->grandChildCollection->id, $nestedCollections[0]->nested[0]->id);

    }

    public function testCreateCollectionInNestedShare()
    {
        $s = $this->setupNestedShare();

        $response = $this->actingAs($s->user)->json('POST', 'api/collections', [
            'name'      => 'MyAwesomeCollection',
            'parent_id' => $s->collection->id
        ]);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->status());

        $response = $this->actingAs($s->foreignUser)->json('POST', 'api/collections', [
            'name'      => 'MyAwesomeCollection',
            'parent_id' => $s->childCollection->id
        ]);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->status());

        $response = $this->actingAs($s->user)->json('POST', 'api/collections', [
            'name'      => 'MyAwesomeCollection',
            'parent_id' => $s->childCollection->id
        ]);

        $this->assertEquals(Response::HTTP_CREATED, $response->status());
        $collection = $response->getData()->data;
        $this->assertEquals($s->childCollection->id, $collection->parent_id);
        $this->assertTrue($collection->is_being_shared);
        $this->collectionBelongsToOwner($collection, $s->owner);

        $response = $this->actingAs($s->user)->json('POST', 'api/collections', [
            'name'      => 'MyAwesomeCollection',
            'parent_id' => $s->grandChildCollection->id
        ]);
        $this->assertEquals(Response::HTTP_CREATED, $response->status());
        $collection = $response->getData()->data;
        $this->assertEquals($s->grandChildCollection->id, $collection->parent_id);
        $this->assertTrue($collection->is_being_shared);
        $this->collectionBelongsToOwner($collection, $s->owner);

        $response = $this->actingAs($s->user)->json('GET', 'api/collections?withShared=1');
        $this->assertEquals(Response::HTTP_OK, $response->status());
        $collections = $response->getData()->data->shared_collections;
        $this->assertEquals(2 + 2, count($collections));
        $this->assertEquals($s->childCollection->id, $collections[0]->id);
        $this->assertEquals($s->grandChildCollection->id, $collections[1]->id);

        $response = $this->actingAs($s->owner)->json('POST', 'api/collections', [
            'name'      => 'MyAwesomeCollection',
            'parent_id' => $s->childCollection->id
        ]);
        $this->assertEquals(Response::HTTP_CREATED, $response->status());
        $collection = $response->getData()->data;
        $this->assertTrue($collection->is_being_shared);

    }

    public function testUpdateCollectionNameInNestedShare()
    {
        $s = $this->setupNestedShare();

        $response = $this->actingAs($s->user)->json(
            'PATCH',
            'api/collections/' . $s->collection->id,
            [
                'name' => 'MyCollection',
            ]
        );
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->status());

        $response = $this->actingAs($s->foreignUser)->json(
            'PATCH',
            'api/collections/' . $s->collection->id,
            [
                'name' => 'MyCollection',
            ]
        );
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->status());

        $response = $this->actingAs($s->owner)->json(
            'PATCH',
            'api/collections/' . $s->collection->id,
            [
                'name' => 'MyCollection',
            ]
        );

        $this->assertEquals(Response::HTTP_OK, $response->status());
        $collection = $response->getData()->data;
        $this->assertEquals('MyCollection', $collection->name);
        $this->assertFalse($collection->is_being_shared);
    }

    public function testUpdateCollectionParentIdInNestedShare()
    {
        $s = $this->setupNestedShare();

        $response = $this->actingAs($s->owner)->json(
            'POST',
            'api/collections',
            [
                'name'      => 'MyCollection',
                'parent_id' => $s->childCollection->id,
            ]
        );
        $this->assertEquals(Response::HTTP_CREATED, $response->status());
        $collection = $response->getData()->data;
        $this->assertTrue($collection->is_being_shared);
        $this->assertTrue(Collection::find($s->childCollection->id)->is_being_shared);
        $this->assertTrue(Collection::find($s->grandChildCollection->id)->is_being_shared);

        $response = $this->actingAs($s->user)->json(
            'PATCH',
            'api/collections/' . $collection->id,
            [
                'parent_id' => $s->grandChildCollection->id,
            ]
        );
        $this->assertEquals(Response::HTTP_OK, $response->status());
        $collection = $response->getData()->data;
        $this->assertEquals($s->grandChildCollection->id, $collection->parent_id);
        $this->assertTrue($collection->is_being_shared);
        $this->collectionBelongsToOwner($collection, $s->owner);

        $response = $this->actingAs($s->owner)->json(
            'PATCH',
            'api/collections/' . $collection->id,
            [
                'parent_id' => $s->childCollection->id,
            ]
        );
        $this->assertEquals(Response::HTTP_OK, $response->status());
        $collection = $response->getData()->data;
        $this->assertEquals($s->childCollection->id, $collection->parent_id);
        $this->assertTrue($collection->is_being_shared);
        $this->collectionBelongsToOwner($collection, $s->owner);
    }

    public function testTransferNestedCollectionToNestedShare()
    {
        $s = $this->setupNestedShare();

        $collection = Collection::factory([
            'parent_id' => null,
            'user_id'   => $s->user->id
        ])->create();

        $collection2 = Collection::factory([
            'parent_id' => $collection->id,
            'user_id'   => $s->user->id
        ])->create();

        $response = $this->actingAs($s->user)->json(
            'PATCH', 'api/collections/' . $collection->id, [
                'parent_id' => $s->collection->id,
            ]
        );
        // $s->collection is not part of the share
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->status());

        $response = $this->actingAs($s->user)->json(
            'PATCH',
            'api/collections/' . $collection->id,
            [
                'parent_id' => $s->childCollection->id,
            ]
        );

        $this->assertEquals(Response::HTTP_OK, $response->status());
        $collection = $response->getData()->data;
        $collection2 = Collection::find($collection2->id);
        $this->assertEquals($s->childCollection->id, $collection->parent_id);
        $this->assertEquals($collection->id, $collection2->parent_id);
        $this->assertTrue($collection->is_being_shared);
        $this->assertTrue($collection2->is_being_shared);
        $this->collectionBelongsToOwner($collection, $s->owner);
        $this->collectionBelongsToOwner($collection2, $s->owner);
    }

    public function testTransferCollectionFromNestedShare()
    {
        $s = $this->setupNestedShare();

        $post = Post::factory([
            'collection_id' => $s->grandChildCollection->id,
            'user_id'       => $s->owner->id
        ])->create();

        $response = $this->actingAs($s->user)->json(
            'PATCH',
            'api/collections/' . $s->childCollection->id,
            [
                'parent_id' => $s->collection->id,
            ]
        );
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->status());

        $response = $this->actingAs($s->user)->json(
            'PATCH',
            'api/collections/' . $s->childCollection->id,
            [
                'is_root' => true,
            ]
        );
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->status());

        $newCollection = Collection::factory()->create([
            'parent_id' => null,
            'user_id'   => $s->user->id
        ]);

        $response = $this->actingAs($s->user)->json(
            'PATCH',
            'api/collections/' . $s->grandChildCollection->id,
            [
                'parent_id' => $newCollection->id,
            ]
        );
        $this->assertEquals(Response::HTTP_OK, $response->status());
        $collection = $response->getData()->data;
        $this->assertEquals($newCollection->id, $collection->parent_id);
        $this->assertFalse($collection->is_being_shared);
        $this->collectionBelongsToOwner($collection, $s->user);
        $post = Post::find($post->id);
        $this->postBelongsToOwner($post, $s->user);
    }

    public function testTransferNestedCollectionFromNestedShare()
    {
        $s = $this->setupNestedShare();

        $postOfGrandChild = Post::factory([
            'collection_id' => $s->grandChildCollection->id,
            'user_id'       => $s->owner->id
        ])->create();
        $post2OfGrandChild = Post::factory([
            'collection_id' => $s->grandChildCollection->id,
            'user_id'       => $s->owner->id
        ])->create();

        $newCollection = Collection::factory()->create([
            'parent_id' => null,
            'user_id'   => $s->user->id
        ]);

        $response = $this->actingAs($s->user)->json(
            'PATCH',
            'api/collections/' . $s->childCollection->id,
            [
                'parent_id' => $newCollection->id,
            ]
        );
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->status());

        $response = $this->actingAs($s->user)->json(
            'PATCH',
            'api/collections/' . $s->grandChildCollection->id,
            [
                'parent_id' => $newCollection->id,
            ]
        );

        $this->assertEquals(Response::HTTP_OK, $response->status());
        $grandChildCollection = Collection::find($s->grandChildCollection->id);
        $postOfGrandChild = Post::find($postOfGrandChild->id);
        $post2OfGrandChild = Post::find($post2OfGrandChild->id);
        $collection = $response->getData()->data;
        $this->assertEquals($newCollection->id, $collection->parent_id);
        $this->collectionBelongsToOwner($collection, $s->user);
        $this->collectionBelongsToOwner($grandChildCollection, $s->user);
        $this->postBelongsToOwner($postOfGrandChild, $s->user);
        $this->postBelongsToOwner($post2OfGrandChild, $s->user);
        $this->assertFalse($collection->is_being_shared);
        $this->assertFalse($grandChildCollection->is_being_shared);
        $this->assertFalse($newCollection->is_being_shared);
    }

    public function testTransferNestedCollectionFromNestedShareToRoot()
    {
        $s = $this->setupNestedShare();

        $postOfGrandChild = Post::factory([
            'collection_id' => $s->grandChildCollection->id,
            'user_id'       => $s->owner->id
        ])->create();
        $post2OfGrandChild = Post::factory([
            'collection_id' => $s->grandChildCollection->id,
            'user_id'       => $s->owner->id
        ])->create();

        $newCollection = Collection::factory()->create([
            'parent_id' => null,
            'user_id'   => $s->user->id
        ]);

        $response = $this->actingAs($s->user)->json('PATCH',
        'api/collections/' . $s->childCollection->id, [
                'is_root' => true,
        ]);
        // root of a share can not be moved
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->status());

        $response = $this->actingAs($s->user)->json(
            'PATCH', 'api/collections/' . $s->grandChildCollection->id, [
                'is_root' => true,
        ]);

        $this->assertEquals(Response::HTTP_OK, $response->status());
        $grandChildCollection = Collection::find($s->grandChildCollection->id);
        $postOfGrandChild = Post::find($postOfGrandChild->id);
        $post2OfGrandChild = Post::find($post2OfGrandChild->id);
        $collection = $response->getData()->data;
        $this->assertEmpty($collection->parent_id);
        $this->collectionBelongsToOwner($collection, $s->user);
        $this->collectionBelongsToOwner($grandChildCollection, $s->user);
        $this->postBelongsToOwner($postOfGrandChild, $s->user);
        $this->postBelongsToOwner($post2OfGrandChild, $s->user);
        $this->assertFalse($collection->is_being_shared);
        $this->assertFalse($grandChildCollection->is_being_shared);
        $this->assertFalse($newCollection->is_being_shared);

    }

    public function testTransferNestedCollectionToAnotherShare()
    {
        $owner = User::factory()->create();
        $user = User::factory()->create();
        $collection1 = Collection::factory([
            'user_id' => $owner->id
        ])->create();
        $childCollection1 = Collection::factory([
            'user_id'   => $owner->id,
            'parent_id' => $collection1->id
        ])->create();
        $grandChildCollection1 = Collection::factory([
            'user_id'   => $owner->id,
            'parent_id' => $childCollection1->id
        ])->create();
        $grandChildCollection2 = Collection::factory([
            'user_id'   => $owner->id,
            'parent_id' => $childCollection1->id
        ])->create();
        $collection2 = Collection::factory([
            'user_id' => $owner->id
        ])->create();
        $childCollection2 = Collection::factory([
            'user_id'   => $owner->id,
            'parent_id' => $collection2->id
        ])->create();
        $grandChildCollection3 = Collection::factory([
            'user_id'   => $owner->id,
            'parent_id' => $childCollection2->id
        ])->create();
        $grandChildCollection4 = Collection::factory([
            'user_id'   => $owner->id,
            'parent_id' => $childCollection2->id
        ])->create();
        $share1 = $this->makeShare($collection1, $user, $owner);
        $share2 = $this->makeShare($collection2, $user, $owner);

        $response = $this->actingAs($user)->json(
            'PATCH',
            'api/collections/' . $childCollection1->id,
            ['parent_id' => $childCollection2->id]
        );

        $this->assertEquals(Response::HTTP_OK, $response->status());
        $collection1 = Collection::find($collection1->id);
        $collection2 = Collection::find($collection2->id);
        $childCollection1 = Collection::find($childCollection1->id);
        $grandChildCollection1 = Collection::find($grandChildCollection1->id);
        $grandChildCollection2 = Collection::find($grandChildCollection2->id);
        $childCollection2 = Collection::find($childCollection2->id);
        $grandChildCollection3 = Collection::find($grandChildCollection3->id);
        $grandChildCollection4 = Collection::find($grandChildCollection4->id);

        $this->assertEquals($childCollection2->id, $childCollection1->parent_id);
        $this->assertEquals($collection2->id, $childCollection1->root_collection_id);
        $this->assertEquals($collection2->id, $grandChildCollection1->root_collection_id);
        $this->assertEquals($collection2->id, $grandChildCollection2->root_collection_id);
        $this->assertEquals([
            $grandChildCollection3->id, $grandChildCollection4->id, $childCollection1->id
        ], $childCollection2->children()->orderBy('left')->pluck('id')->toArray());
        $this->assertEmpty($collection1->children()->get());
        $this->assertEmpty($collection1->descendants()->get());

        $this->collectionBelongsToOwner($childCollection1, $owner);
        $this->collectionBelongsToOwner($grandChildCollection2, $owner);
        $this->assertTrue($collection1->is_being_shared);
        $this->assertTrue($collection2->is_being_shared);
        $this->assertTrue($childCollection1->is_being_shared);
        $this->assertTrue($childCollection2->is_being_shared);
        $this->assertTrue($grandChildCollection1->is_being_shared);
        $this->assertTrue($grandChildCollection3->is_being_shared);
    }

    public function testTransferingNestedCollectionShareInShare()
    {
        $owner = User::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $parentCollection = Collection::factory([
            'user_id' => $owner->id
        ])->create();
        $childCollection = Collection::factory([
            'user_id'   => $owner->id,
            'parent_id' => $parentCollection->id
        ])->create();
        $grandChildCollection = Collection::factory([
            'user_id'   => $owner->id,
            'parent_id' => $childCollection->id
        ])->create();

        $share1 = $this->makeShare($parentCollection, $user1, $owner);
        $share2 = $this->makeShare($grandChildCollection, $user2, $owner);

        $response = $this->actingAs($user1)->json(
            'PATCH',
            'api/collections/' . $childCollection->id,
            ['is_root' => true]
        );

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->status());

        $response = $this->actingAs($owner)->json(
            'PATCH',
            'api/collections/' . $childCollection->id,
            ['is_root' => true]
        );

        $this->assertEquals(Response::HTTP_OK, $response->status());

    }

    public function testDeleteCollectionInNestedShare()
    {
        $s = $this->setupNestedShare();

        $collection = Collection::factory()->create([
            'parent_id' => $s->grandChildCollection->id,
            'user_id' => $s->owner->id
        ]);

        $response = $this->actingAs($s->user)->json(
            'DELETE',
            'api/collections/' . $s->collection->id
        );
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->status());

        $response = $this->actingAs($s->foreignUser)->json(
            'DELETE',
            'api/collections/' . $s->grandChildCollection->id
        );
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->status());

        $response = $this->actingAs($s->user)->json(
            'DELETE',
            'api/collections/' . $s->grandChildCollection->id
        );
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->status());
        $this->assertEmpty(Collection::find($collection->id));
    }

    public function testGetPostInNestedShare()
    {
        $s = $this->setupNestedShare();

        $post = Post::factory([
            'collection_id' => $s->collection->id
        ])->create();
        $postOfChildCollection = Post::factory([
            'collection_id' => $s->childCollection->id
        ])->create();

        $response = $this->actingAs($s->user)->json('GET', 'api/posts/' . $post->id);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->status());

        $response = $this->actingAs($s->foreignUser)->json('GET', 'api/posts/' . $post->id);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->status());

        $response = $this->actingAs($s->user)->json('GET', 'api/posts/' . $postOfChildCollection->id);
        $this->assertEquals(Response::HTTP_OK, $response->status());

        $response = $this->actingAs($s->owner)->json('GET', 'api/posts/' . $postOfChildCollection->id);
        $this->assertEquals(Response::HTTP_OK, $response->status());
    }

    public function testGetPostsInNestedShare()
    {

        $s = $this->setupNestedShare();
        $post = Post::factory([
            'collection_id' => $s->collection->id
        ])->create();

        $response = $this->actingAs($s->user)->json('GET', 'api/posts', [
            'collection_id' => $s->collection->id
        ]);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->status());

        $response = $this->actingAs($s->user)->json('GET', 'api/posts', [
            'collection_id' => $s->childCollection->id
        ]);
        $this->assertEquals(Response::HTTP_OK, $response->status());
        $posts = $response->getData()->data;
        $this->assertCount(0, $posts);

        $response = $this->actingAs($s->user)->json('GET', 'api/posts', [
            'collection_id' => $s->grandChildCollection->id
        ]);
        $this->assertEquals(Response::HTTP_OK, $response->status());

        $response = $this->actingAs($s->owner)->json('GET', 'api/posts');
        $this->assertEquals(Response::HTTP_OK, $response->status());
        $posts = $response->getData()->data;
        $this->assertCount(1, $posts);

        $response = $this->actingAs($s->foreignUser)->json('GET', 'api/posts');
        $this->assertEquals(Response::HTTP_OK, $response->status());
        $posts = $response->getData()->data;
        $this->assertCount(0, $posts);

    }

    public function testCreatePostInNestedShare()
    {

        $s = $this->setupNestedShare();

        $response = $this->actingAs($s->user)->json('POST', 'api/posts', [
            'content'       => 'https://github.com',
            'collection_id' => $s->collection->id
        ]);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->status());

        $response = $this->actingAs($s->foreignUser)->json('POST', 'api/posts', [
            'content'       => 'https://github.com',
            'collection_id' => $s->childCollection->id
        ]);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->status());

        $response = $this->actingAs($s->user)->json('POST', 'api/posts', [
            'content'       => 'https://github.com',
            'collection_id' => $s->childCollection->id
        ]);
        $this->assertEquals(Response::HTTP_CREATED, $response->status());
        $post = $response->getData()->data;
        $this->assertEquals($s->childCollection->id, $post->collection_id);
        $this->assertEquals($s->owner->id, $post->user_id);

        $response = $this->actingAs($s->user)->json('POST', 'api/posts', [
            'content'       => 'https://github.com',
            'collection_id' => $s->grandChildCollection->id
        ]);
        $this->assertEquals(Response::HTTP_CREATED, $response->status());
        $post = $response->getData()->data;
        $this->assertEquals($s->grandChildCollection->id, $post->collection_id);
        $this->assertEquals($s->owner->id, $post->user_id);

        $response = $this->actingAs($s->user)->json('GET', 'api/posts');
        $this->assertEquals(Response::HTTP_OK, $response->status());
        $posts = $response->getData()->data;
        $this->assertEquals(0, count($posts));

        $response = $this->actingAs($s->owner)->json('GET', 'api/posts');
        $this->assertEquals(Response::HTTP_OK, $response->status());
        $posts = $response->getData()->data;
        $this->assertEquals(2, count($posts));

        $response = $this->actingAs($s->foreignUser)->json('GET', 'api/posts');
        $this->assertEquals(Response::HTTP_OK, $response->status());
        $posts = $response->getData()->data;
        $this->assertEquals(0, count($posts));

    }

    public function testUpdatePostInNestedShare()
    {
        $s = $this->setupNestedShare();

        $post = Post::factory()->create([
            'content'       => 'this is some content',
            'collection_id' => $s->childCollection->id,
            'user_id'       => $s->owner->id
        ]);
/*
        $response = $this->actingAs($s->user)->json(
            'PATCH',
            'api/posts/' . $post->id,
            [
                'collection_id' => $s->collection->id
            ]
        );
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->status());

        $response = $this->actingAs($s->user)->json(
            'PATCH',
            'api/posts/' . $post->id,
            [
                'content'       => 'https://github.com',
                'collection_id' => $s->collection->id
            ]
        );
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->status());
*/
        $response = $this->actingAs($s->user)->json(
            'PATCH',
            'api/posts/' . $post->id,
            [
                'content'       => 'https://laravel.com',
                'collection_id' => $s->grandChildCollection->id
            ]
        );
        $this->assertEquals(Response::HTTP_OK, $response->status());
        $post = $response->getData()->data;
        $this->assertEquals($s->grandChildCollection->id, $post->collection_id);
        $this->assertEquals('https://laravel.com', $post->content);
        $this->postBelongsToOwner($post, $s->owner);

    }

    public function testTransferPostToNestedShare()
    {
        $s = $this->setupNestedShare();

        $collection = Collection::factory()->create([
            'user_id' => $s->foreignUser->id
        ]);
        $post = Post::factory()->create([
            'content'       => 'this is some content',
            'collection_id' => $collection->id,
            'user_id'       => $s->foreignUser->id
        ]);

        $response = $this->actingAs($s->foreignUser)->json(
            'PATCH', 'api/posts/' . $post->id, [
            'collection_id' => $s->collection->id
        ]);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->status());

        $response = $this->actingAs($s->foreignUser)->json(
            'PATCH', 'api/posts/' . $post->id, [
                'collection_id' => $s->childCollection->id
        ]);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->status());

        $collection = Collection::factory()->create([
            'user_id' => $s->user->id
        ]);
        $post = Post::factory()->create([
            'content'       => 'this is some content',
            'collection_id' => $collection->id,
            'user_id'       => $s->user->id
        ]);
        $response = $this->actingAs($s->user)->json(
            'PATCH', 'api/posts/' . $post->id, [
            'collection_id' => $s->childCollection->id
        ]);
        $this->assertEquals(Response::HTTP_OK, $response->status());
        $post = $response->getData()->data;
        $this->assertEquals($s->childCollection->id, $post->collection_id);
        // ownership has to change as well
        $this->postBelongsToOwner($post, $s->owner);

        $post = Post::factory()->create([
            'content'       => 'this is some content',
            'collection_id' => null,
            'user_id'       => $s->user->id
        ]);
        $response = $this->actingAs($s->user)->json('PATCH', 'api/posts/' . $post->id, [
            'collection_id' => $s->grandChildCollection->id
        ]);
        $this->assertEquals(Response::HTTP_OK, $response->status());
        $post = $response->getData()->data;
        $this->assertEquals($s->grandChildCollection->id, $post->collection_id);
        $this->postBelongsToOwner($post, $s->owner);
    }

    public function testTransferPostFromNestedShare()
    {
        $s = $this->setupNestedShare();
/*
        $post = Post::factory()->create([
            'content'       => 'this is some content',
            'collection_id' => $s->collection->id,
            'user_id'       => $s->owner->id
        ]);

        $response = $this->actingAs($s->user)->json('PATCH', 'api/posts/' . $post->id, [
            'collection_id' => $s->childCollection->id
        ]);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->status());

        $post = Post::factory()->create([
            'content'       => 'this is some content',
            'collection_id' => $s->childCollection->id,
            'user_id'       => $s->owner->id
        ]);
        $collection = Collection::factory()->create([
            'user_id' => $s->user->id
        ]);

        $response = $this->actingAs($s->user)->json('PATCH', 'api/posts/' . $post->id, [
            'collection_id' => $collection->id
        ]);
        $this->assertEquals(Response::HTTP_OK, $response->status());
        $post = $response->getData()->data;
        $this->assertEquals($collection->id, $post->collection_id);
        $this->postBelongsToOwner($post, $s->user);
*/
        $post = Post::factory()->create([
            'content'       => 'this is some content',
            'collection_id' => $s->grandChildCollection->id,
            'user_id'       => $s->owner->id
        ]);
        $response = $this->actingAs($s->user)->json('PATCH', 'api/posts/' . $post->id, [
            'is_uncategorized' => true
        ]);
        $this->assertEquals(Response::HTTP_OK, $response->status());
        $post = $response->getData()->data;
        $this->assertEmpty($post->collection_id);
        $this->postBelongsToOwner($post, $s->user);
    }

    public function testDeletePostInNestedShare()
    {
        $s = $this->setupNestedShare();

        $post = Post::factory([
            'collection_id' => $s->collection->id,
            'user_id' => $s->owner->id
        ])->create();
        $postOfChildCollection = Post::factory([
            'collection_id' => $s->childCollection->id
        ])->create();

        $response = $this->actingAs($s->user)->json('DELETE', 'api/posts/' . $post->id);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->status());

        $response = $this->actingAs($s->foreignUser)->json('DELETE', 'api/posts/' . $post->id);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->status());

        $response = $this->actingAs($s->user)->json('DELETE', 'api/posts/' . $postOfChildCollection->id);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->status());

        $postOfChildCollection = Post::factory([
            'collection_id' => $s->childCollection->id
        ])->create();
        $response = $this->actingAs($s->owner)->json('DELETE', 'api/posts/' . $postOfChildCollection->id);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->status());
    }

    public function testEditNestedShare()
    {
        $s = $this->setupNestedShare();

        $response = $this->actingAs($s->user)->json(
            'PATCH',
            'api/shares/private/' . $s->share->id,
            [
                'user_id' => $s->foreignUser->id
            ]
        );
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->status());

        $response = $this->actingAs($s->user)->json(
            'PATCH',
            'api/shares/private/' . $s->share->id,
            [
                'collection_id' => $s->collection->id
            ]
        );
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->status());

        $response = $this->actingAs($s->owner)->json(
            'PATCH',
            'api/shares/private/' . $s->share->id,
            [
                'collection_id' => $s->collection->id
            ]
        );
        $this->assertEquals(Response::HTTP_OK, $response->status());
        $share = $response->getData()->data;
        $this->assertEquals($s->user->id, $share->user_id);
        $this->assertEquals($s->owner->id, $share->created_by);
        $this->assertEquals($s->collection->id, $share->collection_id);

        $response = $this->actingAs($s->user)->json('GET', 'api/posts/', [
            'collection_id' => $s->collection->id
        ]);
        $this->assertEquals(Response::HTTP_OK, $response->status());

        $response = $this->actingAs($s->owner)->json(
            'PATCH',
            'api/shares/private/' . $s->share->id,
            [
                'user_id' => $s->foreignUser->id
            ]
        );
        $this->assertEquals(Response::HTTP_OK, $response->status());
        $share = $response->getData()->data;
        $this->assertEquals($s->foreignUser->id, $share->user_id);

        $response = $this->actingAs($s->user)->json('GET', 'api/posts/', [
            'collection_id' => $s->childCollection->id
        ]);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->status());

        $response = $this->actingAs($s->foreignUser)->json('GET', 'api/posts/', [
            'collection_id' => $s->childCollection->id
        ]);
        $this->assertEquals(Response::HTTP_OK, $response->status());

    }

    public function testDeleteNestedShare()
    {
        $owner = User::factory()->create();
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $collection = Collection::factory([
            'user_id' => $owner->id
        ])->create();
        $childCollection = Collection::factory([
            'user_id'   => $owner->id,
            'parent_id' => $collection->id
        ])->create();
        $share = $this->makeShare($collection, $user, $owner);

        $response = $this->actingAs($user2)->json('DELETE', 'api/shares/private/' . $share->id);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->status());

        $response = $this->actingAs($user)->json('DELETE', 'api/shares/private/' . $share->id);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->status());
        // create new share
        $share = $this->makeShare($collection, $user, $owner);

        $response = $this->actingAs($owner)->json('DELETE', 'api/shares/private/' . $share->id);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->status());
        $this->assertFalse(Collection::find($collection->id)->is_being_shared);

        $response = $this->actingAs($user)->json('GET', 'api/collections/' . $collection->id);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->status());

        $response = $this->actingAs($owner)->json('GET', 'api/collections/' . $collection->id);
        $collection = $response->getData()->data;
        $this->assertFalse($collection->is_being_shared);

        $response = $this->actingAs($owner)->json('GET', 'api/collections/' . $childCollection->id);
        $collection = $response->getData()->data;
        $this->assertFalse($collection->is_being_shared);
    }

    public function testDeleteOneOfTwoNestedShares()
    {
        $owner = User::factory()->create();
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $collection = Collection::factory([
            'user_id' => $owner->id
        ])->create();
        $childCollection = Collection::factory([
            'user_id'   => $owner->id,
            'parent_id' => $collection->id
        ])->create();
        $share = $this->makeShare($collection, $user, $owner);
        $share2 = $this->makeShare($childCollection, $user2, $owner);

        $response = $this->actingAs($owner)->json('DELETE', 'api/shares/private/' . $share->id);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->status());
        $this->assertFalse(Collection::find($collection->id)->is_being_shared);
        // still has to be true because of the second still intact share
        $this->assertTrue(Collection::find($childCollection->id)->is_being_shared);

        $response = $this->actingAs($user)->json('GET', 'api/collections/' . $childCollection->id);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->status());

        $response = $this->actingAs($user2)->json('GET', 'api/collections/' . $collection->id);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->status());

        $response = $this->actingAs($user2)->json('GET', 'api/collections/' . $childCollection->id);
        $collection = $response->getData()->data;
        $this->assertTrue($collection->is_being_shared);
    }

    public function testDeleteOneOfTwoNotRootNestedShares()
    {
        $owner = User::factory()->create();
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $collection = Collection::factory([
            'user_id' => $owner->id
        ])->create();
        $childCollection = Collection::factory([
            'user_id'   => $owner->id,
            'parent_id' => $collection->id
        ])->create();
        $grandChildCollection = Collection::factory([
            'user_id'   => $owner->id,
            'parent_id' => $collection->id
        ])->create();
        $share = $this->makeShare($childCollection, $user, $owner);
        $share2 = $this->makeShare($grandChildCollection, $user2, $owner);

        $response = $this->actingAs($owner)->json('DELETE', 'api/shares/private/' . $share->id);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->status());
        $this->assertFalse(Collection::find($childCollection->id)->is_being_shared);
        // still has to be true because of the second still intact share
        $this->assertTrue(Collection::find($grandChildCollection->id)->is_being_shared);

        $response = $this->actingAs($user)->json('GET', 'api/collections/' . $grandChildCollection->id);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->status());

        $response = $this->actingAs($user2)->json('GET', 'api/collections/' . $childCollection->id);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->status());

        $response = $this->actingAs($user2)->json('GET', 'api/collections/' . $grandChildCollection->id);
        $collection = $response->getData()->data;
        $this->assertTrue($collection->is_being_shared);
    }

    public function testDeleteTwoNestedSharesOfTheSameCollection()
    {
        $owner = User::factory()->create();
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $collection = Collection::factory([
            'user_id' => $owner->id
        ])->create();
        $childCollection = Collection::factory([
            'user_id'   => $owner->id,
            'parent_id' => $collection->id
        ])->create();
        $share = $this->makeShare($collection, $user, $owner);
        $share2 = $this->makeShare($collection, $user2, $owner);

        $response = $this->actingAs($owner)->json('DELETE', 'api/shares/private/' . $share->id);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->status());
        // still has to be true because of the second still intact share
        $this->assertTrue(Collection::find($collection->id)->is_being_shared);
        $this->assertTrue(Collection::find($childCollection->id)->is_being_shared);

        $response = $this->actingAs($user2)->json('GET', 'api/collections/' . $childCollection->id);
        $collection = $response->getData()->data;
        $this->assertTrue($collection->is_being_shared);

        $response = $this->actingAs($owner)->json('DELETE', 'api/shares/private/' . $share2->id);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->status());
        $this->assertFalse(Collection::find($collection->id)->is_being_shared);
        $this->assertFalse(Collection::find($childCollection->id)->is_being_shared);
    }

    private function collectionBelongsToOwner($collection, User $owner)
    {
        $this->assertEquals($owner->id, $collection->user_id);
    }

    private function postBelongsToOwner(Post|\stdClass $post, User $owner)
    {
        $this->assertEquals($owner->id, $post->user_id);
    }

    private function makeShare(Collection $collection, User $user, User $owner): PrivateShare
    {
        $share = PrivateShare::factory([
            'collection_id' => $collection->id,
            'user_id' => $user->id,
            'created_by' => $owner->id
        ])->create();
        Collection::find($collection->id)
            ->descendantsAndSelf()
            ->update(['is_being_shared' => 'true']);
        return $share;
    }

}
