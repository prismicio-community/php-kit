<?php

namespace Prismic\Event;

use Symfony\Component\EventDispatcher\Event;
use Prismic\SearchForm;

/**
 * An event dispatched when a SearchForm object is about to be submitted.
 */
class PreSubmitEvent extends Event
{

    /**
     * @var SearchForm
     */
    private $searchForm;

    /**
     * Make a new instance
     *
     * @param SearchForm $searchForm the associated search form
     */
    public function __construct(SearchForm $searchForm)
    {
        $this->searchForm = $searchForm;
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

}
