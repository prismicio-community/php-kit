[![alt text](https://travis-ci.org/prismicio/php-kit.png?branch=master "Travis build")](https://travis-ci.org/prismicio/php-kit)
[![Code Climate](https://codeclimate.com/github/prismicio/php-kit/badges/gpa.svg)](https://codeclimate.com/github/prismicio/php-kit)
[![Test Coverage](https://codeclimate.com/github/prismicio/php-kit/badges/coverage.svg)](https://codeclimate.com/github/prismicio/php-kit)

## PHP development kit for prismic.io

### Getting started

#### Install the kit for your project

First of all, please install [apc](http://www.php.net/manual/en/ref.apc.php) to have the default build-in cache support.

Now, the best way to install the library in your project is with the composer package manager ([install it](https://getcomposer.org/doc/00-intro.md) if you haven't yet)

Then run this from your project's root in order to add the dependency:

```bash
$ composer require prismic/php-sdk
```

If asked for a version, type in 'dev-master' (unless you want another version):

```
Please provide a version constraint for the prismic/php-sdk requirement: dev-master
```

Usage in your PHP code:

```php
<?php

include_once __DIR__.'/../vendor/autoload.php';

use Prismic\Api;

```

The kit is compatible and tested with PHP5.3 and above.

#### Get started with prismic.io

You can find out [how to get started with prismic.io](https://developers.prismic.io/documentation/UjBaQsuvzdIHvE4D/getting-started) on our [prismic.io developer's portal](https://developers.prismic.io/).

#### Get started using the kit

Also on our [prismic.io developer's portal](https://developers.prismic.io/), on top of our full documentation, you will:
 * get a thorough introduction of [how to use prismic.io kits](https://developers.prismic.io/documentation/UjBe8bGIJ3EKtgBZ/api-documentation#kits-and-helpers), including this one.
 * see [what else is available for PHP](https://developers.prismic.io/technologies/UjBh98uvzeMJvE4q/php): starter projects, examples, ...

### Using the kit

#### Kit's detailed documentation

To get a detailed documentation of the PHP kit's variables and methods, please check out the [prismic.io PHP kit's documentation](http://prismicio.github.io/php-kit/).

#### Specific PHP kit syntax

Thanks to PHP's syntax, this kit contains some mild differences and syntastic sugar over the section of our documentation that tells you [how to use prismic.io kits](https://developers.prismic.io/documentation/UjBe8bGIJ3EKtgBZ/api-documentation#kits-and-helpers) in general (which you should read first). The differences are listed here:

 * Rather that using `Api.form('everything')` to find the form to query on, use the `Api.forms()` method, which returns an array. Your call will therefore look like this: `$api.forms()->everything`
 * The `asHtml()` function takes an object implementing the `LinkResolver` interface as a parameter, which doesn't take the ref into account. If you want to use your ref in your URLs (as you should), you will have to store it globally. A clean way would by building a `Context` object (as discussed in our cross-technology kits and helpers documentation), and store this object globally (this is what is done in our PHP plain starter kit).

Knowing all that, here is typical code written with the PHP kit:

 * A typical API object instantiation looks like this: `Api.get(url)`
 * A typical querying looks like this: `$api->forms()->everything->query('[[:d = at(document.type, "product")]]')->ref($ref)->submit()`
 * A typical fragment manipulation looks like this: `doc->getImageView('article.image', 'icon')->getUrl()`
 * A typical fragment serialization to HTML looks like this: `doc->getStructuredText('article.body')->asHtml($link_resolver)`

### Changelog

Need to see what changed, or to upgrade your kit? We keep our changelog on [this repository's "Releases" tab](https://github.com/prismicio/php-kit/releases).

### Contribute to the kit

Contribution is open to all developer levels, read our "[Contribute to the official kits](https://developers.prismic.io/documentation/UszOeAEAANUlwFpp/contribute-to-the-official-kits)" documentation to learn more.

#### Install the kit locally

Clone this GitHub repository, then [install Composer](https://getcomposer.org/doc/00-intro.md) if you haven't, and run:

```bash
$ composer install
```

#### Test

Please write tests for any bugfix or new feature.

If you find existing code that is not optimally tested and wish to make it better, we really appreciate it; but you should document it on its own branch and its own pull request.

Tests are run by running the command `phpunit`.

Some of the kit's tests check stuff that are built on top of APC and need APC to work from the command line. If you've installed and enabled APC, and your cache tests don't pass:
 * check if your APC is enabled for your command line, by running `php -i | grep apc`; if no output is displayed, then maybe the APC extension you installed and enabled is only enabled in apache but not for your command line. Check how your OS works to make that happen, and if it involves changing a php.ini file, make sure it's the right php.ini (you might have one for apache, and one for the command line)
 * if APC is enabled for the command line, and yet the tests still fail, make sure your `apc.enable_cli` (which you see in the output of  `php -i | grep apc`) is 'On'. If it's not, add this at the end of your php.ini: `apc.enable_cli = 1`. Make sure it's the right php.ini (you might have one for apache, and one for the command line)

#### Documentation

Please document any bugfix or new feature.

If you find existing code that is not optimally documented and wish to make it better, we really appreciate it; but you should document it on its own branch and its own pull request.

For documentation admins: documentation is generated by running the command:

```bash
$ ./vendor/phpdocumentor/phpdocumentor/bin/phpdoc
```

### License

This software is licensed under the Apache 2 license, quoted below.

Copyright 2013 Zengularity (http://www.zengularity.com).

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this project except in compliance with the License. You may obtain a copy of the License at http://www.apache.org/licenses/LICENSE-2.0.

Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License.
