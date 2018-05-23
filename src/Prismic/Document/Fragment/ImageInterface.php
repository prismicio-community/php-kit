<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

interface ImageInterface extends FragmentInterface
{
    public function getLabel() :? string;

    public function getUrl() : string;

    public function getAlt() :? string;

    public function getCopyright() :? string;

    public function getWidth() : int;

    public function getHeight() : int;

    public function getLink() :? LinkInterface;

    public function hasLink() : bool;

    public function ratio() : float;
}
