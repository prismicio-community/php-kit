<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: jeroen
 * Date: 20-3-19
 * Time: 21:19
 */

namespace Prismic;

class Language
{

    /**
     * Language id
     * @var string
     */
    private $id;

    /**
     * Language name
     * @var string
     */
    private $name;

    /**
     * Language constructor.
     *
     * @param string $id
     * @param string $name
     */
    private function __construct(
        string $id,
        string $name
    ) {
        $this->id = $id;
        $this->name = $name;
    }


    /**
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     *
     * @param \stdClass $json
     * @return Language
     *
     * @throws Exception\InvalidArgumentException
     */
    public static function parse(\stdClass $json): self
    {
        if (! isset($json->id, $json->name)) {
            throw new Exception\InvalidArgumentException(
                'The properties id, name should exist in the JSON object for a Language'
            );
        }
        return new self(
            $json->id,
            $json->name
        );
    }
}
