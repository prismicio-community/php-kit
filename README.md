![alt text](https://travis-ci.org/prismicio/php-kit.png?branch=master "Travis build")

## PHP development kit for prismic.io

### Installation using Composer

Add the dependency:

```bash
php composer.phar require prismic/php-sdk
```

If asked for a version, type in 'dev-master' (unless you want another version):

```bash
Please provide a version constraint for the prismic/php-sdk requirement: dev-master
```

### Usage

```php
<?php

include_once __DIR__.'/../vendor/autoload.php';

use Prismic\Api;

```

### Licence

This software is licensed under the Apache 2 license, quoted below.

Copyright 2013 Zengularity (http://www.zengularity.com).

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this project except in compliance with the License. You may obtain a copy of the License at http://www.apache.org/licenses/LICENSE-2.0.

Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License.
