[![alt text](https://travis-ci.org/prismicio/php-kit.png?branch=master "Travis build")](https://travis-ci.org/prismicio/php-kit)

## PHP development kit for prismic.io

### Getting started

#### Install the kit for your project

The best way to install the library is with the composer package manager ([install it](https://getcomposer.org/doc/00-intro.md) if you haven't yet)

Then run this from your project's root in order to add the dependency:

```bash
composer require prismic/php-sdk
```

If asked for a version, type in 'dev-master' (unless you want another version):

```bash
Please provide a version constraint for the prismic/php-sdk requirement: dev-master
```

Usage in your PHP code:

```php
<?php

include_once __DIR__.'/../vendor/autoload.php';

use Prismic\Api;

```

#### Get started with prismic.io

You can find out [how to get started with prismic.io](https://developers.prismic.io/documentation/UjBaQsuvzdIHvE4D/getting-started) on our [prismic.io developer's portal](https://developers.prismic.io/).

#### Get started using the kit

Also on our [prismic.io developer's portal](https://developers.prismic.io/), on top of our full documentation, you will:
 * get a thorough introduction of [how to use prismic.io kits](https://developers.prismic.io/documentation/UjBe8bGIJ3EKtgBZ/api-documentation#kits-and-helpers), including this one.
 * see [what else is available for PHP](https://developers.prismic.io/technologies/UjBh98uvzeMJvE4q/php): starter projects, examples, ...


#### Kit's detailed documentation

You can find [the full documentation of the PHP kit here](http://prismicio.github.io/php-kit/).

Thanks to PHP's syntax, this kit contains some mild differences and syntastic sugar over the section of our documentation that tells you [how to use prismic.io kits](https://developers.prismic.io/documentation/UjBe8bGIJ3EKtgBZ/api-documentation#kits-and-helpers) in general (which you should read first). The differences are listed here:
 * 

*(Feel free to contribute differences you find.)*

### Changelog

Need to see what changed, or to upgrade your kit? We keep our changelog on [this repository's "Releases" tab](https://github.com/prismicio/php-kit/releases).

### Contribute to the kit

Contribution is open to all developer levels, read our "[Contribute to the official kits](https://developers.prismic.io/documentation/UszOeAEAANUlwFpp/contribute-to-the-official-kits)" documentation to learn more.

#### Install the kit locally

Clone this GitHub repository, then [install Composer](https://getcomposer.org/doc/00-intro.md) if you haven't, and run:
```bash
composer install
```

#### Test

Please write tests for any bugfix or new feature.

If you find existing code that is not optimally tested and wish to make it better, we really appreciate it; but you should document it on its own branch and its own pull request.

#### Documentation

Please document any bugfix or new feature.

If you find existing code that is not optimally documented and wish to make it better, we really appreciate it; but you should document it on its own branch and its own pull request.


### Licence

This software is licensed under the Apache 2 license, quoted below.

Copyright 2013 Zengularity (http://www.zengularity.com).

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this project except in compliance with the License. You may obtain a copy of the License at http://www.apache.org/licenses/LICENSE-2.0.

Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License.
