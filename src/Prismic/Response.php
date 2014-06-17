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

class Response
{
    private $results;
    private $page;
    private $resultsPerPage;
    private $resultsSize;
    private $totalResultsSize;
    private $totalPages;
    private $nextPage;
    private $prevPage;

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

    public function getResults()
    {
        return $this->results;
    }

    public function getPage()
    {
        return $this->page;
    }

    public function getResultsPerPage()
    {
        return $this->resultsPerPage;
    }

    public function getResultsSize()
    {
        return $this->resultsSize;
    }

    public function getTotalResultsSize()
    {
        return $this->totalResultsSize;
    }

    public function getTotalPages()
    {
        return $this->totalPages;
    }

    public function getNextPage()
    {
        return $this->nextPage;
    }

    public function getPrevPage()
    {
        return $this->prevPage;
    }
}
