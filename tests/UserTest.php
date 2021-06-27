<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use App\User;
use App\Collection;
use App\Post;

class UserTest extends TestCase
{

    use DatabaseMigrations;

    public function testCreateUser()
    {
        $user = factory(User::class)->create([
            'permission' => 255
        ]);

        $this->actingAs($user)->json('POST', 'api/users', [
            'name' => 'John Smith',
            'email' => 'john@example.com',
            'password' => 'Foo1234bar'
        ]);

        $this->assertEquals(201, $this->response->status());
        $data = $this->response->getData()->data;
        $this->assertNotEquals(null, $data);

    }

    public function testChangeUserPassword()
    {
        $user = factory(User::class)->create([
            'password' => 'Foo1234bar'
        ]);

        $this->actingAs($user)->json('PATCH', 'api/users/' . $user->id, [
            'password_old' => 'Foo1234bar',
            'password_new' => 'foo1234baR'
        ]);

        $this->assertEquals(200, $this->response->status());
    }

    public function testDeleteUser()
    {
        $user = factory(User::class)->create([
            'permission' => 255
        ]);
        
        factory(Collection::class)->create([
            'user_id' => $user->id
        ]);
        
        factory(Post::class)->create([
            'user_id' => $user->id
        ]);

        $this->actingAs($user)->json('DELETE', 'api/users/' . $user->id);

        $this->assertEquals(200, $this->response->status());
    }

}
