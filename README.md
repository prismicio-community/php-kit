# Prismic.io PHP SDK

[![Github Actions CI][github-actions-ci-src]][github-actions-ci-href]
[![Total Downloads][packagist-downloads-src]][packagist-downloads-href]
[![Latest Stable Version][packagist-version-src]][packagist-version-href]
[![License][packagist-license-src]][packagist-license-href]

This is the official PHP SDK for Prismic.io, providing a straightforward way to connect to Prismic’s headless API. It is maintained by the community to help developers get started with Prismic in PHP.

-   Offers an easy-to-use interface for basic interactions with Prismic content
-   Covers core features of the Prismic API

### Overview

1. [Getting started](#getting-started)
    1. [Installation](#installation)
    2. [Recommended PHP Extensions](#recommended-php-extensions)
    3. [Basic Usage & API calls](#basic-usage--api-calls)
    4. [DOM Helper](#dom-helper)
2. [More information](#more-information)
3. [Contributing](#contributing)
4. [License](#license)

## Getting started

### Installation

Installation using [Composer](https://getcomposer.org/doc/00-intro.md):

```bash
$ composer require prismic/php-sdk
```

### Recommended PHP Extensions

We recommend installing and enabeling the [APCu](https://www.php.net/manual/en/ref.apcu.php) extension to have the built-in default cache support. Otherwise, you can implement your own cache strategy by extending the `Prismic\Cache\CacheInterface` interface.

### Basic Usage & API calls

If you are not using automatic autoloading, include the Composer autoload file:

```php
<?php
include_once __DIR__.'/../vendor/autoload.php';

use Prismic\Api;
```

Then you can start making your first API calls:

```php
$api = Api::get('https://your-repo-name.prismic.io/api/v2');
$document = $api->getByUID('get-started-type', 'get-started');
```

### DOM Helper

The PHP SDK provides a set of DOM helpers to help you render your Prismic content.

In these examples we have a document variable corresponding to the fetched Prismic document. We also have a $linkResolver variable containing the functional link resolver, [read our docs to learn more about Link Resolving](https://prismic.io/docs/php/beyond-the-api/link-resolving).

#### Link

```php
<?php
use Prismic\Dom\Link;

echo Link::asUrl($document->data->link, $linkResolver);
```

#### Rich Text

```php
<?php
use Prismic\Dom\RichText;

echo RichText::asText($document->data->title);
echo RichText::asHtml($document->data->description, $linkResolver);
```

#### Date

```php
<?php
use Prismic\Dom\Date;

$date = Date::asDate($document->data->date);
echo $date->format('Y-m-d H:i:s');
```

## More information

-   [Developer Documentaiton](./docs)
-   [Generated PHPDoc](https://prismicio-community.github.io/php-kit/)
-   [Changelog](https://github.com/prismicio-community/php-kit/releases)

## Contributing

Whether you're helping us fix bugs, improve the docs, or spread the word, we'd love to have you as part of the Prismic developer community!

**Reporting a bug**: [Open an issue][repo-bug-report] explaining your application's setup and the bug you're encountering.

**Suggesting an improvement**: [Open an issue][repo-feature-request] explaining your improvement or feature so we can discuss and learn more.

**Submitting code changes**: For small fixes, feel free to [open a pull request][repo-pull-requests] with a description of your changes. For large changes, please first [open an issue][repo-feature-request] so we can discuss if and how the changes should be implemented.

For more clarity on this project, check out the detailed [CONTRIBUTING.md](./CONTRIBUTING.md) for our guidelines.

## License

This software is licensed under the [Apache 2 License](https://opensource.org/license/apache-2-0), quoted below:

```plaintext
Copyright 2013-2024 Prismic <contact@prismic.io> (https://prismic.io)

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
```

<!-- Links -->

[repo-bug-report]: https://github.com/prismicio-community/php-kit/issues/new?assignees=&labels=bug&template=bug_report.md&title=
[repo-feature-request]: https://github.com/prismicio-community/php-kit/issues/new?assignees=&labels=enhancement&template=feature_request.md&title=
[repo-pull-requests]: https://github.com/prismicio-community/php-kit/pulls

<!-- Badges -->

[github-actions-ci-src]: https://github.com/prismicio-community/php-kit/workflows/ci/badge.svg
[github-actions-ci-href]: https://github.com/prismicio-community/php-kit/actions?query=workflow%3Aci
[packagist-downloads-src]: https://img.shields.io/packagist/dm/prismic/php-sdk
[packagist-downloads-href]: https://packagist.org/packages/prismicio-community/php-kit
[packagist-version-src]: https://img.shields.io/packagist/v/prismic/php-sdk
[packagist-version-href]: https://packagist.org/packages/prismicio-community/php-kit
[packagist-license-src]: https://img.shields.io/packagist/l/prismic/php-sdk
[packagist-license-href]: https://packagist.org/packages/prismicio-community/php-kit
