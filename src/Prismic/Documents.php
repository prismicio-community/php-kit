<?php

/*
 * This file is part of the Prismic PHP SDK
 *
 * Copyright 2013 Zengularity (http://www.zengularity.com).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prismic;

use Prismic\Document;

class Documents
{

    private $page;
    private $results_per_page;
    private $results_size;
    private $total_results_size;
    private $total_pages;
    private $next_page;
    private $prev_page;
    private $results;

    /**
     * @param number $page
     * @param number $results_per_page
     * @param number $results_size
     * @param number $total_results_size
     * @param number $total_pages
     * @param string $next_page
     * @param string $prev_page
     * @param array  $results
     */
    public function __construct($page, $results_per_page, $results_size, $total_results_size, $total_pages, $next_page, $prev_page, array $results)
    {
        $this->page = $page;
        $this->results_per_page = $results_per_page;
        $this->results_size = $results_size;
        $this->total_results_size = $total_results_size;
        $this->total_pages = $total_pages;
        $this->next_page = $next_page;
        $this->prev_page = $prev_page;
        $this->results = $results;
    }

    /**
     * @return number
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @return number
     */
    public function getResultsPerPage()
    {
        return $this->results_per_page;
    }

    /**
     * @return number
     */
    public function getResultsSize()
    {
        return $this->results_size;
    }

    /**
     * @return number
     */
    public function getTotalResultsSize()
    {
        return $this->total_results_size;
    }

    /**
     * @return number
     */
    public function getTotalPages()
    {
        return $this->total_pages;
    }

    /**
     * @return string
     */
    public function getNextPage()
    {
        return $this->next_page;
    }

    /**
     * @return string
     */
    public function getPrevPage()
    {
        return $this->prev_page;
    }

    /**
     * @return array
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * @param \stdClass $json
     *
     * @return Documents
     */
    public static function parse(\stdClass $json)
    {
        return new Documents(
            $json->page,
            $json->results_per_page,
            $json->results_size,
            $json->total_results_size,
            $json->total_pages,
            $json->next_page,
            $json->prev_page,
            array_map(function($doc) { return Document::parse($doc); }, $json->results)
        );
    }
}
