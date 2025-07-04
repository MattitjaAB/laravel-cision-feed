<?php

namespace Mattitja\Cision;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Symfony\Component\DomCrawler\Crawler;
use Mattitja\Cision\Support\HtmlCleaner;

class Cision
{
    protected string $slug;

    /**
     * Constructor that accepts a slug identifier for the feed.
     */
    public function __construct(string $slug = null)
    {
        $this->slug = $slug ?? env('LARAVEL_CISION_FEED_SLUG', '');
    }

    public function all(int $page = 1): array|false
    {
        return $this->fetch($page);
    }

    public function press(int $page = 1): array|false
    {
        return $this->fetch($page, 'Press');
    }

    public function financial(int $page = 1): array|false
    {
        return $this->fetch($page, 'Financial');
    }

    public function news(int $page = 1): array|false
    {
        return $this->fetch($page, 'News');
    }

    public function media(int $page = 1): array|false
    {
        return $this->fetch($page, 'Media');
    }

    protected function fetch(int $page = 1, ?string $type = null): array|false
    {
        $response = $this->makeRequest($page, $type);

        if ($response === false || $response->failed()) {
            return false;
        }

        return $this->parseResponse($response->body(), $page);
    }

    /**
     * Fetch and parse the HTML content of a Cision article URL.
     */
    public function fetchContent(string $url): array
    {
        $html = Http::get($url)->body();
        $crawler = new Crawler($html);

        $article = $crawler->filter('article')->first();

        $header = trim($article->filter('h1')->text());
        $publishedAt = $article->filter('time')->attr('datetime');

        $allowedTags = ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'li'];
        $allowedInline = ['strong', 'b', 'em', 'i', 'br'];

        $content = [];

        foreach ($article as $node) {
            foreach ($node->childNodes as $child) {
                $content[] = HtmlCleaner::renderNode($child, $allowedTags, $allowedInline);
            }
        }

        $cleaned = HtmlCleaner::clean(implode("\n", array_filter($content)));

        return [
            'header' => $header,
            'published_at' => $publishedAt,
            'content' => $cleaned,
        ];
    }

    protected function makeRequest(int $page, ?string $type = null): Response|false
    {
        if (empty($this->slug)) {
            return false;
        }

        $query = [
            'n' => $this->slug,
            'pageIx' => $page,
            'format' => 'rss',
        ];

        if ($type) {
            $query['m'] = $type;
        }

        return Http::get('https://news.cision.com/se/ListItems?' . http_build_query($query));
    }

    protected function parseResponse(string $data, int $page): array|false
    {
        if (empty($data)) {
            return false;
        }

        $xml = simplexml_load_string($data, "SimpleXMLElement", LIBXML_NOCDATA);
        if ($xml === false) {
            return false;
        }

        $json = json_encode($xml);
        $rows = json_decode($json, true);

        if (!isset($rows['channel']['item'])) {
            return false;
        }

        $items = array_map(fn ($item) => [
            'guid' => $item['guid'],
            'title' => $item['title'],
            'description' => $item['description'],
            'link' => $item['link'],
            'created_at' => Carbon::parse($item['pubDate'])->addHours(2)->format('Y-m-d H:i:s'),
        ], $rows['channel']['item']);

        return [
            'items' => $items,
            'previous_page' => $page > 1 ? $page - 1 : false,
            'next_page' => count($items) === 24 ? $page + 1 : false,
        ];
    }
}
