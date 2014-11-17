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

class Experiments {

    private $draft;
    private $running;

    public function __construct(array $draft, array $running)
    {
        $this->draft = $draft;
        $this->running = $running;
    }

    public function getCurrent()
    {
        if (count($this->running) > 0)
        {
            return $this->running[0];
        }
        return null;
    }

    public function refFromCookie($cookie)
    {
        if ($cookie == null) return null;
        $splitted = preg_split("/%20/", trim($cookie));

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

    public function getDraft()
    {
        return $this->draft;
    }

    public function getRunning()
    {
        return $this->running;
    }

    /**
     * Parses a given experiments. Not meant to be used except for testing.
     *
     * @param  \stdClass $json the json bit retrieved from the API that represents experiments.
     * @return \Prismic\Experiments e manipulable object for that experiments.
     */
    public static function parse(\stdClass $json)
    {
        return new Experiments(
            array_map(function ($exp) { return Experiment::parse($exp); }, $json->draft),
            array_map(function ($exp) { return Experiment::parse($exp); }, $json->running)
        );
    }

    private function findRunningById($id)
    {
        foreach ($this->running as $exp)
        {
            if ($exp->getGoogleId() == $id) {
                return $exp;
            }
        }
        return null;
    }
}

class Experiment {

    private $id;
    private $googleId;
    private $name;
    private $variations;

    public function __construct($id, $googleId, $name, array $variations)
    {
        $this->id = $id;
        $this->googleId = $googleId;
        $this->name = $name;
        $this->variations = $variations;
    }

    public function getId() {
        return $this->id;
    }

    public function getGoogleId() {
        return $this->googleId;
    }

    public function getName() {
        return $this->name;
    }

    public function getVariations() {
        return $this->variations;
    }

    /**
     * Parses a given experiment. Not meant to be used except for testing.
     *
     * @param  \stdClass $json the json bit retrieved from the API that represents a experiment.
     * @return \Prismic\Variation the manipulable object for that experiment.
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

class Variation
{

    private $id;
    private $ref;
    private $label;

    public function __construct($id, $ref, $label)
    {
        $this->id = $id;
        $this->ref = $ref;
        $this->label = $label;
    }

    public function getId() {
        return $this->id;
    }

    public function getRef() {
        return $this->ref;
    }

    public function getLabel() {
        return $this->label;
    }

    /**
     * Parses a given variation. Not meant to be used except for testing.
     *
     * @param  \stdClass $json the json bit retrieved from the API that represents a variation.
     * @return \Prismic\Variation the manipulable object for that variation.
     */
    public static function parse(\stdClass $json)
    {
        return new Variation($json->id, $json->ref, $json->label);
    }

}
