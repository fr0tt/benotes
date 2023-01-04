<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Collection;
use App\Models\Post;

use function PHPUnit\Framework\assertGreaterThan;

class UserTest extends TestCase
{

    use RefreshDatabase;

    public function testCreateUser()
    {
        $user = User::factory()->create([
            'permission' => 255
        ]);

        $response = $this->actingAs($user)->json('POST', 'api/users', [
            'name' => 'John Smith',
            'email' => 'john@example.com',
            'password' => 'Foo1234bar'
        ]);

        $this->assertEquals(201, $response->status());
        $data = $response->getData()->data;
        $this->assertNotEquals(null, $data);
    }

    public function testCreateUserWithoutAuth()
    {
        $response = $this->json('POST', 'api/users', [
            'name' => 'Johnny Smith',
            'email' => 'johnny@example.com',
            'password' => 'Foo1234bar'
        ]);

        $this->assertGreaterThan(299, $response->status());
    }

    public function testChangeUserPassword()
    {
        $user = User::factory()->create([
            'password' => 'Foo1234bar'
        ]);

        $response = $this->actingAs($user)->json('PATCH', 'api/users/' . $user->id, [
            'password_old' => 'Foo1234bar',
            'password_new' => 'foo1234baR'
        ]);

        $this->assertEquals(200, $response->status());
    }

    public function testDeleteUser()
    {
        $user = User::factory()->create([
            'permission' => 255
        ]);

        $collection = Collection::factory()->create([
            'user_id' => $user->id
        ]);

        Post::factory()->create([
            'user_id' => $user->id,
            'collection_id' => $collection->id
        ]);


        $user2 = User::factory()->create();

        $collection2 = Collection::factory()->create([
            'user_id' => $user2->id
        ]);

        Post::factory()->create([
            'user_id' => $user2->id,
            'collection_id' => $collection2->id
        ]);

        $response = $this->actingAs($user)->json('DELETE', 'api/users/' . $user->id);

        $this->assertEquals(200, $response->status());

        // only data of the deleted user should be deleted, not everything from everyone
        $this->assertGreaterThan(0, Post::where('user_id', $user2->id)->count());
    }
}
