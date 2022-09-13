[![alt text](https://travis-ci.org/prismicio/php-kit.png?branch=master "Travis build")](https://travis-ci.org/prismicio/php-kit)

# PHP development kit for Prismic

## Getting started

### Install the kit for your project

First of all, install [apc](http://www.php.net/manual/en/ref.apc.php) to have the default built-in cache support.

Install with [Composer](https://getcomposer.org/doc/00-intro.md):

```bash
$ composer require prismic/php-sdk
```

### Usage

Include the dependency:

```php
<?php
include_once __DIR__.'/../vendor/autoload.php';

use Prismic\Api;
```

Then call the API:

```php
<?php
$api = Api::get('https://your-repo-name.prismic.io/api/v2');
$doc = $api->getByUID('get-started');
```

This kit supports PHP version >= 7.1.

Because of a dependency on the event dispatcher, this library is compatible with Symfony version 2.8 and higher.
For Symfony 2.7 projects, use version 2.0.3.

### DOM helpers usage

In these examples we have a $doc variable corresponding to the fetched Prismic document.
We also have a $linkResolver variable containing the functional link resolver, [read our docs to learn more about Link Resolving](https://prismic.io/docs/php/beyond-the-api/link-resolving).

#### Link

```php
<?php
use Prismic\Dom\Link;

echo Link::asUrl($doc->data->link, $linkResolver);
```

#### Rich Text

```php
<?php
use Prismic\Dom\RichText;

echo RichText::asText($doc->data->title);
echo RichText::asHtml($doc->data->description, $linkResolver);
```

#### Date

```php
<?php
use Prismic\Dom\Date;

$date = Date::asDate($doc->data->date);
echo $date->format('Y-m-d H:i:s');
```

## More information

-   [Developer docs](./docs)
-   [PHP Quickstart tutorial](https://prismic.io/quickstart#?lang=php)
-   [PHPDoc](https://prismicio.github.io/php-kit)
-   [Changelog](https://github.com/prismicio/php-kit/releases)

## Install the kit locally

Clone this GitHub repository, then [install Composer](https://getcomposer.org/doc/00-intro.md) if you haven't, and run:

```bash
$ composer install
```

## Tests

Please write tests for any bugfix or new feature.

If you find existing code that is not optimally tested and wish to make it better, we really appreciate it; but you should document it on its own branch and its own pull request.

Tests are run by running the command:

```bash
$ ./vendor/bin/phpunit
```

Some of the kit's tests check stuff that are built on top of APC and need APC to work from the command line. If you've installed and enabled APC, and your cache tests don't pass:

-   check if your APC is enabled for your command line, by running `php -i | grep apc`; if no output is displayed, then maybe the APC extension you installed and enabled is only enabled in apache but not for your command line. Check how your OS works to make that happen, and if it involves changing a php.ini file, make sure it's the right php.ini (you might have one for apache, and one for the command line)
-   if APC is enabled for the command line, and yet the tests still fail, make sure your `apc.enable_cli` (which you see in the output of `php -i | grep apc`) is 'On'. If it's not, add this at the end of your php.ini: `apc.enable_cli = 1`. Make sure it's the right php.ini (you might have one for apache, and one for the command line)

## License

This software is licensed under the Apache 2 license, quoted below.

Copyright 2018 Prismic (https://prismic.io).

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this project except in compliance with the License. You may obtain a copy of the License at http://www.apache.org/licenses/LICENSE-2.0.

Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License.
