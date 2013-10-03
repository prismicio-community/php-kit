<?php

namespace Prismic\Fragment;

use Prismic\Fragment\Block\EmbedBlock;
use Prismic\Fragment\Block\HeadingBlock;
use Prismic\Fragment\Block\ListItemBlock;
use Prismic\Fragment\Block\ParagraphBlock;
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

    public function asText()
    {
        $result = array_map(function ($block) {
            return $block->text;
        }, $this->blocks);

        return join("\n\n", $result);
    }

    public function asHtml($linkResolver = null)
    {
        $groups = array();
        foreach ($this->blocks as $block) {
            $count = count($groups);
            if ($count > 0) {
                $lastOne = $groups[$count - 1];
                if ('ul' == $lastOne->maybeTag && ($block instanceof ListItemBlock) && !$block->ordered) {
                    $lastOne->addBlock($block);
                }
                else if ('ol' == $lastOne->maybeTag && ($block instanceof ListItemBlock) && $block->ordered) {
                    $lastOne->addBlock($block);
                }
                else if (($block instanceof ListItemBlock) && !$block->ordered) {
                    $newGroup = new Group("ul", array());
                    $newGroup->addBlock($block);
                    array_push($groups, $newGroup);
                }
                else {
                    if (($block instanceof ListItemBlock) && $block->ordered) {
                        $newGroup = new Group("ol", array());
                        $newGroup->addBlock($block);
                        array_push($groups, $newGroup);
                    }
                    else {
                        $newGroup = new Group(NULL, array());
                        $newGroup->addBlock($block);
                        array_push($groups, $newGroup);
                    }
                }
            }
            else {
                $newGroup = new Group(NULL, array());
                $newGroup->addBlock($block);
                array_push($groups, $newGroup);
            }
        }
        $html = "";
        foreach ($groups as $group) {
            $maybeTag = $group->maybeTag;
            if (isset($maybeTag)) {
                $html = $html . "<" . $group->maybeTag . ">";
                foreach ($group->blocks as $block) {
                    $html = $html . StructuredText::asHtmlBlock($block, $linkResolver);
                }
                $html = $html . "</" . $group->maybeTag . ">";
            }
            else {
                foreach ($group->blocks as $block) {
                    $html = $html . StructuredText::asHtmlBlock($block, $linkResolver);
                }
            }
        }
        return $html;
    }

    public static function asHtmlBlock($block, $linkResolver = null)
    {
        if ($block instanceof HeadingBlock) {
            return '<h' . $block->level . '>' . StructuredText::asHtmlText($block->text, $block->spans, $linkResolver) . '</h' . $block->level . '>';
        }
        else if ($block instanceof ParagraphBlock) {
            return '<p>' . StructuredText::asHtmlText($block->text, $block->spans, $linkResolver) . '</p>';
        }
        else if ($block instanceof ListItemBlock) {
            return '<li>' . StructuredText::asHtmlText($block->text, $block->spans, $linkResolver) . '</li>';
        }
        else if ($block instanceof ImageBlock) {
            return '<p>' . $block->view->asHtml($linkResolver) . '</p>';
        }
        else if ($block instanceof EmbedBlock) {
            return $block->obj->asHtml();
        }
        return "";
    }

    public static function asHtmlText($text, $spans, $linkResolver = null)
    {
        if (empty($spans)) {
            return $text;
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
                if ($span->link instanceof WebLink) {
                    return array('<a href="' . $span->link->url . '">', '</a>');
                }
                else if ($span->link instanceof MediaLink) {
                    return array('<a href="' . $span->link->url . '">', '</a>');
                }
                else if ($span->link instanceof DocumentLink) {
                    $url = $linkResolver ? $linkResolver($span->link) : '';
                    return array('<a href="' . $url . '">', '</a>');
                }
            }
            return array('', '');
        };

        $peek = function ($array) {
            return $array[count($array) - 1];
        };

        $peekStart = function ($span) {
            return empty($span) ? PHP_INT_MAX : $span[count($span) - 1]->start;
        };

        $peekEnd = function ($span) {
            return empty($span) ? PHP_INT_MAX : $span[count($span) - 1]->end;
        };

        while (!(empty($starts) && empty($endings))) {
            $next = min($peekStart($starts), $peekEnd($endings));
            if ($next > $pos) {
                $result = $result . substr($text, 0, $next - $pos);
                $text = substr($text, $next - $pos);
                $pos = $next;
            }
            else {
                $spansToApply = "";
                while (min($peekStart($starts), $peekEnd($endings)) == $pos) {
                    if (!empty($endings) && $peek($endings)->end == $pos) {
                        $spansToApply = $spansToApply . $getStartAndEnd(array_pop($endings), $linkResolver)[1];
                    }
                    else if (!empty($starts) && $peek($starts)->start == $pos) {
                        $start = array_pop($starts);
                        array_push($endings, $start);
                        $spansToApply = $spansToApply . $getStartAndEnd($start, $linkResolver)[0];
                    }
                }
                $result = $result . $spansToApply;
            }
        }
        return $result . (strlen($text) > 0 ? $text : '');
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
            }
            else if ("Link.document" == $linkType) {
                $link = DocumentLink::parse($json->data->value);
            }
            else if ("Link.file" == $linkType) {
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
            return new HeadingBlock($p->text, $p->spans, 1);
        }

        if ($json->type == 'heading2') {
            $p = StructuredText::parseText($json);
            return new HeadingBlock($p->text, $p->spans, 2);
        }

        if ($json->type == 'heading3') {
            $p = StructuredText::parseText($json);
            return new HeadingBlock($p->text, $p->spans, 3);
        }

        if ($json->type == 'heading4') {
            $p = StructuredText::parseText($json);
            return new HeadingBlock($p->text, $p->spans, 4);
        }

        if ($json->type == 'paragraph') {
            $p = StructuredText::parseText($json);
            return new ParagraphBlock($p->text, $p->spans);
        }

        if ($json->type == 'list-item') {
            $p = StructuredText::parseText($json);
            return new ListItemBlock($p->text, $p->spans, false);
        }

        if ($json->type == 'o-list-item') {
            $p = StructuredText::parseText($json);
            return new ListItemBlock($p->text, $p->spans, true);
        }

        if ($json->type == 'image') {
            $view = ImageView::parse($json);
            return new ImageBlock($view);
        }

        if ($json->type == 'embed') {
            return new EmbedBlock(Embed::parse($json));
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