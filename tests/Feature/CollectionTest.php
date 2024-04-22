<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Collection;

class CollectionTest extends TestCase
{

    use RefreshDatabase;

    public function testCreateNestedCollection()
    {
        $user = User::factory()->create([
            'permission' => 255
        ]);

        $response = $this->actingAs($user)->json('POST', 'api/collections', [
            'name' => 'Nested Collection',
            'icon_id' => 107
        ]);

        $this->assertEquals(201, $response->status());
        $rootCollection = $response->getData()->data;
        $this->assertEquals('Nested Collection', $rootCollection->name);
        $this->assertEquals(107, $rootCollection->icon_id);
        $this->assertNotEquals(null, $rootCollection);

        $response = $this->actingAs($user)->json('POST', 'api/collections', [
            'name' => 'Inside Nested Collection',
            'icon_id' => 104,
            'parent_id' => $rootCollection->id
        ]);

        $this->assertEquals(201, $response->status());
        $childCollection = $response->getData()->data;
        $this->assertNotEquals(null, $childCollection);
        $this->assertEquals($rootCollection->id, $childCollection->parent_id);
    }

    public function testRemoveChildCollection()
    {
        $user = User::factory()->create([
            'permission' => 255
        ]);
        $rootCollection = Collection::factory()->create([
            'user_id' => $user->id
        ]);
        $childCollection = Collection::factory()->create([
            'user_id' => $user->id,
            'parent_id' => $rootCollection->id
        ]);

        $this->assertEquals($rootCollection->id, $childCollection->parent_id);

        $response = $this->actingAs($user)->json('PATCH', 'api/collections/' . $childCollection->id, [
            'is_root' => true
        ]);
        $this->assertEquals(200, $response->status());
        $this->assertEmpty(Collection::find($childCollection->id)->parent_id);
    }

    public function testDeleteNestedCollection()
    {
        $user = User::factory()->create([
            'permission' => 255
        ]);
        $rootCollection = Collection::factory()->create([
            'user_id' => $user->id
        ]);
        $childCollection = Collection::factory()->create([
            'user_id' => $user->id,
            'parent_id' => $rootCollection->id
        ]);
        $grandChildCollection = Collection::factory()->create([
            'user_id' => $user->id,
            'parent_id' => $childCollection->id
        ]);
        $grandGrandChildCollection = Collection::factory()->create([
            'user_id' => $user->id,
            'parent_id' => $grandChildCollection->id
        ]);

        $response = $this->actingAs($user)->json('GET', 'api/collections', [
            'nested' => true
        ]);

        $this->assertEquals(200, $response->status());
        $data = $response->getData()->data;
        $this->assertEquals(1, count($data));
        $this->assertEquals(4, Collection::count());
        $this->assertEquals($rootCollection->id, $childCollection->parent_id);
        $this->assertEquals($grandChildCollection->id, $grandGrandChildCollection->parent_id);

        $response = $this->actingAs($user)->json('DELETE', 'api/collections/' . $grandChildCollection->id);
        $this->assertEquals(204, $response->status());
        $this->assertEquals(2, Collection::count());

        $response = $this->actingAs($user)->json('DELETE', 'api/collections/' . $rootCollection->id);
        $this->assertEquals(204, $response->status());
        $this->assertEquals(2, Collection::count());
    }

    public function testDeleteNestedCollectionCompletely()
    {
        $user = User::factory()->create([
            'permission' => 255
        ]);
        $rootCollection = Collection::factory()->create([
            'user_id' => $user->id
        ]);
        $childCollection = Collection::factory()->create([
            'user_id' => $user->id,
            'parent_id' => $rootCollection->id
        ]);
        $grandChildCollection = Collection::factory()->create([
            'user_id' => $user->id,
            'parent_id' => $childCollection->id
        ]);
        $grandGrandChildCollection = Collection::factory()->create([
            'user_id' => $user->id,
            'parent_id' => $grandChildCollection->id
        ]);

        $response = $this->actingAs($user)->json('GET', 'api/collections', [
            'nested' => true
        ]);

        $this->assertEquals(200, $response->status());
        $data = $response->getData()->data;
        $this->assertEquals(1, count($data));
        $this->assertEquals(4, Collection::count());
        $this->assertEquals($rootCollection->id, $childCollection->parent_id);
        $this->assertEquals($grandChildCollection->id, $grandGrandChildCollection->parent_id);

        $response = $this->actingAs($user)->json('DELETE', 'api/collections/' .
            $grandChildCollection->id . '?nested=true');
        $this->assertEquals(204, $response->status());
        $this->assertEquals(2, Collection::count());
        $this->assertTrue($rootCollection->exists());

        $response = $this->actingAs($user)->json('DELETE', 'api/collections/' .
            $rootCollection->id . '?nested=true');
        $this->assertEquals(204, $response->status());
        $this->assertEquals(0, Collection::count());
        $this->assertNull(Collection::find($rootCollection->id));
    }

    public function testDeleteNotNestedCollectionCompletely()
    {
        $user = User::factory()->create([
            'permission' => 255
        ]);
        $collection = Collection::factory()->create([
            'user_id' => $user->id
        ]);
        $unnecessaryCollection = Collection::factory()->create([
            'user_id' => $user->id
        ]);

        // use nested flag intentionally incorrect
        $response = $this->actingAs($user)->json('DELETE', 'api/collections/' .
            $collection->id . '?nested=true');
        $this->assertEquals(204, $response->status());
        $this->assertEquals(1, Collection::count());
        $this->assertEmpty(Collection::find($collection->id));
    }
}
