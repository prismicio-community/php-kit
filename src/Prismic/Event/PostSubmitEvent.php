<?php

namespace Prismic\Event;

use Symfony\Component\EventDispatcher\Event;
use Prismic\SearchForm;

/**
 * An event dispatched after a SearchForm object's submission receives a
 * response.
 */
class PostSubmitEvent extends Event
{

    /**
     * @var SearchForm
     */
    private $searchForm;
    /**
     * @var \stdClass
     */
    private $json;
    /**
     * @var boolean
     */
    private $cacheHit;

    /**
     * Make a new instance
     *
     * @param SearchForm $searchForm the associated search form
     * @param \stdClass the JSON response from the API
     * @param boolean $cacheHit true if the response came from the cache
     */
    public function __construct(SearchForm $searchForm, \stdClass $json, $cacheHit)
    {
        $this->searchForm = $searchForm;
        $this->json = $json;
        $this->cacheHit = $cacheHit;
    }

    /**
     * Get the search form associated with this event
     *
     * @return SearchForm the search form
     */
    public function getSearchForm()
    {
        return $this->searchForm;
    }

    /**
     * Get the object representing the JSON response from Prismic
     *
     * @return stdClass JSON
     */
    public function getJson()
    {
        return $this->json;
    }

    /**
     * Was the response a cache hit?
     *
     * @return boolean true if the response came from the cache
     */
    public function wasCacheHit()
    {
        return $this->cacheHit;
    }

}
