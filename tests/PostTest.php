<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Symfony\Component\HttpFoundation\Response;
use App\User;
use App\Post;
use App\Collection;

class PostTest extends TestCase
{

    use DatabaseMigrations;

    /**
     * @group failing
     * @dataProvider createPostsProvider
     */
    public function testCreatePost($content, $type)
    {
        $user = factory(User::class)->create();

        $collection = factory(Collection::class)->create();

        $this->actingAs($user)->json('POST', 'api/posts', [
            'content' => $content,
            'collection_id' => $collection->id
        ]);

        //echo var_dump($this->response->getData()->data);

        $this->assertEquals(201, $this->response->status());
        $data = $this->response->getData()->data;
        $this->assertEquals($type, $data->type);
        if ($data->type === 'link') {
            $this->assertNotEquals(null, $data->color);
            $this->assertNotEquals(null, $data->url);
        }
        $this->assertNotEquals(null, $data->collection_id);

    }

    /**
     * dataProvider for testCreatePost()
     */
    public function createPostsProvider()
    {
        return [
            1 => ['https://test.de', 'link'],
            2 => ['https://go-rel.github.io/', 'link'],
            3 => ['https://www.youtube.com/watch?v=ZyURjdnYQaU', 'link'],
            4 => ['https://github.com/verlok/vanilla-lazyload', 'link'],
            5 => ['https://www.amazon.com/Design-Everyday-Things-Revised-Expanded/dp/0465050654/ref=sr_1_1?dchild=1&keywords=don+norman&link_code=qs&qid=1608495907&sr=8-1&tag=operabrowser-21', 'link'],
            6 => ['<a href="https://www.wolframalpha.com" rel="noopener noreferrer nofollow">https://www.wolframalpha.com</a>', 'link'],
            7 => ['<p class="">dfgd adijfds https://google.com</p>', 'text'],
            8 => ['<p>https://www.wolframalpha.com</p><p>https://laravel.com</p>', 'text'],
            9 => ['Hdfgd fijsdoij <a href="https://slack.com" rel="noopener noreferrer nofollow">https://slack.com</a>', 'text'],
            10 => ['https://laravel.com', 'link'],
            11 => ['Lorem ipsum https://fonts.adobe.com/fonts/realist', 'text'],
            12 => ['https://gamesindustry.biz', 'link'],
            13 => ['https://www.php.net/manual/en/function.parse-url.php', 'link'],
        ];
    }

    public function testCreatePostWithInvalidLink()
    {
        $user = factory(User::class)->create();

        $collection = factory(Collection::class)->create();
        $content = 'https://test.com';

        $this->actingAs($user)->json('POST', 'api/posts', [
            'content' => $content,
            'collection_id' => $collection->id
        ]);

        $this->assertEquals(201, $this->response->status());
        $data = $this->response->getData()->data;
        $this->assertEquals('link', $data->type);
        $this->assertEquals($content, $data->content);
        $this->assertNotEquals(null, $data->url);
    }

    public function testCreatePostWithStyle()
    {

        $user = factory(User::class)->create();
        $collection = factory(Collection::class)->create();

        $content = '<h2>Hi there,</h2><p>this is a very <em>basic</em> example of tiptap.</p>' .
            '<pre><code>body { display: none; }</code></pre>' .
            '<ul><li><p>A regular list</p></li><li><p>With regular items</p></li></ul><blockquote>' .
            '<p>It\'s amazing 👏 <br>– mom</p></blockquote>';

        $this->actingAs($user)->json('POST', 'api/posts', [
            'content' => $content,
            'collection_id' => $collection->id
        ]);

        $this->assertEquals(201, $this->response->status());
        $data = $this->response->getData()->data;
        $this->assertEquals('text', $data->type);
        $this->assertEquals($content, $data->content);
    }

