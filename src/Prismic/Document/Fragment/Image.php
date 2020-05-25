<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Exception\RuntimeException;
use Prismic\LinkResolver;
use stdClass;
use function array_diff_key;
use function array_flip;
use function array_keys;
use function count;

class Image implements ImageInterface
{
    /** @var ImageInterface[] */
    private $views;

    private function __construct()
    {
    }

    public static function factory(object $value, LinkResolver $linkResolver) : self
    {
        $image = new static();
        $value = $value->value ?? $value;

        $main = $value->main ?? $value;
        unset($value->main);
        $views = $value->views ?? null;

        $image->views = [
            'main' => ImageView::factory($main, $linkResolver),
        ];
        if (! $views) {
            $views = new stdClass();
            $keys = array_diff_key(
                (array) $value,
                array_flip(['url', 'alt', 'copyright', 'linkTo', 'label', 'dimensions', 'type'])
            );
            if (count($keys)) {
                foreach (array_keys($keys) as $viewName) {
                    $views->{$viewName} = $value->{$viewName};
                }
            }
        }

        foreach (array_keys((array) $views) as $viewName) {
            $image->views[$viewName] = ImageView::factory($views->{$viewName}, $linkResolver);
        }

        return $image;
    }

    public function getMain() : ImageInterface
    {
        $view = $this->getView('main');
        if (! $view) {
            /**
             * It is very unlikely this will be reached due to ImageView::validatePayload
             */
            throw new RuntimeException('The main view could not be retrieved for this image');
        }

        return $view;
    }

    public function getView(string $view) :? ImageInterface
    {
        return $this->views[$view] ?? null;
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
