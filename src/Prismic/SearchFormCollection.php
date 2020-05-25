<?php
declare(strict_types=1);

namespace Prismic;

use BadMethodCallException;
use Prismic\Exception\InvalidArgumentException;
use function sprintf;

class SearchFormCollection
{
    /** @var SearchForm[] */
    private $forms = [];

    /** @param SearchForm[] $forms */
    public function __construct(array $forms)
    {
        foreach ($forms as $form) {
            $this->addForm($form->getKey(), $form);
        }
    }

    private function addForm(string $name, SearchForm $form) : void
    {
        $this->forms[$name] = $form;
    }

    public function getForm(string $name) :? SearchForm
    {
        return $this->hasForm($name)
            ? $this->forms[$name]
            : null;
    }

    public function hasForm(string $name) : bool
    {
        return isset($this->forms[$name]);
    }

    public function __get(string $name) : SearchForm
    {
        $form = $this->getForm($name);
        if (! $form) {
            throw new InvalidArgumentException(sprintf(
                'The search form named "%s" does not exist',
                $name
            ));
        }

        return $form;
    }

    /** @param mixed $value */
    public function __set(string $name, $value) : void
    {
        throw new BadMethodCallException('There is no __set method');
    }

    public function __isset(string $name) : bool
    {
        return $this->hasForm($name);
    }
}
