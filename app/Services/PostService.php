<?php

namespace App\Services;

use App\Jobs\ProcessMissingThumbnail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Intervention\Image\Exception\ImageException;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use HeadlessChromium\BrowserFactory;

use App\Models\Post;
use App\Models\Collection;
use App\Models\User;
use App\Models\PostTag;
use ColorThief\ColorThief;

class PostService
{

    public function all(
        int $collection_id = -1,
        bool $is_uncategorized = false,
        int $tag_id = -1,
        bool $withTags = false,
        string $filter = '',
        int $auth_type = User::UNAUTHORIZED_USER,
        bool $is_archived = false,
        Post $after_post = null,
        int $offset = -1,
        int $limit = 50
    ): \Illuminate\Database\Eloquent\Collection {
        $posts = new Post;

        if ($withTags) {
            $posts = $posts->with('tags:id,name');
        }

        if ($tag_id > 0) {
            $post_ids = PostTag::where('tag_id', $tag_id)->select('post_id')->get();
            $posts = $posts->whereIn('id', $post_ids);
        }

        if ($auth_type === User::API_USER) {
            if ($collection_id > 0 && $filter !== '') {
                $collection_ids = Collection::with('children')
                    ->where('parent_id', $collection_id)
                    ->pluck('id')->all();
                $collection_ids[] = $collection_id;
                $posts = $posts
                    ->whereIn('collection_id', $collection_ids)
                    ->where('user_id', '=', Auth::user()->id);
            } else if ($collection_id > 0 || $is_uncategorized === true) {
                $collection_id = Collection::getCollectionId(
                    $collection_id,
                    $is_uncategorized
                );
                $posts = $posts->where([
                    ['collection_id', '=', $collection_id],
                    ['user_id', '=', Auth::user()->id]
                ]);
            } else {
                $posts = $posts->where('user_id', Auth::user()->id);
            }
        } else if ($auth_type === User::SHARE_USER) {
            $share = Auth::guard('share')->user();
            $posts = $posts->where([
                'collection_id' => $share->collection_id,
                'user_id'       => $share->created_by
            ]);
        }

        if ($filter !== '' && $auth_type === User::API_USER) {
            $filter = strtolower($filter);
            $posts = $posts->where(function ($query) use ($filter) {
                $query
                    ->whereRaw('LOWER(title) LIKE ?', "%{$filter}%")
                    ->orWhereRaw('LOWER(content) LIKE ?', "%{$filter}%");
            });
        }

        if ($after_post !== null) {
            $posts = $posts->where('order', '<', $after_post->order);
        } else if ($offset > 0) {
            $posts = $posts->offset($offset);
        }

        if ($limit > 0) {
            $posts = $posts->limit($limit);
        }

        if ($is_archived) {
            return $posts
                ->onlyTrashed()
                ->orderBy('deleted_at', 'desc')->get();
        }

        return $posts->orderBy('order', 'desc')->get();
    }

    public function store($title, $content, $collection_id, $description, $tags, $user_id): Post
    {
        $content = $this->sanitize($content);
        $info = $this->computePostData($title, $content, $description);

        $attributes = array_merge([
            'title'         => $title,
            'content'       => $content,
            'collection_id' => $collection_id,
            'description'   => $description,
            'user_id'       => $user_id
        ], $info);

        $attributes['order'] = Post::where('collection_id', Collection::getCollectionId($collection_id))
            ->max('order') + 1;

        $post = Post::create($attributes);

        if ($info['type'] === Post::POST_TYPE_LINK) {
            $this->saveImage($info['image_path'], $post);
        }
        if (isset($tags)) {
            $this->saveTags($post->id, $tags);
        }

        $post->tags = $post->tags()->get();
        return $post;
    }

    public function delete(Post $post): void
    {
        Post::where('collection_id', $post->collection_id)
            ->where('order', '>', $post->order)
            ->decrement('order');

        $post->delete();
    }

    public function restore(Post $post): Post
    {
        $maxOrder = Post::where('collection_id', $post->collection_id)->max('order');
        if ($post->order <= $maxOrder) {
            Post::where('collection_id', $post->collection_id)
                ->where('order', '>=', $post->order)
                ->increment('order');
        } else {
            $post->order = $maxOrder + 1;
        }
        $post->restore();
        return $post;
    }

