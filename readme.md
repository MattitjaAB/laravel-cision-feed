## How to install
1. Install the package `composer require mathiaspalmqvist/laravel-cision-feed`
2. add the cision company slug to `LARAVEL_CISION_FEED_SLUG` at your env

## Functions

`LaravelCisionFeed::all();`
Returns everyting from the feed.

`LaravelCisionFeed::press();`
Returns all press releases from the feed.

`LaravelCisionFeed::financial();`
Returns all financial reports from the feed.

`LaravelCisionFeed::news();`
Returns all news from the feed.

`LaravelCisionFeed::media();`
Returns all media from the feed.
