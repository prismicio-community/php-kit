<?php
/**
 * This file is part of the Prismic PHP SDK
 *
 * Copyright 2013 Zengularity (http://www.zengularity.com).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prismic;

/**
 * This class embodies a search request response from the prismic.io API,
 * containing a list of documents, as well as meta-information about this
 * list (mostly about pagination).
 *
 * This is what is returned when you call submit() on a search request, which
 * is the only kind of request except for the API endpoint. So, let's say that
 * when you call prismic.io's API, you get that object a lot.
 *
 * Do remember that requests in prismic.io are paginated by 20 by default.
 */
class Response
{
    /**
     * @var array the list of returned documents
     */
    private $results;
    /**
     * @var integer the page number for this query
     */
    private $page;
    /**
     * @var integer the requested number of results per page on this query
     */
    private $resultsPerPage;
    /**
     * @var integer the size of the current page
     */
    private $resultsSize;
    /**
     * @var integer the total number of documents, all pages together
     */
    private $totalResultsSize;
    /**
     * @var integer the number of pages for this query
     */
    private $totalPages;
    /**
     * @var string the RESTful URL of the search request for the next page; null otherwise
     */
    private $nextPage;
    /**
     * @var string the RESTful URL of the search request for the previous page; null otherwise
     */
    private $prevPage;

    /**
     * Constructs a Response object.
     *
     * @param array  $results           the list of returned documents
     * @param string $page              the page number for this query
     * @param string $resultsPerPage    the requested number of results per page on this query
     * @param string $resultsSize       the ID of the release
     * @param string $totalResultsSize  the ID of the release
     * @param string $totalPages        the ID of the release
     * @param string $nextPage          the ID of the release
     * @param string $prevPage          the ID of the release
     */
    public function __construct($results, $page, $resultsPerPage, $resultsSize, $totalResultsSize, $totalPages, $nextPage, $prevPage)
    {
        $this->results = $results;
        $this->page = $page;
        $this->resultsPerPage = $resultsPerPage;
        $this->resultsSize = $resultsSize;
        $this->totalResultsSize = $totalResultsSize;
        $this->totalPages = $totalPages;
        $this->nextPage = $nextPage;
        $this->prevPage = $prevPage;
    }

    /**
     * Returns the list of returned documents, which is an array of Document objects.
    *
    * @return array the list of returned documents
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * Returns the page number for this query.
    *
    * @return integer the page number for this query
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Returns the requested number of results per page on this query.
    *
    * @return integer the requested number of results per page on this query
     */
    public function getResultsPerPage()
    {
        return $this->resultsPerPage;
    }

    /**
     * Returns the size of the current page.
    *
    * @return integer the size of the current page
     */
    public function getResultsSize()
    {
        return $this->resultsSize;
    }

    /**
     * Returns the total number of documents, all pages together.
    *
    * @return integer the total number of documents, all pages together
     */
    public function getTotalResultsSize()
    {
        return $this->totalResultsSize;
    }

    /**
     * Returns the number of pages for this query.
    *
    * @return integer the number of pages for this query
     */
    public function getTotalPages()
    {
        return $this->totalPages;
    }

    /**
     * Returns the RESTful URL of the search request for the next page; null otherwise.
    *
    * @return string the RESTful URL of the search request for the next page; null otherwise
     */
    public function getNextPage()
    {
        return $this->nextPage;
    }

    /**
     * Returns the RESTful URL of the search request for the previous page; null otherwise.
    *
    * @return string the RESTful URL of the search request for the previous page; null otherwise
     */
    public function getPrevPage()
    {
        return $this->prevPage;
    }

    /**
     * Parsing a Responses from a json, unmarshalling them into PHP objects.
     *
     * @param  \stdClass          $json the JSON retrieved from the call
     * @return \Prismic\Documents the result of the call
     */
    public static function parse(\stdClass $json)
    {
        $results = array_map(function ($doc) { return Document::parse($doc);  }, $json->results);

        return new Response(
            $results,
            $json->page,
            $json->results_per_page,
            $json->results_size,
            $json->total_results_size,
            $json->total_pages,
            $json->next_page,
            $json->prev_page
        );
    }

}