    public function computePostData(string $title = null, string $content, string $description = null)
    {
        // more explicit: https?(:\/\/)((\w|-)+\.)+(\w+)(\/\w+)*(\?)?(\w=\w+)?(&\w=\w+)*
        preg_match_all('/(https?:\/\/)((\S+?\.|localhost:)\S+?)(?=\s|<|"|$)/', $content, $matches);
        $matches = $matches[0];
        $info = null;
        if (count($matches) > 0) {
            $info = $this->getInfo($matches[0]);
        }

        if (!empty($title)) {
            unset($info['title']);
        }
        if (!empty($description)) {
            unset($info['description']);
        }

        $stripped_content = strip_tags($content);
        if (empty($matches)) {
            $info['type'] = Post::POST_TYPE_TEXT;
        } else if (strlen($stripped_content) > strlen($matches[0])) { // contains more than just a link
            $info['type'] = Post::POST_TYPE_TEXT;
        } else if ($stripped_content != $matches[0]) {
            $info['type'] = Post::POST_TYPE_TEXT;
        } else {
            $info['type'] = Post::POST_TYPE_LINK;
        }

        return $info;
    }

    public function sanitize(string $str)
    {
        return strip_tags($str, '<a><strong><b><em><i><s><p><h1><h2><h3><h4><h5>' .
            '<pre><br><hr><blockquote><ul><li><ol><code><img><unfurling-link>');
    }

    public function getInfo(string $url, $act_as_bot = false)
    {
        $base_url = parse_url($url);
        $base_url = $base_url['scheme'] . '://' . $base_url['host'];

        $useragent = $act_as_bot ? 'Googlebot/2.1 (+http://www.google.com/bot.html)' :
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: text/html'));
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, config('benotes.curl_timeout'));
        $html = curl_exec($ch);
        $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        if (empty($html) && !$act_as_bot) {
            return $this->getInfo($url, true);
        }

        if (empty($html) || !Str::contains($content_type, 'text/html')) {
            return [
                'url'         => substr($url, 0, 512),
                'base_url'    => substr($base_url, 0, 255),
                'title'       => substr($url, 0, 255),
                'description' => null,
                'color'       => null,
                'image_path'  => null,
            ];
        }

        $document = new \DOMDocument();
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
        @$document->loadHTML($html);
        $titles = $document->getElementsByTagName('title');
        if (count($titles) > 0) {
            $title = trim($titles->item(0)->nodeValue);
        } else {
            $title = $base_url;
        }
        $metas = $document->getElementsByTagName('meta');

        for ($i = 0; $i < $metas->length; $i++) {
            $meta = $metas->item($i);
            if ($meta->getAttribute('name') === 'description') {
                $description = trim($meta->getAttribute('content'));
            } else if ($meta->getAttribute('name') === 'theme-color') {
                $color = $meta->getAttribute('content');
            } else if ($meta->getAttribute('property') === 'og:image') {
                if ($meta->getAttribute('content') != '') {
                    $image_path = $meta->getAttribute('content');
                    if (Str::startsWith($image_path, parse_url($image_path)['path'])) {
                        $image_path = $this->composeImagePath($image_path, $base_url, $url);
                    }
                }
            }
        }

        if (empty($color)) {
            $color = $this->getDominantColor($base_url);
        }

        if (empty($description) && empty($image_path) & !$act_as_bot) {
            // try again with bot as useragent
            return $this->getInfo($url, true);
        }

