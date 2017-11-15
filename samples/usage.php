<?php

include_once __DIR__.'/../vendor/autoload.php';

use Prismic\Api;

$options = array(
    'prismic.ref'   => false,
    'prismic.api'   => "https://lesbonneschoses.prismic.io/api",
    'prismic.token' => "Your permanent token",
);

// retrieve the main information from the api, form and repositories ...
$api = Api::get($options['prismic.api'], $options['prismic.token']);

// retrieve the main repository reference
$ref = $options['prismic.ref'] ?: $api->master()->getRef();

$searchForm = $api->forms()->everything->ref($ref);

// create a new search form with the search query
$queryForm = $searchForm->query('[[:d = at(document.type, "product")]]');

// retrieve the results from the API
$results = $queryForm->submit();