    public function testUpdatePost()
    {
        $user = factory(User::class)->create();
        $collection = factory(Collection::class)->create();

        $content = '<h2>Hi there,</h2><p>this is a very <em>basic</em> example of tiptap.</p>' .
        '<pre><code>body { display: none; }</code></pre>' .
        '<ul><li><p>A regular list</p></li><li><p>With regular items</p></li></ul><blockquote>' .
        '<p>It\'s amazing 👏 <br>– mom</p></blockquote>';

        $this->actingAs($user)->json('POST', 'api/posts', [
            'content' => $content,
            'collection_id' => $collection->id
        ]);

        $this->assertEquals(201, $this->response->status());
        $post = $this->response->getData()->data;

        $new_content = $content . '<p>++</p>';

        $this->actingAs($user)->json('PATCH', 'api/posts/' . $post->id, [
            'content' => $new_content
        ]);

        $this->assertEquals(200, $this->response->status());
        $data = $this->response->getData()->data;
        $this->assertEquals($new_content, $data->content);
    }

    public function testUpdatePostOrder()
    {
        $user = factory(User::class)->create();
        $collection = factory(Collection::class)->create();

        $post = factory(Post::class)->create([
            'collection_id' => $collection->id,
            'order' => 1
        ]);
        $post2 = factory(Post::class)->create([
            'collection_id' => $collection->id,
            'order' => 2
        ]);
        $post3 = factory(Post::class)->create([
            'collection_id' => $collection->id,
            'order' => 3
        ]);
        $post4 = factory(Post::class)->create([
            'collection_id' => $collection->id,
            'order' => 4
        ]);

        // instead of 4 3 2 1 --> 4 1 3 2
        $this->actingAs($user)->json('PATCH', 'api/posts/' . $post->id, [
            'order' => 3
        ]);

        $this->assertEquals(Post::find($post4->id)->order, $post4->order);
        $this->assertEquals(3, Post::find($post->id)->order);
        $this->assertEquals(2, Post::find($post3->id)->order);
        $this->assertEquals(1, Post::find($post2->id)->order);
    }

    public function testUpdatePostOrderFromHigherToLower()
    {
        $user = factory(User::class)->create();
        $collection = factory(Collection::class)->create();

        $post = factory(Post::class)->create([
            'collection_id' => $collection->id,
            'order' => 1
        ]);
        $post2 = factory(Post::class)->create([
            'collection_id' => $collection->id,
            'order' => 2
        ]);

        // instead of 2 1 --> 1 2
        $this->actingAs($user)->json('PATCH', 'api/posts/' . $post->id, [
            'order' => 2
        ]);

        $this->assertEquals(2, Post::find($post->id)->order);
        $this->assertEquals(1, Post::find($post2->id)->order);
    }

    public function testUpdateOrderAndDeletePost()
    {
        $user = factory(User::class)->create();
        $collection = factory(Collection::class)->create();

        $post = factory(Post::class)->create([
            'collection_id' => $collection->id,
            'order' => 1
        ]);
        $post2 = factory(Post::class)->create([
            'collection_id' => $collection->id,
            'order' => 2
        ]);
        $post3 = factory(Post::class)->create([
            'collection_id' => $collection->id,
            'order' => 3
        ]);
        $post4 = factory(Post::class)->create([
            'collection_id' => $collection->id,
            'order' => 4
        ]);

        // 4 3 2 1 --> 4 -3- 2 1 --> 4 1 2 --> 1 4 2
        $this->actingAs($user)->json('DELETE', 'api/posts/' . $post3->id);

        $this->assertEquals(3, Post::find($post4->id)->order);
        $this->assertEquals(2, Post::find($post2->id)->order);
        $this->assertEquals(1, Post::find($post->id)->order);

        $this->actingAs($user)->json('PATCH', 'api/posts/' . $post->id, [
            'order' => 2
        ]);

        $this->assertEquals(3, Post::find($post4->id)->order);
        $this->assertEquals(2, Post::find($post->id)->order);
        $this->assertEquals(1, Post::find($post2->id)->order);

        $this->actingAs($user)->json('PATCH', 'api/posts/' . $post->id, [
            'order' => 3
        ]);

        $this->assertEquals(3, Post::find($post->id)->order);
        $this->assertEquals(2, Post::find($post4->id)->order);
        $this->assertEquals(1, Post::find($post2->id)->order);
    }

