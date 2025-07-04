# Laravel Cision Feed

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mattitjaab/laravel-cision-feed.svg?style=flat-square)](https://packagist.org/packages/mattitjaab/laravel-cision-feed)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/mattitjaab/laravel-cision-feed/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/mattitjaab/laravel-cision-feed/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/mattitjaab/laravel-cision-feed/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/mattitjaab/laravel-cision-feed/actions?query=workflow%3A%22Fix+PHP+code+style+issues%22+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/mattitjaab/laravel-cision-feed.svg?style=flat-square)](https://packagist.org/packages/mattitjaab/laravel-cision-feed)

A Laravel package for retrieving and parsing data from [Cision News](https://news.cision.com/se). Fetch press releases, financial reports, media posts, and structured article content using a simple and expressive API.

---

## Features

- Retrieve Cision RSS feeds by type (Press, News, Financial, Media)
- Parse article pages and extract clean structured HTML
- Automatically handles encoding, formatting, and clean-up of content
- Fully testable and extensible

---

## Installation

```bash
composer require mattitjaab/laravel-cision-feed
```

You may optionally set your Cision slug in your `.env`:

```
LARAVEL_CISION_FEED_SLUG=your-cision-slug
```

---

## Usage

```php
use Mattitja\Cision\Cision;

$cision = new Cision();

// Fetch RSS entries
$items = $cision->press(); // or ->news(), ->financial(), ->media()

// Fetch full content for a specific article
$article = $cision->fetchContent('https://news.cision.com/se/example-company/r/your-article-slug,c1234567');

echo $article['header'];       // Article headline
echo $article['published_at']; // ISO timestamp
echo $article['content'];      // Clean HTML
```

---

## Testing

```bash
composer test
```

---

## Changelog

See [CHANGELOG](CHANGELOG.md) for recent changes.

---

## Contributing

Contributions are welcome! Please see [CONTRIBUTING](CONTRIBUTING.md) for guidelines.

---

## Security

If you discover any security-related issues, please refer to our [security policy](../../security/policy).

---

## Credits

- [Mattitja AB](https://github.com/MattitjaAB)
- [All Contributors](../../contributors)

---

## License

The MIT License (MIT). See [LICENSE](LICENSE.md) for details.
