<?php
declare(strict_types=1);

namespace Prismic;

use function sprintf;

class SearchFormCollection
{
    /**
     * @var SearchForm[]
     */
    private $forms = [];

    public function __construct(array $forms)
    {
        foreach ($forms as $name => $form) {
            $this->addForm($name, $form);
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
            throw new Exception\InvalidArgumentException(sprintf(
                'The search form named "%s" does not exist',
                $name
            ));
        }
        return $form;
    }

    public function __isset(string $name) : bool
    {
        return $this->hasForm($name);
    }
}
