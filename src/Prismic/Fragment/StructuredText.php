<?php

/*
 * This file is part of the Prismic PHP SDK
 *
 * Copyright 2013 Zengularity (http://www.zengularity.com).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prismic\Fragment;

use Prismic\Fragment\Block\EmbedBlock;
use Prismic\Fragment\Block\HeadingBlock;
use Prismic\Fragment\Block\ImageBlock;
use Prismic\Fragment\Block\ListItemBlock;
use Prismic\Fragment\Block\ParagraphBlock;
use Prismic\Fragment\Block\PreformattedBlock;
use Prismic\Fragment\Link\DocumentLink;
use Prismic\Fragment\Link\MediaLink;
use Prismic\Fragment\Link\WebLink;
use Prismic\Fragment\Span\EmSpan;
use Prismic\Fragment\Span\HyperlinkSpan;
use Prismic\Fragment\Span\StrongSpan;

class StructuredText implements FragmentInterface
{
    private $blocks;

    public function __construct($blocks)
    {
        $this->blocks = $blocks;
    }

    public function getBlocks()
    {
        return $this->blocks;
    }

    public function asText()
    {
        $result = array_map(function ($block) {
            return $block->getText();
        }, $this->blocks);

        return join("\n\n", $result);
    }

    public function getFirstPreformatted()
    {
        foreach ($this->blocks as $block) {
            if (isset($block) && $block instanceof PreformattedBlock) {
                return $block;
            }
        }
    }

    public function getFirstParagraph()
    {
        foreach ($this->blocks as $block) {
            if (isset($block) && $block instanceof ParagraphBlock) {
                return $block;
            }
        }
    }

    public function getFirstImage()
    {
        foreach ($this->blocks as $block) {
            if (isset($block) && $block instanceof ImageBlock) {
                return $block;
            }
        }
    }

    public function asHtml($linkResolver = null)
    {
        $groups = array();
        foreach ($this->blocks as $block) {
            $count = count($groups);
            if ($count > 0) {
                $lastOne = $groups[$count - 1];
                if ('ul' == $lastOne->getTag() && ($block instanceof ListItemBlock) && !$block->isOrdered()) {
                    $lastOne->addBlock($block);
                } elseif ('ol' == $lastOne->getTag() && ($block instanceof ListItemBlock) && $block->isOrdered()) {
                    $lastOne->addBlock($block);
                } elseif (($block instanceof ListItemBlock) && !$block->isOrdered()) {
                    $newBlockGroup = new BlockGroup("ul", array());
                    $newBlockGroup->addBlock($block);
                    array_push($groups, $newBlockGroup);
                } else {
                    if (($block instanceof ListItemBlock) && $block->isOrdered()) {
                        $newBlockGroup = new BlockGroup("ol", array());
                        $newBlockGroup->addBlock($block);
                        array_push($groups, $newBlockGroup);
                    } else {
                        $newBlockGroup = new BlockGroup(null, array());
                        $newBlockGroup->addBlock($block);
                        array_push($groups, $newBlockGroup);
                    }
                }
            } else {
                $newBlockGroup = new BlockGroup(null, array());
                $newBlockGroup->addBlock($block);
                array_push($groups, $newBlockGroup);
            }
        }
        $html = "";
        foreach ($groups as $group) {
            $maybeTag = $group->getTag();
            if (isset($maybeTag)) {
                $html = $html . "<" . $group->getTag() . ">";
                foreach ($group->getBlocks() as $block) {
                    $html = $html . StructuredText::asHtmlBlock($block, $linkResolver);
                }
                $html = $html . "</" . $group->getTag() . ">";
            } else {
                foreach ($group->getBlocks() as $block) {
                    $html = $html . StructuredText::asHtmlBlock($block, $linkResolver);
                }
            }
        }

        return $html;
    }

    public static function asHtmlBlock($block, $linkResolver = null)
    {
        if ($block instanceof HeadingBlock) {
            return nl2br('<h' . $block->getLevel() . '>' .
                    StructuredText::asHtmlText($block->getText(), $block->getSpans(), $linkResolver) .
                    '</h' . $block->getLevel() . '>');
        } elseif ($block instanceof ParagraphBlock) {
            return nl2br('<p>' .
                   StructuredText::asHtmlText($block->getText(), $block->getSpans(), $linkResolver) . '</p>');
        } elseif ($block instanceof ListItemBlock) {
            return nl2br('<li>' .
                   StructuredText::asHtmlText($block->getText(), $block->getSpans(), $linkResolver) . '</li>');
        } elseif ($block instanceof ImageBlock) {
            return nl2br('<p>' . $block->getView()->asHtml($linkResolver) . '</p>');
        } elseif ($block instanceof EmbedBlock) {
            return nl2br($block->getObj()->asHtml());
        } elseif ($block instanceof PreformattedBlock) {
            return '<pre>' .
                   StructuredText::asHtmlText($block->getText(), $block->getSpans(), $linkResolver) .
                   '</pre>';
        }

        return "";
    }

    public static function asHtmlText($text, $spans, $linkResolver = null)
    {
        if (empty($spans)) {
            return htmlentities($text);
        }

        $starts = array();
        for ($i = count($spans) - 1; $i >= 0; $i--) {
            array_push($starts, $spans[$i]);
        }

        $endings = array();
        $result = "";
        $pos = 0;

        $getStartAndEnd = function ($span, $linkResolver = null) {
            if ($span instanceof StrongSpan) {
                return array("<strong>", "</strong>");
            }
            if ($span instanceof EmSpan) {
                return array("<em>", "</em>");
            }
            if ($span instanceof HyperlinkSpan) {
                if ($span->getLink() instanceof WebLink) {
                    return array('<a href="' . $span->getLink()->getUrl() . '">', '</a>');
                } elseif ($span->getLink() instanceof MediaLink) {
                    return array('<a href="' . $span->getLink()->getUrl() . '">', '</a>');
                } elseif ($span->getLink() instanceof DocumentLink) {
                    $url = $linkResolver ? $linkResolver($span->getLink()) : '';

                    return array('<a href="' . $url . '">', '</a>');
                }
            }

            return array('', '');
        };

        $peek = function ($array) {
            return $array[count($array) - 1];
        };

        $peekStart = function ($span) {
            return empty($span) ? PHP_INT_MAX : $span[count($span) - 1]->getStart();
        };

        $peekEnd = function ($span) {
            return empty($span) ? PHP_INT_MAX : $span[count($span) - 1]->getEnd();
        };

        while (!(empty($starts) && empty($endings))) {
            $next = min($peekStart($starts), $peekEnd($endings));
            if ($next > $pos) {
                $htmlToAdd = htmlentities(substr($text, 0, $next - $pos));
                $text = substr($text, $next - $pos);
                $pos = $next;
            } else {
                $spansToApply = "";
                while (min($peekStart($starts), $peekEnd($endings)) == $pos) {
                    if (!empty($endings) && $peek($endings)->getEnd() == $pos) {
                        $startAndEnd = $getStartAndEnd(array_pop($endings), $linkResolver);
                        $spansToApply = $spansToApply . $startAndEnd[1];
                    } elseif (!empty($starts) && $peek($starts)->getStart() == $pos) {
                        $start = array_pop($starts);
                        array_push($endings, $start);
                        $startAndEnd = $getStartAndEnd($start, $linkResolver);
                        $spansToApply = $spansToApply . $startAndEnd[0];
                    }
                }
                $htmlToAdd = $spansToApply;
            }
            $result = $result . $htmlToAdd;
        }

        return $result . (strlen($text) > 0 ? htmlentities($text) : '');
    }

    public static function parseSpan($json)
    {
        $type = $json->type;
        $start = $json->start;
        $end = $json->end;

        if ("strong" == $type) {
            return new StrongSpan($start, $end);
        }

        if ("em" == $type) {
            return new EmSpan($start, $end);
        }

        $link = false;
        if ("hyperlink" == $type) {
            $linkType = $json->data->type;
            if ("Link.web" == $linkType) {
                $link = WebLink::parse($json->data->value);
            } elseif ("Link.document" == $linkType) {
                $link = DocumentLink::parse($json->data->value);
            } elseif ("Link.file" == $linkType) {
                $link = MediaLink::parse($json->data->value);
            }
        }

        if ($link) {
            return new HyperlinkSpan($start, $end, $link);
        }

        return null;
    }

    public static function parseText($json)
    {
        $text = $json->text;
        $spans = array();
        foreach ($json->spans as $spanJson) {
            $span = StructuredText::parseSpan($spanJson);
            if (isset($span)) {
                array_push($spans, $span);
            }
        }

        return new ParsedText($text, $spans);
    }

    public static function parseBlock($json)
    {
        if ($json->type == 'heading1') {
            $p = StructuredText::parseText($json);

            return new HeadingBlock($p->getText(), $p->getSpans(), 1);
        }

        if ($json->type == 'heading2') {
            $p = StructuredText::parseText($json);

            return new HeadingBlock($p->getText(), $p->getSpans(), 2);
        }

        if ($json->type == 'heading3') {
            $p = StructuredText::parseText($json);

            return new HeadingBlock($p->getText(), $p->getSpans(), 3);
        }

        if ($json->type == 'heading4') {
            $p = StructuredText::parseText($json);

            return new HeadingBlock($p->getText(), $p->getSpans(), 4);
        }

        if ($json->type == 'paragraph') {
            $p = StructuredText::parseText($json);

            return new ParagraphBlock($p->getText(), $p->getSpans());
        }

        if ($json->type == 'list-item') {
            $p = StructuredText::parseText($json);

            return new ListItemBlock($p->getText(), $p->getSpans(), false);
        }

        if ($json->type == 'o-list-item') {
            $p = StructuredText::parseText($json);

            return new ListItemBlock($p->getText(), $p->getSpans(), true);
        }

        if ($json->type == 'image') {
            $view = ImageView::parse($json);

            return new ImageBlock($view);
        }

        if ($json->type == 'embed') {
            return new EmbedBlock(Embed::parse($json));
        }

        if ($json->type == 'preformatted') {
            return new PreformattedBlock($json->text, $json->spans, false);
        }

        return null;
    }

    public static function parse($json)
    {
        $blocks = array();
        foreach ($json as $blockJson) {
            $maybeBlock = StructuredText::parseBlock($blockJson);
            if (isset($maybeBlock)) {
                array_push($blocks, $maybeBlock);
            }
        }

        return new StructuredText($blocks);
    }
}