        return [
            'url'         => substr($url, 0, 512),
            'base_url'    => substr($base_url, 0, 255),
            'title'       => substr($title, 0, 255),
            'description' => $description ?? null,
            'color'       => $color ?? null,
            'image_path'  => $image_path ?? null,
        ];
    }

    public function getDominantColor(string $base_url)
    {
        if (!extension_loaded('gd') & !extension_loaded('imagick') & !extension_loaded('gmagick')) {
            return null;
        }

        $host = parse_url($base_url)['host'];
        try {
            $rgb = ColorThief::getColor('https://www.google.com/s2/favicons?domain=' . $host);
        } catch (\RuntimeException $e) {
            return '#FFF';
        }
        $hex = sprintf("#%02x%02x%02x", $rgb[0], $rgb[1], $rgb[2]);
        return $hex;
    }

    public function saveTags(int $post_id, array $tag_ids)
    {
        $old_tags_obj = PostTag::select('tag_id')->where('post_id', $post_id)->get();
        $old_tags = [];
        foreach ($old_tags_obj as $old_tag) {
            $old_tags[] = $old_tag->tag_id;
        }

        foreach ($tag_ids as $tag_id) {
            if (!in_array($tag_id, $old_tags)) {
                PostTag::create([
                    'post_id' => $post_id,
                    'tag_id'  => $tag_id
                ]);
            }
        }
        PostTag::whereNotIn('tag_id', $tag_ids)->where('post_id', $post_id)->delete();
    }

    public function saveImage($image_path, Post $post)
    {

        if (empty($image_path)) {
            ProcessMissingThumbnail::dispatchIf(config('benotes.generate_missing_thumbnails'), $post);
            return;
        }

        if (config('benotes.use_filesystem') == false) {
            $post->image_path = $image_path;
            $post->save();
            return;
        }

        try {
            $image = Image::make($image_path);
        } catch (ImageException $e) {
            Log::notice('Image could not be created');
        }
        if (!isset($image)) {
            return;
        }

        $filename = $this->generateThumbnailFilename($image_path, $post->id);
        $image = $image->fit(400, 210)->limitColors(255);
        Storage::put('thumbnails/' . $filename, $image->stream());

        $post->image_path = $filename;
        $post->save();
    }

    public function crawlWithChrome(string $filename, string $path, string $url, int $postId)
    {
        $imagePath = $path;
        $width = 400;
        $height = 210;
        // use googlebot in order to avoid, among others, cookie consensus banners
        $useragent = 'Googlebot/2.1 (+http://www.google.com/bot.html)';
        $browser = config('benotes.browser') === 'chromium' ? 'chromium-browser' : 'google-chrome';

        $factory = new BrowserFactory($browser);
        $browser = $factory->createBrowser([
            'noSandbox'   => true,
            'keepAlive'   => true,
            'userAgent'   => $useragent,
            'customFlags' => [
                '--disable-dev-shm-usage',
                '--disable-gpu'
            ],
            'debugLogger' => config('app.debug') ? storage_path('logs/browser.log') : null
        ]);

        try {
            $page = $browser->createPage();
            $page->navigate($url)->waitForNavigation();
            sleep(3); // in order to make sure that the site is reallly loaded

            $title = $page->dom()->querySelector('title')->getText();
            $descriptionEl = $page->dom()->querySelector('head meta[name=description]');
            $description = $descriptionEl ? $descriptionEl->getAttribute('content') : null;
            $imageEl = $page->dom()->querySelector('head meta[property=\'og:image\']');
            $imagePathOG = $imageEl ? $imageEl->getAttribute('content') : null;

            $post = Post::find($postId);

            if (!empty($title) && $title !== $post->title) {
                $post->title = $title;
                $post->save();
            }
            if (!empty($description) && empty($post->description)) {
                $post->description = $description;
                $post->save();
            }

            if (!empty($imagePathOG)) {
                // if crawling the website with chromium reveals an already
                // existing thumbnail, use it instead
                $imagePath = $imagePathOG;
            } else {
                $page->screenshot()->saveToFile($imagePath);
            }

            // temporally store the image
            $image = Image::make($imagePath);
            if (!$image) {
                return;
            }
            $image = $image->fit($width, $height);
            Storage::put('thumbnails/' . $filename, $image->stream());
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::debug('Attempt to create thumbnail failed');
        } finally {
            $browser->close();
        }
    }

    public function generateThumbnailFilename($name, $id)
    {
        return 'thumbnail_' . md5($name) . '_' . $id . '.jpg';
    }

    public function getThumbnailPath($filename)
    {
        return storage_path('app/public/thumbnails/' . $filename);
    }
    public function boolValue($value = null): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    private function composeImagePath(string $image_path, string $base_url, string $url): string
    {
        if (str_starts_with($image_path, './')) {
            return $url . Str::replaceFirst('./', '/', $image_path);
        } else if (str_starts_with($image_path, '../')) {
            return preg_replace('/\/\w+\/$/', '/', $url) .
                Str::replaceFirst('../', '', $image_path);
        }
        return $base_url . $image_path;
    }
}
