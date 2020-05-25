<?php
declare(strict_types=1);

namespace Prismic;

use Prismic\Document\HydratorInterface;

class Response
{
    /** @var int */
    private $page;

    /** @var int */
    private $perPage;

    /** @var int */
    private $totalResults;

    /** @var int */
    private $pageCount;

    /** @var string|null */
    private $nextPage;

    /** @var string|null */
    private $prevPage;

    /** @var DocumentInterface[] */
    private $results;

    private function __construct()
    {
    }

    public static function fromJsonString(string $json, HydratorInterface $hydrator) : self
    {
        return static::fromJsonObject(Json::decodeObject($json), $hydrator);
    }

    public static function fromJsonObject(object $data, HydratorInterface $hydrator) : self
    {
        $instance = new static();

        $instance->page         = $data->page;
        $instance->perPage      = $data->results_per_page;
        $instance->totalResults = $data->total_results_size;
        $instance->pageCount    = $data->total_pages;
        $instance->nextPage     = $data->next_page;
        $instance->prevPage     = $data->prev_page;
        $instance->results      = [];

        foreach ($data->results as $object) {
            $instance->results[] = $hydrator->hydrate($object);
        }

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

    /**
     * @return DocumentInterface[]
     */
    public function getResults() : array
    {
        return $this->results;
    }
}
