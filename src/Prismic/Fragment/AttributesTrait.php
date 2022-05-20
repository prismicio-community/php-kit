<?php

namespace Prismic\Fragment;

trait AttributesTrait
{
    /**
     * @return array
     */
    public function getAttributes(): array
    {
        $attributes = [];
        if (isset($this->label)) {
            $attributes[] = sprintf('class="%s"', $this->label);
        } elseif (isset($this->data->label)) {
            $attributes[] = sprintf('class="%s"', $this->data->label);
        }
        return $attributes;
    }

}
