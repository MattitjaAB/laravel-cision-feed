<?php

use Mattitja\Cision\Cision;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

it('can fetch press releases from the RSS feed', function () {
    Http::fake([
        'news.cision.com/*' => Http::response(file_get_contents(__DIR__.'/Fixtures/rss-press.xml')),
    ]);

    $cision = new Cision('test-company');
    $data = $cision->press();

    expect($data)->toBeArray();
    expect($data)->toHaveKey('items');
    expect($data['items'])->not->toBeEmpty();

    $item = $data['items'][0];
    expect($item)->toHaveKeys(['guid', 'title', 'description', 'link', 'created_at']);
});

it('can fetch and parse full article content', function () {
    Http::fake([
        'news.cision.com/*' => Http::response(file_get_contents(__DIR__.'/Fixtures/article.html')),
    ]);

    $cision = new Cision();
    $content = $cision->fetchContent('https://news.cision.com/se/example/r/test,c123456');

    expect($content)->toHaveKeys(['header', 'published_at', 'content']);
    expect($content['header'])->not->toBeEmpty();
    expect($content['content'])->toContain('<p');
});
