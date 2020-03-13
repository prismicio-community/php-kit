<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Document\Fragment\Link\AbstractLink;
use Prismic\LinkResolver;
use Laminas\Escaper\Escaper;
use function array_walk;
use function implode;
use function is_array;
use function is_scalar;
use function json_encode;
use function nl2br;
use function preg_split;
use function sprintf;
use function strpos;
use function substr;

trait HtmlHelperTrait
{

    private $escapeHelper;

    private function htmlAttributes(array $attributes) : string
    {
        $html = '';
        foreach ($attributes as $key => $val) {
            $key = $this->escapeHtml($key);

            if (('on' == substr($key, 0, 2)) || ('constraints' == $key)) {
                // Don't escape event attributes; _do_ substitute double quotes with singles
                if (! is_scalar($val)) {
                    // non-scalar data should be cast to JSON first
                    $val = json_encode($val);
                }
            } else {
                if (is_array($val)) {
                    $val = implode(' ', $val);
                }
            }

            $val = $this->escapeHtmlAttr($val);

            if (strpos($val, '"') !== false) {
                $html .= " $key='$val'";
            } else {
                $html .= " $key=\"$val\"";
            }
        }

        return $html;
    }

    private function getEscapeHelper() : Escaper
    {
        if (! $this->escapeHelper) {
            $this->escapeHelper = new Escaper;
        }
        return $this->escapeHelper;
    }

    private function escapeHtml(string $value) : string
    {
        return $this->getEscapeHelper()->escapeHtml($value);
    }

    private function escapeHtmlAttr(string $value) : string
    {
        return $this->getEscapeHelper()->escapeHtmlAttr($value);
    }

    private function insertSpans(string $text, array $spans, LinkResolver $linkResolver) : string
    {
        if (empty($spans)) {
            return nl2br($this->escapeHtml($text));
        }

        $nodes = preg_split('//u', $text, -1, \PREG_SPLIT_NO_EMPTY);
        array_walk($nodes, function (&$character) {
            $character = $this->escapeHtml($character);
        });
        foreach ($spans as $span) {
            if (! isset($span->type)) {
                continue;
            }
            $openTag = $closeTag = null;
            $end = $span->end - 1;
            switch ($span->type) {
                case 'strong':
                case 'em':
                    $openTag  = sprintf('<%s>', $span->type);
                    $closeTag = sprintf('</%s>', $span->type);
                    break;

                case 'label':
                    // Multiple labels at the same indexes are not possible,
                    // therefore we don't have to combine CSS classes
                    $openTag  = sprintf('<span%s>', $this->htmlAttributes(['class' => $span->data->label]));
                    $closeTag = '</span>';
                    break;

                case 'hyperlink':
                    $link = AbstractLink::abstractFactory($span->data, $linkResolver);
                    if ($link) {
                        $openTag  = $link->openTag();
                        $closeTag = $link->closeTag();
                    }
                    break;
            }
            if (! $openTag || ! $closeTag) {
                continue;
            }
            $nodes[$span->start] = sprintf('%s%s', $openTag, $nodes[$span->start]);
            $nodes[$end] = sprintf('%s%s', $nodes[$end], $closeTag);
        }
        return nl2br(implode('', $nodes));
    }
}
