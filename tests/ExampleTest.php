<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class PostTest extends TestCase
{
    public function testExample()
    {
        $contents = [
            'https://laravel.com'
        ];
        foreach ($contents as &$content) {
            $this->json('POST', 'api/posts', ['content' => $content]);
            $this->assertEquals(201, $this->response->status());
            dd($this->response);
        }

    }
}
