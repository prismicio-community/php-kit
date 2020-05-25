<?php
declare(strict_types=1);

namespace Prismic;

use function array_map;
use function count;
use function explode;

class Experiments
{
    /** @var Experiment[] */
    private $draft;

    /** @var Experiment[] */
    private $running;

    /**
     * @param Experiment[] $draft
     * @param Experiment[] $running
     */
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
        if (count($this->running) > 0) {
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

        $parts = explode(' ', $cookie);

        if (count($parts) >= 2) {
            $experiment = $this->findRunningById($parts[0]);
            if (! $experiment) {
                return null;
            }

            $variations = $experiment->getVariations();
            $varIndex = (int) $parts[1];
            if ($varIndex > -1 && $varIndex < count($variations)) {
                return $variations[$varIndex]->getRef();
            }
        }

        return null;
    }

    /**
     * @return Experiment[]
     */
    public function getDraft() : array
    {
        return $this->draft;
    }

    /**
     * @return Experiment[]
     */
    public function getRunning() : array
    {
        return $this->running;
    }

    public static function parse(object $json) : self
    {
        return new self(
            array_map(static function (object $exp) : Experiment {
                return Experiment::parse($exp);
            }, $json->draft),
            array_map(static function (object $exp) : Experiment {
                return Experiment::parse($exp);
            }, $json->running)
        );
    }

    /**
     * Find the running experiment with the given Google ID
     */
    private function findRunningById(string $id) :? Experiment
    {
        foreach ($this->running as $exp) {
            if ($exp->getGoogleId() === $id) {
                return $exp;
            }
        }

        return null;
    }
}
