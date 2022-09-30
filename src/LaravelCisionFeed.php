<?php

namespace Mathiaspalmqvist\LaravelCisionFeed;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class LaravelCisionFeed
{
    /**
     * @param int $page
     * @return array|false
     */
    public static function all(int $page = 1): bool|array
    {
        $request = self::request($page);

        if ($request->failed()) {
            return false;
        }

        return self::parse($request->body());
    }

    /**
     * @param int $page
     * @return array|false
     */
    public static function press(int $page = 1): bool|array
    {
        $request = self::request($page, 'Press');

        if ($request->failed()) {
            return false;
        }

        return self::parse($request->body());
    }

    /**
     * @param int $page
     * @return array|false
     */
    public static function financial(int $page = 1): bool|array
    {
        $request = self::request($page, 'Financial');

        if ($request->failed()) {
            return false;
        }

        return self::parse($request->body());
    }

    /**
     * @param int $page
     * @return array|false
     */
    public static function news(int $page = 1): bool|array
    {
        $request = self::request($page, 'News');

        if ($request->failed()) {
            return false;
        }

        return self::parse($request->body());
    }

    /**
     * @param int $page
     * @return array|false
     */
    public static function media(int $page = 1): bool|array
    {
        $request = self::request($page, 'Media');

        if ($request->failed()) {
            return false;
        }

        return self::parse($request->body());
    }

    /**
     * @param int $page
     * @param string|null $m
     * @return false|\Illuminate\Http\Client\Response
     */
    private static function request(int $page = 1, string $m = null): bool|\Illuminate\Http\Client\Response
    {
        if (env('LARAVEL_CISION_FEED_SLUG') === null) {
            return false;
        }

        $query = [
            'n' => env('LARAVEL_CISION_FEED_SLUG'),
            'pageIx' => $page,
            'format' => 'rss',
        ];

        if ($m) {
            $query['m'] = $m;
        }

        return Http::get('https://news.cision.com/se/ListItems?'.http_build_query($query));
    }

    /**
     * @param string|null $data
     * @return array|false
     */
    private static function parse(string $data = null): bool|array
    {
        if ($data === null) {
            return false;
        }

        $xml = simplexml_load_string($data, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xml);
        $rows = json_decode($json, true);

        if (! isset($rows['channel']['item'])) {
            return false;
        }

        $return = [];

        foreach ($rows['channel']['item'] as $row) {
            $return[] = [
                'guid' => $row['guid'],
                'title' => $row['title'],
                'description' => $row['description'],
                'link' => $row['link'],
                'created_at' => Carbon::parse($row['pubDate'])->addHours(2)->format('Y-m-d H:i:s'),
            ];
        }

        return $return;
    }
}
