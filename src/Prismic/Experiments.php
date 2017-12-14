<?php

namespace Prismic;

/**
 * Class Experiments
 *
 * @package Prismic
 */
class Experiments {

    //! array */
    private $draft;
    //! array */
    private $running;

    /**
     * @param array $draft
     * @param array $running
     */
    public function __construct(array $draft, array $running)
    {
        $this->draft = $draft;
        $this->running = $running;
    }

    /**
     * @return Experiment
     */
    public function getCurrent()
    {
        if (count($this->running) > 0)
        {
            return $this->running[0];
        }
        return null;
    }

    /**
     * @param string|null $cookie
     *
     * @return Ref|null
     */
    public function refFromCookie($cookie)
    {
        if ($cookie == null) return null;
        $splitted = explode(" ", $cookie);

        if (count($splitted) >= 2)
        {
            $experiment = $this->findRunningById($splitted[0]);
            if ($experiment == null) return null;
            $variations = $experiment->getVariations();
            $varIndex = (int)($splitted[1]);
            if ($varIndex > -1 && $varIndex < count($variations)) {
                return $variations[$varIndex]->getRef();
            }
        }
        return null;
    }

    /**
     * @return array
     */
    public function getDraft()
    {
        return $this->draft;
    }

    /**
     * @return array
     */
    public function getRunning()
    {
        return $this->running;
    }

    /**
     * Parses a given experiments. Not meant to be used except for testing.
     *
     * @param  $json the json bit retrieved from the API that represents experiments.
     * @return Prismic::Experiments the manipulable object for the experiments.
     */
    public static function parse(\stdClass $json)
    {
        return new Experiments(
            array_map(function ($exp) { return Experiment::parse($exp); }, $json->draft),
            array_map(function ($exp) { return Experiment::parse($exp); }, $json->running)
        );
    }

    /**
     * @param string $id
     *
     * @return Experiment|null
     */
    private function findRunningById($id)
    {
        /** @var Experiment $exp */
        foreach ($this->running as $exp)
        {
            if ($exp->getGoogleId() == $id) {
                return $exp;
            }
        }
        return null;
    }
}

/**
 * Class Experiment
 *
 * @package Prismic
 */
class Experiment {

    /** @var string */
    private $id;
    /** @var string */
    private $googleId;
    /** @var string */
    private $name;
    /** @var array */
    private $variations;

    /**
     * @param string $id
     * @param string $googleId
     * @param string $name
     * @param array  $variations
     */
    public function __construct($id, $googleId, $name, array $variations)
    {
        $this->id = $id;
        $this->googleId = $googleId;
        $this->name = $name;
        $this->variations = $variations;
    }

    /**
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getGoogleId() {
        return $this->googleId;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getVariations() {
        return $this->variations;
    }

    /**
     * Parses a given experiment. Not meant to be used except for testing.
     *
     * @param  $json the json bit retrieved from the API that represents a experiment.
     *
     * @return Prismic::Variation the manipulable object for that experiment.
     */
    public static function parse(\stdClass $json)
    {
        $googleId = (isset($json->googleId) ? $json->googleId : 0);
        $vars = array_map(function ($varJson) { return Variation::parse($varJson); }, $json->variations);
        return new Experiment(
            $json->id,
            $googleId,
            $json->name,
            $vars
        );
    }

}

/**
 * Class Variation
 *
 * @package Prismic
 */
class Variation
{
    /** @var string */
    private $id;
    /** @var string */
    private $ref;
    /** @var string */
    private $label;

    /**
     * @param string $id
     * @param string $ref
     * @param string $label
     */
    public function __construct($id, $ref, $label)
    {
        $this->id = $id;
        $this->ref = $ref;
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getRef() {
        return $this->ref;
    }

    /**
     * @return string
     */
    public function getLabel() {
        return $this->label;
    }

    /**
     * Parses a given variation. Not meant to be used except for testing.
     *
     * @param  $json the json bit retrieved from the API that represents a variation.
     *
     * @return Prismic::Variation the manipulable object for that variation.
     */
    public static function parse(\stdClass $json)
    {
        return new Variation($json->id, $json->ref, $json->label);
    }

}