    public function testDeletePost()
    {
        $user = factory(User::class)->create();
        $collection = factory(Collection::class)->create([
            'user_id' => $user->id
        ]);

        $post = factory(Post::class)->create([
            'collection_id' => $collection->id,
            'user_id' => $user->id
        ]);
        $this->actingAs($user)->json('DELETE', 'api/posts/' . $post->id);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $this->response->status());
        $this->assertTrue(Post::onlyTrashed()->find($post->id)->trashed());

        $post2 = factory(Post::class)->create([
            'collection_id' => $collection->id,
            'user_id' => $user->id
        ]);
        $this->actingAs($user)->json('PATCH', 'api/posts/' . $post2->id, [
            'is_archived' => true
        ]);
        $this->assertTrue(Post::onlyTrashed()->find($post2->id)->trashed());

    }

    public function testDeleteAndRestorePost()
    {

        $user = factory(User::class)->create();
        $collection = factory(Collection::class)->create([
            'user_id' => $user->id
        ]);

        // 4 3 -2- 1      --> 3 2 1   --> 4 3 2 1

        array_map(function ($order) use ($collection, $user) {
            factory(Post::class)->create([
                'collection_id' => $collection->id,
                'user_id' => $user->id,
                'order' => $order
            ]);
        }, [1, 2, 3, 4]);

        // delete
        $postId = Post::where('collection_id', $collection->id)
            ->where('order', 2)->first()->id;

        $this->actingAs($user)->json('DELETE', 'api/posts/' . $postId);

        $posts = Post::where('collection_id', $collection->id)
            ->orderBy('order', 'desc')->get();

        $this->assertTrue(Post::onlyTrashed()->find($postId)->trashed());
        $this->assertEquals(3, $posts->count());
        $this->assertEquals([3, 2, 1],
            [$posts[0]->order, $posts[1]->order, $posts[2]->order]);

        // restore
        $this->actingAs($user)->json('PATCH', 'api/posts/' . $postId, [
            'is_archived' => false
        ]);

        $posts = Post::where('collection_id', $collection->id)
            ->orderBy('order', 'desc')->get();

        $this->assertNotNull(Post::find($postId));
        $this->assertEquals([4, 3, 2, 1],
            [$posts[0]->order, $posts[1]->order, $posts[2]->order , $posts[3]->order]);

    }

    public function testDeleteAndRestoreFirstPost()
    {

        $user = factory(User::class)->create();
        $collection = factory(Collection::class)->create([
            'user_id' => $user->id
        ]);

        // -3- 2 1        --> 2 1     --> 3 2 1

        array_map(function ($order) use ($collection, $user) {
            factory(Post::class)->create([
                'collection_id' => $collection->id,
                'user_id' => $user->id,
                'order' => $order
            ]);
        }, [1, 2, 3]);

        // delete
        $postId = Post::where('collection_id', $collection->id)
            ->where('order', 3)->first()->id;

        $this->actingAs($user)->json('DELETE', 'api/posts/' . $postId);

        $posts = Post::where('collection_id', $collection->id)
            ->orderBy('order', 'desc')->get();

        $this->assertTrue(Post::onlyTrashed()->find($postId)->trashed());
        $this->assertEquals(2, $posts->count());
        $this->assertEquals([2, 1],
            [$posts[0]->order, $posts[1]->order]
        );

        // restore
        $this->actingAs($user)->json('PATCH', 'api/posts/' . $postId, [
            'is_archived' => false
        ]);

        $posts = Post::where('collection_id', $collection->id)
            ->orderBy('order', 'desc')->get();

        $this->assertNotNull(Post::find($postId));
        $this->assertEquals(
            [3, 2, 1],
            [$posts[0]->order, $posts[1]->order, $posts[2]->order]
        );


    }

    public function testDeleteMultipleAndRestoreOnePost()
    {

        $user = factory(User::class)->create();
        $collection = factory(Collection::class)->create([
            'user_id' => $user->id
        ]);

        // -5- -4- 3 2 1  --> 3 2 1   --> 4 3 2 1

        array_map(function ($order) use ($collection, $user) {
            factory(Post::class)->create([
                'collection_id' => $collection->id,
                'user_id' => $user->id,
                'order' => $order
            ]);
        },
            [1, 2, 3, 4, 5]
        );

        // delete
        $postId = Post::where('collection_id', $collection->id)
            ->where('order', 5)->first()->id;
        $postId2 = Post::where('collection_id', $collection->id)
            ->where('order', 4)->first()->id;

        $this->actingAs($user)->json('DELETE', 'api/posts/' . $postId);
        $this->actingAs($user)->json('DELETE', 'api/posts/' . $postId2);

        $posts = Post::where('collection_id', $collection->id)
            ->orderBy('order', 'desc')->get();

        $this->assertTrue(Post::onlyTrashed()->find($postId)->trashed());
        $this->assertTrue(Post::onlyTrashed()->find($postId2)->trashed());
        $this->assertEquals(3, $posts->count());
        $this->assertEquals(
            [3, 2, 1],
            [$posts[0]->order, $posts[1]->order, $posts[2]->order]
        );

        // restore
        $this->actingAs($user)->json('PATCH', 'api/posts/' . $postId, [
            'is_archived' => false
        ]);

        $posts = Post::where('collection_id', $collection->id)
            ->orderBy('order', 'desc')->get();

        $this->assertNotNull(Post::find($postId));
        $this->assertNull(Post::find($postId2));
        $this->assertEquals([4, 3, 2, 1], [
            $posts[0]->order, $posts[1]->order, $posts[2]->order,
            $posts[3]->order
        ]);

    }

    public function testChangeCollection()
    {
        $user = factory(User::class)->create();
        $collection  = factory(Collection::class)->create();
        $collection2 = factory(Collection::class)->create();

        $post = factory(Post::class)->create([
            'collection_id' => $collection->id,
            'order' => 1
        ]);
        $post2 = factory(Post::class)->create([
            'collection_id' => $collection->id,
            'order' => 2
        ]);
        $post3 = factory(Post::class)->create([
            'collection_id' => $collection->id,
            'order' => 3
        ]);
        $post4 = factory(Post::class)->create([
            'collection_id' => $collection2->id,
            'order' => 1
        ]);

        $this->actingAs($user)->json('PATCH', 'api/posts/' . $post->id, [
            'collection_id' => $collection2->id
        ]);

        // 3 2 1 and 4 --> 3 2 and 1 4
        $this->assertEquals($collection2->id, Post::find($post->id)->collection_id);
        $this->assertEquals(2, Post::where('collection_id', $collection2->id)->count());

        $this->assertEquals(1, Post::find($post2->id)->order);
        $this->assertEquals(2, Post::find($post3->id)->order);
        $this->assertEquals(2, Post::find($post->id)->order);
        $this->assertEquals(Post::find($post4->id)->order, $post4->order);
    }

    public function testCreatePostWithDifferentCollections()
    {

        $user = factory(User::class)->create();
        $collection = factory(Collection::class)->create();

        $posts = [
            [
                'content' => 'foo bar',
                'collection_id' => $collection->id
            ],
            [
                'content' => 'foo bar',
                'is_uncategorized' => true
            ],
            [
                'content' => 'foo bar',
                'is_uncategorized' => false
            ],
            [
                'content' => 'foo bar'
            ]
        ];

        foreach ($posts as &$post) {
            $this->actingAs($user)->json('POST', 'api/posts', $post);
            $this->assertEquals(201, $this->response->status());
            $data = $this->response->getData()->data;
            $this->assertNotEquals($data, null);
        }
    }

    public function testCreatePostWithoutAuth()
    {
        $post = [
            'content' => 'foo bar'
        ];
        $this->json('POST', 'api/posts', $post);
        $this->assertEquals(401, $this->response->status());
    }

    public function testCreatePostWithDifferentUsers()
    {
        $post = [
            'content' => 'foo bar'
        ];
        $user = factory(User::class)->create();
        $user2 = factory(User::class)->create();

        $this->actingAs($user)->json('POST', 'api/posts', $post);
        $this->assertEquals(201, $this->response->status());

        $this->actingAs($user2)->json('POST', 'api/posts', $post);
        $this->assertEquals(201, $this->response->status());

        $this->actingAs($user2)->json('POST', 'api/posts', [
            'content' => 'foo bar',
            'user_id' => $user->id
        ]);
        $this->assertEquals(201, $this->response->status());
        $this->assertLessThan(Post::where('user_id', $user2->id)->count(),
            Post::where('user_id', $user->id)->count());
    }

    public function testCreatePostWithNotOwnedCollection()
    {
        $user = factory(User::class)->create();
        $user2 = factory(User::class)->create();
        $collection = factory(Collection::class)->create([
            'user_id' => $user->id
        ]);

        $this->actingAs($user2)->json('POST', 'api/posts', [
            'content' => 'foo bar',
            'collection_id' => $collection->id
        ]);
        $this->assertEquals(403, $this->response->status());
    }

    public function testGetPostWithOffset()
    {
        $user = factory(User::class)->create();
        $post1 = factory(Post::class)->create([
            'order' => 1
        ]);
        $post2 = factory(Post::class)->create([
            'order' => 2
        ]);
        $post3 = factory(Post::class)->create([
            'order' => 3
        ]);
        $post4 = factory(Post::class)->create([
            'order' => 4
        ]);

        $this->actingAs($user)->json('GET', 'api/posts?offset=2&limit=2');
        $data = $this->response->getData()->data;
        $this->assertEquals(count($data), 2);
        $this->assertEquals($data[0]->order, 2);

    }

    public function testGetPostWithoutAuth()
    {
        $user = factory(User::class)->create();
        $post = factory(Post::class)->create();

        $this->json('GET', 'api/posts');
        $this->assertEquals(401, $this->response->status());
        $this->json('GET', 'api/posts/' . $post->id);
        $this->assertEquals(401, $this->response->status());
    }

    public function testGetPostFromOwnerOnly()
    {
        $user = factory(User::class)->create();
        $user2 = factory(User::class)->create();
        $post = factory(Post::class)->create([
            'user_id' => $user->id
        ]);
        factory(Post::class)->create([
            'user_id' => $user->id
        ]);
        factory(Post::class)->create([
            'user_id' => $user2->id
        ]);

        $this->actingAs($user)->json('GET', 'api/posts');
        $amountOfPosts = count($this->response->getData()->data);
        $this->assertEquals(200, $this->response->status());
        $this->actingAs($user2)->json('GET', 'api/posts');
        $amountOfPosts2 = count($this->response->getData()->data);
        $this->assertEquals(200, $this->response->status());
        $this->assertLessThan($amountOfPosts, $amountOfPosts2);

        $this->actingAs($user2)->json('GET', 'api/posts/' . $post->id);
        $this->assertEquals(403, $this->response->status());
    }

    public function testCreatePostWithoutStorage()
    {
        config(['benotes.use_filesystem' => false]);

        $user = factory(User::class)->create();
        $collection = factory(Collection::class)->create();

        $this->actingAs($user)->json('POST', 'api/posts', [
            'content' => 'https://nyt.com',
            'collection_id' => $collection->id
        ]);

        $this->assertEquals(201, $this->response->status());
        $data = $this->response->getData()->data;
        $this->assertEquals('link', $data->type);
        $this->assertStringStartsNotWith('/storage/thumbnails/thumbnail_', $data->image_path);
        $this->assertStringStartsWith('https://', $data->image_path);


        config(['benotes.use_filesystem' => true]);

        $this->actingAs($user)->json('POST', 'api/posts', [
            'content' => 'https://nyt.com',
            'collection_id' => $collection->id
        ]);

        $this->assertEquals(201, $this->response->status());
        $data = $this->response->getData()->data;
        $this->assertEquals('link', $data->type);
        $this->assertStringStartsWith('/storage/thumbnails/thumbnail_', $data->image_path);
        $this->assertStringStartsNotWith('https://', $data->image_path);
    }

}
