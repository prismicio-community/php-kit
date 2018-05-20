<?php
declare(strict_types=1);

namespace Prismic;

use Prismic\Exception;

class Response
{

    /**
     * @var int
     */
    private $page;

    /**
     * @var int
     */
    private $perPage;

    /**
     * @var int
     */
    private $totalResults;

    /**
     * @var int
     */
    private $pageCount;

    /**
     * @var string|null
     */
    private $nextPage;

    /**
     * @var string|null
     */
    private $prevPage;

    /**
     * @var array
     */
    private $results;

    public static function fromJsonString(string $json) : self
    {
        $data = \json_decode($json);
        if (! $json) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Failed to decode json payload: %s',
                \json_last_error_msg()
            ). \json_last_error());
        }
        $instance = new static;

        $instance->page         = $data->page;
        $instance->perPage      = $data->results_per_page;
        $instance->totalResults = $data->total_results_size;
        $instance->pageCount    = $data->total_pages;
        $instance->nextPage     = $data->next_page;
        $instance->prevPage     = $data->prev_page;
        $instance->results      = $data->results;

        return $instance;
    }

    public function getCurrentPageNumber() : int
    {
        return $this->page;
    }

    public function getResultsPerPage() : int
    {
        return $this->perPage;
    }

    public function getTotalResults() : int
    {
        return $this->totalResults;
    }

    public function getTotalPageCount() : int
    {
        return $this->pageCount;
    }

    public function getNextPageUrl() :? string
    {
        return $this->nextPage;
    }

    public function getPrevPageUrl() :? string
    {
        return $this->prevPage;
    }

    public function getResults() : array
    {

    }
}
