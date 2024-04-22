<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Collection;
use App\Models\Post;
use App\Models\PublicShare;
use Illuminate\Testing\Fluent\AssertableJson;

class PublicShareTest extends TestCase
{

    use RefreshDatabase;

    public function testCreateShare()
    {

        $user = User::factory()->create();
        $collection = Collection::factory()->create();
        $token = $this->faker->slug();

        $response = $this->actingAs($user)->json('POST', 'api/shares/public', [
            'token'         => $token,
            'collection_id' => $collection->id,
            'is_active'     => true
        ]);

        $this->assertEquals(201, $response->status());


        $response = $this->actingAs($user)->json('GET', 's?token=' . $token);
        $this->assertEquals(200, $response->status());
    }

    public function testAccessSharedPosts()
    {
        $user = User::factory()->create();
        $collection = Collection::factory()->create();
        Post::factory(['collection_id' => $collection->id])->create();
        $token = $this->faker->slug();

        PublicShare::create([
            'token'         => $token,
            'collection_id' => $collection->id,
            'is_active'     => true,
            'created_by'    => $user->id
        ]);

        $requestWithToken = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ]);

        $collection2 = Collection::factory()->create();
        Post::factory(['collection_id' => $collection2->id])->create();

        $response = $requestWithToken->json('GET', 'api/posts');
        $this->assertEquals(200, $response->status());

        $response = $requestWithToken->json('GET', 'api/posts?collection_id=' . $collection->id);
        $this->assertEquals(200, $response->status());

        $response = $requestWithToken->json('GET', 'api/posts?collection_id=' . $collection2->id);
        $this->assertEquals(1, count($response->getData()->data));
        $response->assertJson(
            fn(AssertableJson $json) =>
            $json->where('data.0.collection_id', $collection->id)
        );
    }

    public function testDeleteShare()
    {

        $user = User::factory()->create();
        $collection = Collection::factory()->create();
        $token = $this->faker->slug();

        $response = $this->actingAs($user)->json('POST', 'api/shares/public', [
            'token'         => $token,
            'collection_id' => $collection->id,
            'is_active'     => true
        ]);

        $this->assertEquals(201, $response->status());
        $share = $response->getData()->data;

        $response = $this->actingAs($user)->json('DELETE', 'api/shares/public/' . $share->id);
        $this->assertEquals(204, $response->status());
    }
}
