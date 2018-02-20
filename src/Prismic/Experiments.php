<?php
declare(strict_types=1);

namespace Prismic;

use stdClass;

/**
 * Class Experiments
 *
 * @package Prismic
 */
class Experiments {

    /**
     * Array of draft experiments
     * @var array
     */
    private $draft;

    /**
     * Array of running experiments
     * @var array
     */
    private $running;

    private function __construct(array $draft, array $running)
    {
        $this->draft   = $draft;
        $this->running = $running;
    }

    /**
     * Return the current running Experiment
     */
    public function getCurrent() :? Experiment
    {
        if (count($this->running) > 0)
        {
            return $this->running[0];
        }
        return null;
    }

    /**
     * Given the value of an experiment cookie, return the corresponding Ref as a string
     */
    public function refFromCookie(?string $cookie) :? string
    {
        if (empty($cookie)) {
            return null;
        }
        $splitted = explode(" ", $cookie);

        if (count($splitted) >= 2)
        {
            $experiment = $this->findRunningById($splitted[0]);
            if (!$experiment) {
                return null;
            }
            $variations = $experiment->getVariations();
            $varIndex = (int)($splitted[1]);
            if ($varIndex > -1 && $varIndex < count($variations)) {
                return $variations[$varIndex]->getRef();
            }
        }
        return null;
    }

    public function getDraft() : array
    {
        return $this->draft;
    }

    public function getRunning() : array
    {
        return $this->running;
    }

    /**
     * Parses a given experiments. Not meant to be used except for testing.
     *
     * @param  $json the json bit retrieved from the API that represents experiments.
     * @return Prismic::Experiments the manipulable object for the experiments.
     */
    public static function parse(stdClass $json)
    {
        return new self(
            array_map(function ($exp) { return Experiment::parse($exp); }, $json->draft),
            array_map(function ($exp) { return Experiment::parse($exp); }, $json->running)
        );
    }

    /**
     * Find the running experiment with the given Google ID
     */
    private function findRunningById(string $id) :? Experiment
    {
        /** @var Experiment $exp */
        foreach ($this->running as $exp)
        {
            if ($exp->getGoogleId() === $id) {
                return $exp;
            }
        }
        return null;
    }
}




