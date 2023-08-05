<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use App\Models\Post;
use App\Models\Collection;
use App\Models\Tag;
use App\Models\PostTag;

class PostTest extends TestCase
{

    use RefreshDatabase;

    /**
     * @dataProvider createPostsProvider
     */
    public function testCreatePost($content, $type)
    {
        $user = User::factory()->create();

        $collection = Collection::factory()->create();

        $response = $this->actingAs($user)->json('POST', 'api/posts', [
            'content'       => $content,
            'collection_id' => $collection->id
        ]);

        //echo var_dump($response->getData()->data);

        $this->assertEquals(201, $response->status());
        $data = $response->getData()->data;
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
            1  => ['https://go-rel.github.io/', 'link'],
            2  => ['https://www.youtube.com/watch?v=ZyURjdnYQaU', 'link'],
            3  => ['https://github.com/verlok/vanilla-lazyload', 'link'],
            4  => ['https://www.amazon.com/Design-Everyday-Things-Revised-Expanded/dp/0465050654/ref=sr_1_1?dchild=1&keywords=don+norman&link_code=qs&qid=1608495907&sr=8-1&tag=operabrowser-21', 'link'],
            5  => ['<a href="https://www.wolframalpha.com" rel="noopener noreferrer nofollow">https://www.wolframalpha.com</a>', 'link'],
            6  => ['<p class="">dfgd adijfds https://google.com</p>', 'text'],
            7  => ['<p>https://www.wolframalpha.com</p><p>https://laravel.com</p>', 'text'],
            8  => ['Hdfgd fijsdoij <a href="https://slack.com" rel="noopener noreferrer nofollow">https://slack.com</a>', 'text'],
            9  => ['https://laravel.com', 'link'],
            10 => ['Lorem ipsum https://fonts.adobe.com/fonts/realist', 'text'],
            11 => ['https://gamesindustry.biz', 'link'],
            12 => ['https://www.php.net/manual/en/function.parse-url.php', 'link'],
        ];
    }

    public function testCreatePostWithThumbnail()
    {
        $this->assertTrue(config('benotes.use_filesystem'));
        $user = User::factory()->create();
        $collection = Collection::factory()->create();

        $response = $this->actingAs($user)->json('POST', 'api/posts', [
            'content'       => 'https://nyt.com',
            'collection_id' => $collection->id
        ]);

        $this->assertEquals(201, $response->status());
        $data = $response->getData()->data;
        $this->assertEquals('link', $data->type);
        $this->assertNotEmpty($data->description);
        $this->assertNotEmpty($data->title);
        $this->assertNotEmpty($data->image_path);
        $this->assertStringStartsWith(url('/storage/thumbnails/thumbnail_'), $data->image_path);
        $this->assertStringStartsNotWith('https://', $data->image_path);
    }

    public function testCreatePostWithScreenshot()
    {
        $this->assertTrue(config('benotes.use_filesystem'));
        $user = User::factory()->create();
        $collection = Collection::factory()->create();

        $response = $this->actingAs($user)->json('POST', 'api/posts', [
            'content'       => 'https://en.wikipedia.org/wiki/Wikipedia:About',
            'collection_id' => $collection->id
        ]);

        $this->assertEquals(201, $response->status());
        $data = $response->getData()->data;
        $this->assertEquals('link', $data->type);
        $this->assertNotEmpty($data->title);
        $this->assertEmpty($data->image_path);

        Artisan::call('thumbnail:generate ' . $data->id);

        $post = Post::find($data->id);
        $this->assertNotEmpty($post->image_path);
        $this->assertStringStartsNotWith('https://', $post->image_path);
        $this->assertStringStartsWith(url('/storage/thumbnails/thumbnail_'), $post->image_path);
        $this->assertFileExists(
            storage_path(
                'app/public/thumbnails/' . $post->getRawOriginal('image_path')
            )
        );
    }

    public function testCreatePostWithInvalidLink()
    {
        $user = User::factory()->create();

        $collection = Collection::factory()->create();
        $content = 'https://sdoifhpsuidfsiuedhsdiuhfuidhipdh.com';

        $response = $this->actingAs($user)->json('POST', 'api/posts', [
            'content'       => $content,
            'collection_id' => $collection->id
        ]);

        $this->assertEquals(201, $response->status());
        $data = $response->getData()->data;
        $this->assertEquals('link', $data->type);
        $this->assertEquals($content, $data->content);
        $this->assertNotEquals(null, $data->url);
    }

    public function testCreatePostWithStyle()
    {

        $user = User::factory()->create();
        $collection = Collection::factory()->create();

        $content = '<h2>Hi there,</h2><p>this is a very <em>basic</em> example of tiptap.</p>' .
            '<pre><code>body { display: none; }</code></pre>' .
            '<ul><li><p>A regular list</p></li><li><p>With regular items</p></li></ul><blockquote>' .
            '<p>It\'s amazing 👏 <br>– mom</p></blockquote>';

        $response = $this->actingAs($user)->json('POST', 'api/posts', [
            'content'       => $content,
            'collection_id' => $collection->id
        ]);

        $this->assertEquals(201, $response->status());
        $data = $response->getData()->data;
        $this->assertEquals('text', $data->type);
        $this->assertEquals($content, $data->content);
    }

    public function testCreateUncategorizedPostInCorrectOrder()
    {

        $user = User::factory()->create();

        $post = Post::factory()->create([
            'order' => 1
        ]);
        $post2 = Post::factory()->create([
            'order' => 2
        ]);

        $content = 'Post no.3 should have order 3';

        $response = $this->actingAs($user)->json('POST', 'api/posts', [
            'content' => $content
        ]);

        $this->assertEquals(201, $response->status());
        $data = $response->getData()->data;
        $this->assertEquals(3, $data->order);
        $this->assertEquals($content, $data->content);
    }

    public function testCreatePostWithTags()
    {
        $user = User::factory()->create();
        $collection = Collection::factory()->create();
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();

        $content = 'https://github.com';

        $response = $this->actingAs($user)->json('POST', 'api/posts', [
            'content'       => $content,
            'collection_id' => $collection->id,
            'tags'          => [$tag1->id, $tag2->id]
        ]);

        $this->assertEquals(201, $response->status());
        $data = $response->getData()->data;
        $this->assertEquals('link', $data->type);
        $this->assertEquals($content, $data->content);
        $this->assertTrue(
            PostTag::where('post_id', $data->id)->where('tag_id', $tag1->id)->exists()
        );
        $this->assertTrue(
            PostTag::where('post_id', $data->id)->where('tag_id', $tag2->id)->exists()
        );
    }

    public function testUpdatePost()
    {
        $user = User::factory()->create();
        $collection = Collection::factory()->create();

        $content = '<h2>Hi there,</h2><p>this is a very <em>basic</em> example of tiptap.</p>' .
            '<pre><code>body { display: none; }</code></pre>' .
            '<ul><li><p>A regular list</p></li><li><p>With regular items</p></li></ul><blockquote>' .
            '<p>It\'s amazing 👏 <br>– mom</p></blockquote>';

        $response = $this->actingAs($user)->json('POST', 'api/posts', [
            'content'       => $content,
            'collection_id' => $collection->id
        ]);

        $this->assertEquals(201, $response->status());
        $post = $response->getData()->data;

        $new_content = $content . '<p>++</p>';

        $response = $this->actingAs($user)->json('PATCH', 'api/posts/' . $post->id, [
            'content' => $new_content
        ]);

        $this->assertEquals(200, $response->status());
        $data = $response->getData()->data;
        $this->assertEquals($new_content, $data->content);
    }

    public function testUpdatePostWithLink()
    {

        $user = User::factory()->create();
        $collection = Collection::factory()->create();

        $response = $this->actingAs($user)->json('POST', 'api/posts', [
            'content'       => 'https://gitlab.com',
            'collection_id' => $collection->id
        ]);

        $this->assertEquals(201, $response->status());
        $post = $response->getData()->data;
        $this->assertNotEmpty($post->description);
        $this->assertNotEmpty($post->image_path);

        $post = Post::find($post->id);
        $post->title = 'FooBar';
        $post->description = null;
        $post->image_path = null;
        $post->save();
        $this->assertEmpty($post->description);
        $this->assertEmpty($post->image_path);

        $response = $this->actingAs($user)->json('PATCH', 'api/posts/' . $post->id, [
            'content' => 'https://gitlab.com'
        ]);

        $this->assertEquals(200, $response->status());
        $data = $response->getData()->data;
        $this->assertEquals($post->title, $data->title);
        $this->assertNotEmpty($data->description);
        $this->assertNotEmpty($data->image_path);
    }

    public function testUpdatePostWithLinkWithoutThumbnail()
    {

        Config::set('benotes.generate_missing_thumbnails', false);
        $this->assertFalse(config('benotes.generate_missing_thumbnails'));

        $user = User::factory()->create();
        $collection = Collection::factory()->create();

        $response = $this->actingAs($user)->json('POST', 'api/posts', [
            'content'       => 'https://en.wikipedia.org/wiki/Wikipedia:About',
            'collection_id' => $collection->id
        ]);

        $this->assertEquals(201, $response->status());
        $post = $response->getData()->data;
        $this->assertEmpty($post->image_path);

        $response = $this->actingAs($user)->json('PATCH', 'api/posts/' . $post->id, [
            'content' => 'https://en.wikipedia.org/wiki/Wikipedia:About'
        ]);

        $this->assertEquals(200, $response->status());
        $data = $response->getData()->data;
        $this->assertEquals($post->title, $data->title);
        $this->assertEquals('', $data->image_path);
    }

    public function testUpdatePostWithTag()
    {
        $user = User::factory()->create();
        $collection = Collection::factory()->create();
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();

        $content = 'This is a very basic example.';

        $response = $this->actingAs($user)->json('POST', 'api/posts', [
            'content'       => $content,
            'collection_id' => $collection->id,
            'tags'          => [$tag1->id]
        ]);

        $this->assertEquals(201, $response->status());
        $data = $response->getData()->data;
        $this->assertEquals($tag1->id, $data->tags[0]->id);

        $new_content = $content . '<p>++</p>';

        $response = $this->actingAs($user)->json('PATCH', 'api/posts/' . $data->id, [
            'tags' => [$tag2->id]
        ]);

        $this->assertEquals(200, $response->status());
        $data = $response->getData()->data;
        $this->assertEquals($tag2->id, $data->tags[0]->id);
    }

    public function testUpdatePostOrder()
    {
        $user = User::factory()->create();
        $collection = Collection::factory()->create();

        $post = Post::factory()->create([
            'collection_id' => $collection->id,
            'order'         => 1
        ]);
        $post2 = Post::factory()->create([
            'collection_id' => $collection->id,
            'order'         => 2
        ]);
        $post3 = Post::factory()->create([
            'collection_id' => $collection->id,
            'order'         => 3
        ]);
        $post4 = Post::factory()->create([
            'collection_id' => $collection->id,
            'order'         => 4
        ]);

        // instead of 4 3 2 1 --> 4 1 3 2
        $response = $this->actingAs($user)->json('PATCH', 'api/posts/' . $post->id, [
            'order' => 3
        ]);

        $this->assertEquals(Post::find($post4->id)->order, $post4->order);
        $this->assertEquals(3, Post::find($post->id)->order);
        $this->assertEquals(2, Post::find($post3->id)->order);
        $this->assertEquals(1, Post::find($post2->id)->order);
    }

    public function testUpdatePostOrderFromHigherToLower()
    {
        $user = User::factory()->create();
        $collection = Collection::factory()->create();

        $post = Post::factory()->create([
            'collection_id' => $collection->id,
            'order'         => 1
        ]);
        $post2 = Post::factory()->create([
            'collection_id' => $collection->id,
            'order'         => 2
        ]);

        // instead of 2 1 --> 1 2
        $response = $this->actingAs($user)->json('PATCH', 'api/posts/' . $post->id, [
            'order' => 2
        ]);

        $this->assertEquals(2, Post::find($post->id)->order);
        $this->assertEquals(1, Post::find($post2->id)->order);
    }

    public function testUpdateOrderByProvidingWrongOrder()
    {
        $user = User::factory()->create();
        $collection = Collection::factory()->create();

        $post = Post::factory()->create([
            'collection_id' => $collection->id,
            'order'         => 1
        ]);
        $post2 = Post::factory()->create([
            'collection_id' => $collection->id,
            'order'         => 2
        ]);

        $response = $this->actingAs($user)->json('PATCH', 'api/posts/' . $post->id, [
            'order' => 3
        ]);

        $this->assertEquals(2, Post::find($post->id)->order);
        $this->assertEquals(1, Post::find($post2->id)->order);
    }

    public function testUpdateOrderAndDeletePost()
    {
        $user = User::factory()->create();
        $collection = Collection::factory()->create();

        $post = Post::factory()->create([
            'collection_id' => $collection->id,
            'order'         => 1
        ]);
        $post2 = Post::factory()->create([
            'collection_id' => $collection->id,
            'order'         => 2
        ]);
        $post3 = Post::factory()->create([
            'collection_id' => $collection->id,
            'order'         => 3
        ]);
        $post4 = Post::factory()->create([
            'collection_id' => $collection->id,
            'order'         => 4
        ]);

        // 4 3 2 1 --> 4 -3- 2 1 --> 4 1 2 --> 1 4 2
        $response = $this->actingAs($user)->json('DELETE', 'api/posts/' . $post3->id);

        $this->assertEquals(3, Post::find($post4->id)->order);
        $this->assertEquals(2, Post::find($post2->id)->order);
        $this->assertEquals(1, Post::find($post->id)->order);

        $response = $this->actingAs($user)->json('PATCH', 'api/posts/' . $post->id, [
            'order' => 2
        ]);

        $this->assertEquals(3, Post::find($post4->id)->order);
        $this->assertEquals(2, Post::find($post->id)->order);
        $this->assertEquals(1, Post::find($post2->id)->order);

        $response = $this->actingAs($user)->json('PATCH', 'api/posts/' . $post->id, [
            'order' => 3
        ]);

        $this->assertEquals(3, Post::find($post->id)->order);
        $this->assertEquals(2, Post::find($post4->id)->order);
        $this->assertEquals(1, Post::find($post2->id)->order);
    }

    public function testDeletePost()
    {
        $user = User::factory()->create();
        $collection = Collection::factory()->create([
            'user_id' => $user->id
        ]);

        $post = Post::factory()->create([
            'collection_id' => $collection->id,
            'user_id'       => $user->id
        ]);
        $response = $this->actingAs($user)->json('DELETE', 'api/posts/' . $post->id);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->status());
        $this->assertTrue(Post::onlyTrashed()->find($post->id)->trashed());

        $post2 = Post::factory()->create([
            'collection_id' => $collection->id,
            'user_id'       => $user->id
        ]);
        $response = $this->actingAs($user)->json('PATCH', 'api/posts/' . $post2->id, [
            'is_archived' => true
        ]);
        $this->assertTrue(Post::onlyTrashed()->find($post2->id)->trashed());
    }

    public function testDeleteAndRestorePost()
    {

        $user = User::factory()->create();
        $collection = Collection::factory()->create([
            'user_id' => $user->id
        ]);

        // 4 3 -2- 1      --> 3 2 1   --> 4 3 2 1

        array_map(function ($order) use ($collection, $user) {
            Post::factory()->create([
                'collection_id' => $collection->id,
                'user_id'       => $user->id,
                'order'         => $order
            ]);
        }, [1, 2, 3, 4]);

        // delete
        $postId = Post::where('collection_id', $collection->id)
            ->where('order', 2)->first()->id;

        $response = $this->actingAs($user)->json('DELETE', 'api/posts/' . $postId);

        $posts = Post::where('collection_id', $collection->id)
            ->orderBy('order', 'desc')->get();

        $this->assertTrue(Post::onlyTrashed()->find($postId)->trashed());
        $this->assertEquals(3, $posts->count());
        $this->assertEquals(
            [3, 2, 1],
            [$posts[0]->order, $posts[1]->order, $posts[2]->order]
        );

        // restore
        $response = $this->actingAs($user)->json('PATCH', 'api/posts/' . $postId, [
            'is_archived' => false
        ]);

        $posts = Post::where('collection_id', $collection->id)
            ->orderBy('order', 'desc')->get();

        $this->assertNotNull(Post::find($postId));
        $this->assertEquals(
            [4, 3, 2, 1],
            [$posts[0]->order, $posts[1]->order, $posts[2]->order, $posts[3]->order]
        );
    }

    public function testDeleteAndRestoreFirstPost()
    {

        $user = User::factory()->create();
        $collection = Collection::factory()->create([
            'user_id' => $user->id
        ]);

        // -3- 2 1        --> 2 1     --> 3 2 1

        array_map(function ($order) use ($collection, $user) {
            Post::factory()->create([
                'collection_id' => $collection->id,
                'user_id'       => $user->id,
                'order'         => $order
            ]);
        }, [1, 2, 3]);

        // delete
        $postId = Post::where('collection_id', $collection->id)
            ->where('order', 3)->first()->id;

        $response = $this->actingAs($user)->json('DELETE', 'api/posts/' . $postId);

        $posts = Post::where('collection_id', $collection->id)
            ->orderBy('order', 'desc')->get();

        $this->assertTrue(Post::onlyTrashed()->find($postId)->trashed());
        $this->assertEquals(2, $posts->count());
        $this->assertEquals(
            [2, 1],
            [$posts[0]->order, $posts[1]->order]
        );

        // restore
        $response = $this->actingAs($user)->json('PATCH', 'api/posts/' . $postId, [
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

        $user = User::factory()->create();
        $collection = Collection::factory()->create([
            'user_id' => $user->id
        ]);

        // -5- -4- 3 2 1  --> 3 2 1   --> 4 3 2 1

        array_map(
            function ($order) use ($collection, $user) {
                Post::factory()->create([
                    'collection_id' => $collection->id,
                    'user_id'       => $user->id,
                    'order'         => $order
                ]);
            },
            [1, 2, 3, 4, 5]
        );

        // delete
        $postId = Post::where('collection_id', $collection->id)
            ->where('order', 5)->first()->id;
        $postId2 = Post::where('collection_id', $collection->id)
            ->where('order', 4)->first()->id;

        $response = $this->actingAs($user)->json('DELETE', 'api/posts/' . $postId);
        $response = $this->actingAs($user)->json('DELETE', 'api/posts/' . $postId2);

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
        $response = $this->actingAs($user)->json('PATCH', 'api/posts/' . $postId, [
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
        $user = User::factory()->create();
        $collection = Collection::factory()->create();
        $collection2 = Collection::factory()->create();

        $post = Post::factory()->create([
            'collection_id' => $collection->id,
            'order'         => 1
        ]);
        $post2 = Post::factory()->create([
            'collection_id' => $collection->id,
            'order'         => 2
        ]);
        $post3 = Post::factory()->create([
            'collection_id' => $collection->id,
            'order'         => 3
        ]);
        $post4 = Post::factory()->create([
            'collection_id' => $collection2->id,
            'order'         => 1
        ]);

        $response = $this->actingAs($user)->json('PATCH', 'api/posts/' . $post->id, [
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

    public function testChangeCollectionWithoutTag()
    {
        $user = User::factory()->create();
        $collection = Collection::factory()->create();
        $collection2 = Collection::factory()->create();

        $content = 'https://github.com';

        $response = $this->actingAs($user)->json('POST', 'api/posts', [
            'content'       => $content,
            'collection_id' => $collection->id
        ]);

        $this->assertEquals(201, $response->status());
        $post = $response->getData()->data;

        $response = $this->actingAs($user)->json('PATCH', 'api/posts/' . $post->id, [
            'collection_id'    => $collection2->id,
            'is_uncategorized' => false,
            'tags'             => [],
        ]);

        $this->assertEquals(200, $response->status());
        $data = $response->getData()->data;
        $this->assertEquals($collection2->id, $data->collection_id);
    }

    public function testCreatePostWithDifferentCollections()
    {

        $user = User::factory()->create();
        $collection = Collection::factory()->create();

        $posts = [
            [
                'content'       => 'foo bar',
                'collection_id' => $collection->id
            ],
            [
                'content'          => 'foo bar',
                'is_uncategorized' => true
            ],
            [
                'content'          => 'foo bar',
                'is_uncategorized' => false
            ],
            [
                'content' => 'foo bar'
            ]
        ];

        foreach ($posts as &$post) {
            $response = $this->actingAs($user)->json('POST', 'api/posts', $post);
            $this->assertEquals(201, $response->status());
            $data = $response->getData()->data;
            $this->assertNotEquals($data, null);
        }
    }

    public function testCreatePostWithoutAuth()
    {
        $post = [
            'content' => 'foo bar'
        ];
        $response = $this->json('POST', 'api/posts', $post);
        $this->assertEquals(401, $response->status());
    }

    public function testCreatePostWithDifferentUsers()
    {
        $post = [
            'content' => 'foo bar'
        ];
        $user = User::factory()->create();
        $user2 = User::factory()->create();

        $response = $this->actingAs($user)->json('POST', 'api/posts', $post);
        $this->assertEquals(201, $response->status());

        $response = $this->actingAs($user2)->json('POST', 'api/posts', $post);
        $this->assertEquals(201, $response->status());

        $response = $this->actingAs($user2)->json('POST', 'api/posts', [
            'content' => 'foo bar',
            'user_id' => $user->id
        ]);
        $this->assertEquals(201, $response->status());
        $this->assertLessThan(
            Post::where('user_id', $user2->id)->count(),
            Post::where('user_id', $user->id)->count()
        );
    }

    public function testCreatePostWithNotOwnedCollection()
    {
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $collection = Collection::factory()->create([
            'user_id' => $user->id
        ]);

        $response = $this->actingAs($user2)->json('POST', 'api/posts', [
            'content'       => 'foo bar',
            'collection_id' => $collection->id
        ]);
        $this->assertEquals(403, $response->status());
    }

    public function testGetPostWithOffset()
    {
        $user = User::factory()->create();
        $post1 = Post::factory()->create([
            'order' => 1
        ]);
        $post2 = Post::factory()->create([
            'order' => 2
        ]);
        $post3 = Post::factory()->create([
            'order' => 3
        ]);
        $post4 = Post::factory()->create([
            'order' => 4
        ]);

        $response = $this->actingAs($user)->json('GET', 'api/posts?offset=2&limit=2');
        $data = $response->getData()->data;
        $this->assertEquals(count($data), 2);
        $this->assertEquals($data[0]->order, 2);
    }

    public function testGetPostWithSeekPagination()
    {
        $user = User::factory()->create();
        $post4 = Post::factory()->create([
            'order' => 4
        ]);
        $post3 = Post::factory()->create([
            'order' => 3
        ]);
        $post2 = Post::factory()->create([
            'order' => 2
        ]);
        $post1 = Post::factory()->create([
            'order' => 1
        ]);


        $response = $this->actingAs($user)->json('GET', 'api/posts?after_id=' . $post3->id . '&limit=2');
        $data = $response->getData()->data;
        $this->assertEquals(count($data), 2);
        $this->assertEquals($data[0]->id, $post2->id);
        $this->assertEquals($data[1]->id, $post1->id);
    }

    public function testGetPostWithoutAuth()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $response = $this->json('GET', 'api/posts');
        $this->assertEquals(401, $response->status());
        $response = $this->json('GET', 'api/posts/' . $post->id);
        $this->assertEquals(401, $response->status());
    }

    public function testGetPostFromOwnerOnly()
    {
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $user->id
        ]);
        Post::factory()->create([
            'user_id' => $user->id
        ]);
        Post::factory()->create([
            'user_id' => $user2->id
        ]);

        $response = $this->actingAs($user)->json('GET', 'api/posts');
        $amountOfPosts = count($response->getData()->data);
        $this->assertEquals(200, $response->status());
        $response = $this->actingAs($user2)->json('GET', 'api/posts');
        $amountOfPosts2 = count($response->getData()->data);
        $this->assertEquals(200, $response->status());
        $this->assertLessThan($amountOfPosts, $amountOfPosts2);

        $response = $this->actingAs($user2)->json('GET', 'api/posts/' . $post->id);
        $this->assertEquals(403, $response->status());
    }

    public function testSearchInsideACollection()
    {
        $user = User::factory()->create();
        $collection = Collection::factory()->create();
        $collection2 = Collection::factory()->create();
        $post1 = Post::factory()->create([
            'content'       => 'my Test is so great',
            'collection_id' => $collection->id
        ]);
        $post2 = Post::factory()->create([
            'content'       => 'my test is so great',
            'collection_id' => $collection->id
        ]);
        $post3 = Post::factory()->create([
            'content'       => 'my tESt is so great',
            'collection_id' => $collection->id
        ]);
        $post4 = Post::factory()->create([
            'content'       => 'my test is so great',
            'collection_id' => $collection2->id
        ]);


        $response = $this->actingAs($user)->json('GET', 'api/posts', [
            'filter'        => 'test',
            'collection_id' => $collection->id
        ]);
        $data = $response->getData()->data;
        $this->assertEquals(count($data), 3);
        $post_ids = array_map(function ($post) {
            return $post->id;
        }, $data);
        $this->assertContains($post1->id, $post_ids);
        $this->assertContains($post2->id, $post_ids);
        $this->assertContains($post3->id, $post_ids);
        $this->assertNotContains($post4->id, $post_ids);
    }

    public function testCreatePostWithAndWithoutStorage()
    {
        Config::set('benotes.use_filesystem', false);
        $this->assertFalse(config('benotes.use_filesystem'));

        $user = User::factory()->create();
        $collection = Collection::factory()->create();

        $response = $this->actingAs($user)->json('POST', 'api/posts', [
            'content'       => 'https://nyt.com',
            'collection_id' => $collection->id
        ]);

        $this->assertEquals(201, $response->status());
        $data = $response->getData()->data;
        $this->assertEquals('link', $data->type);
        $this->assertStringStartsNotWith(url('/storage/thumbnails/thumbnail_'), $data->image_path);
        $this->assertStringStartsWith('https://', $data->image_path);

        // with storage
        Config::set('benotes.use_filesystem', true);
        $this->assertTrue(config('benotes.use_filesystem'));

        $response = $this->actingAs($user)->json('POST', 'api/posts', [
            'content'       => 'https://nyt.com',
            'collection_id' => $collection->id
        ]);

        $this->assertEquals(201, $response->status());
        $data = $response->getData()->data;
        $this->assertEquals('link', $data->type);
        $this->assertStringStartsWith(url('/storage/thumbnails/thumbnail_'), $data->image_path);
        $this->assertStringStartsNotWith('https://', $data->image_path);
    }
}
