<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

class Color extends AbstractScalarFragment
{
    public function asRgb() :? array
    {
        if ($this->isColor()) {
            list($r, $g, $b) = sscanf($this->value, "#%02x%02x%02x");
            return [
                'r' => $r,
                'g' => $g,
                'b' => $b,
            ];
        }
        return null;
    }

    public function asRgbString(?float $alpha = null) :? string
    {
        if (! $this->isColor()) {
            return null;
        }
        ['r' => $r, 'g' => $g, 'b' => $b] = $this->asRgb();
        if ($alpha) {
            return sprintf('rgba(%d, %d, %d, %0.3f)', $r, $g, $b, $alpha);
        }
        return sprintf('rgb(%d, %d, %d)', $r, $g, $b);
    }

    public function isColor() : bool
    {
        return (bool) \preg_match('/^#[0-9A-F]{6}$/i', (string) $this->value);
    }

    public function asInteger() :? int
    {
        if (! $this->isColor()) {
            return null;
        }
        return \hexdec(\substr($this->value, 1));
    }
}
