<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\LinkResolver;
use stdClass;

class Image implements ImageInterface
{
    /** @var ImageInterface[] */
    private $views;

    private function __construct()
    {
    }

    public static function factory($value, LinkResolver $linkResolver) : FragmentInterface
    {
        $image = new static();
        $value = isset($value->value) ? $value->value : $value;

        $main = isset($value->main) ? $value->main : $value;
        unset($value->main);
        /** @var stdClass|null $views */
        $views = isset($value->views) ? $value->views : null;

        $image->views = [
            'main' => ImageView::factory($main, $linkResolver),
        ];
        if (is_null($views)) {
            $views = new stdClass;
            $keys = \array_diff_key(
                (array) $value,
                \array_flip(['url', 'alt', 'copyright', 'linkTo', 'label', 'dimensions', 'type'])
            );
            if (count($keys)) {
                foreach (\array_keys($keys) as $viewName) {
                    $views->{$viewName} = $value->{$viewName};
                }
            }
        }
        foreach (\array_keys((array) $views) as $viewName) {
            $image->views[$viewName] = ImageView::factory($views->{$viewName}, $linkResolver);
        }

        return $image;
    }

    public function getMain() : ImageInterface
    {
        return $this->getView('main');
    }

    public function getView(string $view) :? ImageInterface
    {
        return isset($this->views[$view])
               ? $this->views[$view]
               : null;
    }

    /**
     * @return ImageInterface[]
     */
    public function getViews() : array
    {
        return $this->views;
    }

    public function asText() :? string
    {
        return $this->getMain()->asText();
    }

    public function asHtml() :? string
    {
        return $this->getMain()->asHtml();
    }

    public function getLabel() :? string
    {
        return $this->getMain()->getLabel();
    }

    public function getUrl() : string
    {
        return $this->getMain()->getUrl();
    }

    public function getAlt() :? string
    {
        return $this->getMain()->getAlt();
    }

    public function getCopyright() :? string
    {
        return $this->getMain()->getCopyright();
    }

    public function getWidth() : int
    {
        return $this->getMain()->getWidth();
    }

    public function getHeight() : int
    {
        return $this->getMain()->getHeight();
    }

    public function getLink() :? LinkInterface
    {
        return $this->getMain()->getLink();
    }

    public function hasLink() : bool
    {
        return $this->getMain()->hasLink();
    }

    public function ratio() : float
    {
        return $this->getMain()->ratio();
    }
}
