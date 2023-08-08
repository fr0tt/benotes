<?php

namespace App\Helpers;

use App\Models\Collection;
use App\Services\PostService;

/*
 * This file is heavily inspired by:
 * 
 * Original: Generic Netscape bookmark parser by https://github.com/kafene
 * Source: https://github.com/kafene/netscape-bookmark-parser/blob/master/NetscapeBookmarkParser.php
 * 
 * The MIT License (MIT)
 * 
 * Copyright (c) 2015-2016 Kafene and contributors
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 **/

class NetscapeBookmarkDecoder
{
    protected $items;
    private PostService $postService;
    private $userId;

    const TRUE_PATTERN = 'y|yes|on|checked|ok|1|true|array|\+|okay|yes|t|one';
    const FALSE_PATTERN = 'n|no|off|empty|null|false|nil|0|-|exit|die|neg|f|zero|void';

    /**
     *
     * @param $userId
     */
    public function __construct(int $userId)
    {
        $this->postService = new PostService();
        $this->userId = $userId;
    }

    /**
     * Parses a Netscape bookmark file
     *
     * @param string $filename Bookmark file to parse
     * @param int $collectionId
     *
     * @return array An associative array containing parsed links
     */
    public function parseFile(string $filename, int $collectionId): array
    {
        return $this->parseString(file_get_contents($filename), $collectionId);
    }

    /**
     * 
     * @param string $bookmarkString String containing Netscape bookmarks
     *
     * @return array An associative array containing parsed links
     */
    public function parseString(string $bookmarkString, int $collectionId): array
    {
        $i = 0;
        $parentsIds = array();

        $lines = explode("\n", $this->sanitizeString($bookmarkString));

        foreach ($lines as $line_no => $line) {
            if (preg_match('/^<h\d.*>(.*)<\/h\d>/i', $line, $m1)) {
                // heading matched
                $collection = $this->createCollection($m1[1], $collectionId);
                array_push($parentsIds, $collection->parent_id);
                $collectionId = $collection->id;
                continue;
            } else if (preg_match('/^<\/DL>/i', $line)) {
                // </DL> matched: stop using heading value
                $collectionId = array_pop($parentsIds);
                continue;
            }

            if (preg_match('/<a/i', $line, $m2)) {

                $this->items[$i]['collection_id'] = $collectionId;

                if (preg_match('/href="(.*?)"/i', $line, $m3)) {
                    $this->items[$i]['uri'] = $m3[1];
                } else {
                    $this->items[$i]['uri'] = '';
                }

                if (preg_match('/<a.*>(.*?)<\/a>/i', $line, $m4)) {
                    $this->items[$i]['title'] = $m4[1];
                } else {
                    $this->items[$i]['title'] = 'untitled';
                }

                if (preg_match('/note="(.*?)"<\/a>/i', $line, $m5)) {
                    $this->items[$i]['note'] = $m5[1];
                } elseif (preg_match('/<dd>(.*?)$/i', $line, $m6)) {
                    $this->items[$i]['note'] = str_replace('<br>', "\n", $m6[1]);
                } else {
                    $this->items[$i]['note'] = '';
                }

                $tags = array();

                if (preg_match('/(tags?|labels?|folders?)="(.*?)"/i', $line, $m7)) {
                    $tags = array_merge(
                        $tags,
                        explode(' ', strtr($m7[2], ',', ' '))
                    );
                }
                $this->items[$i]['tags'] = $tags;

                if (preg_match('/add_date="(.*?)"/i', $line, $m8)) {
                    $this->items[$i]['created_at'] = $this->parseDate($m8[1]);
                } else {
                    $this->items[$i]['created_at'] = time();
                }

                if (preg_match('/last_modified="(.*?)"/i', $line, $m9)) {
                    $this->items[$i]['updated_at'] = $this->parseDate($m9[1]);
                } else {
                    $this->items[$i]['updated_at'] = time();
                }

                $this->createPost(
                    $this->items[$i]['title'],
                    $this->items[$i]['collection_id'],
                    $this->items[$i]['uri'],
                    $this->items[$i]['note'],
                    $this->items[$i]['tags']
                );

                $i++;
            }
        }
        ksort($this->items);
        return $this->items;
    }

    /**
     * Parses a formatted date
     *
     * @see http://php.net/manual/en/datetime.formats.compound.php
     * @see http://php.net/manual/en/function.strtotime.php
     *
     * @param string $date formatted date
     *
     * @return int Unix timestamp corresponding to a successfully parsed date,
     *             else current date and time
     */
    public static function parseDate($date): int
    {
        if (strtotime('@' . $date)) {
            // Unix timestamp
            return strtotime('@' . $date);
        } else if (strtotime($date)) {
            // attempt to parse a known compound date/time format
            return strtotime($date);
        }
        // current date & time
        return time();
    }

    /**
     * Parses the value of a supposedly boolean attribute
     *
     * @param string $value   Attribute value to evaluate
     *
     */
    public function parseBoolean($value): bool
    {
        if (!$value) {
            return false;
        }
        if (!is_string($value)) {
            return true;
        }

        if (preg_match("/^(" . self::TRUE_PATTERN . ")$/i", $value)) {
            return true;
        }
        if (preg_match("/^(" . self::FALSE_PATTERN . ")$/i", $value)) {
            return false;
        }
        return false;
    }

    /**
     * Sanitizes the content of a string containing Netscape bookmarks
     *
     * This removes:
     * - comment blocks
     * - metadata: DOCTYPE, H1, META, TITLE
     * - extra newlines, trailing spaces and tabs
     *
     * @param string $bookmarkString Original bookmark string
     *
     * @return string Sanitized bookmark string
     */

    public static function sanitizeString($bookmarkString): string
    {
        $sanitized = $bookmarkString;

        // trim comments
        $sanitized = preg_replace('@<!--.*-->@mis', '', $sanitized);

        // trim unused metadata
        $sanitized = preg_replace('@(<!DOCTYPE|<META|<TITLE|<H1|<P).*\n@i', '', $sanitized);

        // trim whitespace
        $sanitized = trim($sanitized);

        // trim carriage returns, replace tabs by a single space
        $sanitized = str_replace(array("\r", "\t"), array('', ' '), $sanitized);

        // convert multiline descriptions to one-line descriptions
        // line feeds are converted to <br>
        $sanitized = preg_replace_callback(
            '@<DD>(.*?)<@mis',
            function ($match) {
                return '<DD>' . str_replace("\n", '<br>', trim($match[1])) . PHP_EOL . '<';
            },
            $sanitized
        );

        // keep one XML element per line to prepare for linear parsing
        $sanitized = preg_replace('@>(\s*?)<@mis', ">\n<", $sanitized);

        // concatenate all information related to the same entry on the same line
        // e.g. <A HREF="...">My Link</A><DD>List<br>- item1<br>- item2
        $sanitized = preg_replace('@\n<br>@mis', "<br>", $sanitized);
        $sanitized = preg_replace('@\n<DD@i', '<DD', $sanitized);

        return $sanitized;
    }

    private function createCollection($name, $parent_id): Collection
    {
        $collection = Collection::create([
            'name'      => $name,
            'parent_id' => $parent_id,
            'user_id'   => $this->userId,
        ]);
        return $collection;
    }

    private function createPost($title, $collectionId, $url, $description, $tags = null)
    {
        $this->postService->store(
            $title,
            $url,
            $collectionId,
            $description,
            count($tags) > 0 ? $tags : null,
            $this->userId,
        );
    }

}
