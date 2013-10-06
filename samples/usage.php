<?php

include_once __DIR__.'/../vendor/autoload.php';

use Prismic\Api;

/**
 * For now this class does not exist in the default php-sdk ...
 *
 * Class Context
 */
class Context
{
    private $api;
    private $ref;
    private $accessToken;

    /**
     * @param Api    $api         the Api object to request the remote server
     * @param string $ref         the repository reference
     * @param string $accessToken the access token to access data
     * @param null $linkResolver
     */
    public function __construct(Api $api, $ref, $accessToken = null, $linkResolver = null)
    {
        $this->api = $api;
        $this->ref = $ref;
        $this->accessToken = $accessToken;
    }

    /**
     * @return string or null if non reference is available
     */
    public function maybeRef()
    {
        if ($this->ref != $this->api->master()->ref) {
            return $this->ref;
        }

        return null;
    }

    /**
     * @return bool
     */
    public function hasPrivilegedAccess()
    {
        return isset($this->maybeAccessToken);
    }

    /**
     * @param string $property
     *
     * @return mixed
     */
    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }
}

$options = array(
    'prismic.ref'   => false,
    'prismic.api'   => "https://rabaix.prismic.io/api",
    'prismic.token' => "The token available from the Prismic backend",
);

// retrieve the main information from the api, form and repositories ...
$home = Api::get($options['prismic.api'], $options['prismic.token']);

// retrieve the main repository reference
$ref = $options['prismic.ref'] ?: $home->master()->ref;

// create a context, containing the "home" object and other information required to request the API.
$context = new Context($home, $ref, $options['prismic.token']);

// create a search form from the context, the form required to set the repository reference.
$searchForm = $context->api->forms()->everything->ref($context->ref);

// create a new search form with the search query
$queryForm = $searchForm->query('[[:d = at(document.type, "event")]]');

// retrieve the results from the API
$results = $queryForm->submit();