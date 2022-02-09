<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

use App\Post;
use App\Collection;
use App\User;
use ColorThief\ColorThief;

class PostService
{

    public function all(int $collection_id = -1,
                        bool $is_uncategorized = false,
                        string $filter = '',
                        int $auth_type = User::UNAUTHORIZED_USER,
                        int $limit = -1) : \Illuminate\Database\Eloquent\Collection
    {

        if ($auth_type === User::API_USER) {
            if ($collection_id > 0 || $is_uncategorized === true) {
                $collection_id = Collection::getCollectionId(
                    $collection_id,
                    $is_uncategorized
                );
                $posts = Post::where([
                    ['collection_id', '=', $collection_id],
                    ['user_id', '=', Auth::user()->id]
                ]);
            } else {
                $posts = Post::where('user_id', Auth::user()->id);
            }
        } else if ($auth_type === User::SHARE_USER) {
            $share = Auth::guard('share')->user();
            $posts = Post::where([
                'collection_id' => $share->collection_id,
                'user_id' => $share->created_by
            ]);
        }

        if ($filter !== '' && $auth_type === User::API_USER) {
            $posts = $posts->where(function ($query) use ($filter) {
                $query->where('title', 'LIKE', "%{$filter}%")
                ->orWhere('content', 'LIKE', "%{$filter}%");
            });
        }

        if ($limit > 0) {
            $posts = $posts->limit($limit);
        }

        return $posts->orderBy('order', 'desc')->get();

    }

    public function computePostData(string $title = null, string $content)
    {
        // more explicit: https?(:\/\/)((\w|-)+\.)+(\w+)(\/\w+)*(\?)?(\w=\w+)?(&\w=\w+)*
        preg_match_all('/(https?:\/\/)(\S+?\.\S+?)(?=\s|<|"|$)/', $content, $matches);
        $matches = $matches[0];
        $info = null;
        if (count($matches) > 0) {
            $info = $this->getInfo($matches[0]);
        }

        if (!empty($title)) {
            unset($info['title']);
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
        '<pre><br><hr><blockquote><ul><li><ol><code><unfurling-link>');
    }

    public function getInfo(string $url, $act_as_bot = false)
    {
        $base_url = parse_url($url);
        $base_url = $base_url['scheme'] . '://' . $base_url['host'];

        $useragent = $act_as_bot ? 'Googlebot/2.1 (+http://www.google.com/bot.html)' :
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.132 Safari/537.36';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, config('benotes.curl_timeout'));
        $html = curl_exec($ch);
        $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        if (!str_contains($content_type, 'text/html')) {
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
                $description = $meta->getAttribute('content');
            } else if ($meta->getAttribute('name') === 'theme-color') {
                $color = $meta->getAttribute('content');
            } else if ($meta->getAttribute('property') === 'og:image') {
                if ($meta->getAttribute('content') != '') {
                    $image_path = $meta->getAttribute('content');
                    $base_image_url = parse_url($image_path);
                    if ($base_image_url['path'] === $image_path) {
                        $image_path = $base_url . $image_path;
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
            'description' => (empty($description)) ? null : $description,
            'color'       => (empty($color)) ? null : $color,
            'image_path'  => (empty($image_path)) ? null : $image_path,
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

    public function saveImage($image_path, Post $post)
    {
        if (empty($image_path)) {
            return;
        }

        if (config('benotes.use_filesystem') == false) {
            $post->image_path = $image_path;
            $post->save();
            return;
        }

        $image = Image::make($image_path);
        if (!$image) {
            return;
        }

        $filename = 'thumbnail_' . md5($image_path) . '_' . $post->id . '.jpg';
        $image = $image->fit(400, 210)->limitColors(255);
        Storage::put('thumbnails/' . $filename, $image->stream());

        $post->image_path = $filename;
        $post->save();
    }

}
