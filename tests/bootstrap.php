<?php

if (file_exists($file = __DIR__.'/autoload.php')) {
    require_once $file;
} elseif (file_exists($file = __DIR__.'/autoload.php.dist')) {
    require_once $file;
}

if (ini_get('date.timezone') == null) {
  date_default_timezone_set('UTC');
}

require_once 'Prismic/FakeLinkResolver.php';
