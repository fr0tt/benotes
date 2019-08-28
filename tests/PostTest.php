<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class PostTest extends TestCase
{
    public function testPost()
    {
        $user = App\User::find(1);

        $contents = [
            'https://laravel.com'
        ];

        foreach ($contents as &$content) {
            $this->actingAs($user)->json('POST', 'api/posts', ['content' => $content]);
            $this->assertEquals(201, $this->response->status());
            $data = $this->response->getData()->data;
            if ($data->type === 'link') {
                $this->assertNotEquals(null, $data->color);
            }
        }

    }
}
