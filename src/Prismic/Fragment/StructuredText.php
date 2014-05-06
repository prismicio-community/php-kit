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
use Prismic\Fragment\Block\TextInterface;
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
            return $block instanceof TextInterface ? $block->getText() : '';
        }, $this->blocks);

        return join("\n\n", $result);
    }

    public function getFirstPreformatted()
    {
        $blocks = $this->getPreformatted();

        return reset($blocks);
    }

    public function getPreformatted()
    {
        return array_filter($this->blocks, function ($block) {
            return ($block instanceof PreformattedBlock);
        });
    }

    public function getFirstParagraph()
    {
        $blocks = $this->getParagraphs();

        return reset($blocks);
    }

    public function getParagraphs()
    {
        return array_filter($this->blocks, function ($block) {
            return ($block instanceof ParagraphBlock);
        });
    }

    public function getFirstImage()
    {
        $blocks = $this->getImages();

        return reset($blocks);
    }

    public function getImages()
    {
        return array_filter($this->blocks, function ($block) {
            return ($block instanceof ImageBlock);
        });
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

        $doc = new \DOMDocument;
        $doc->appendChild($doc->createTextNode($text));

        $iterateChildren = function ($node, $start, $span) use (&$iterateChildren, $linkResolver) {
            // Get length of node's text content
            $nodeLength = mb_strlen($node->textContent);

            // If this is a text node we have found the right node
            if ($node instanceof \DOMText) {
                if ($span->getEnd() - $span->getStart() > $nodeLength) {
                    // The span is too long for the node -- we have improperly
                    // nested spans
                    //throw new \Exception("Improperly nested span of type " . get_class($span) . " starting at offset {$span->getStart()}");
                    return;
                }

                // Split the text node into a head, meat and tail
                $meat = $node->splitText($span->getStart() - $start);
                $tail = $meat->splitText($span->getEnd() - $span->getStart());

                // Decide element type and attributes based on span class
                $attributes = array();
                if ($span instanceof StrongSpan) {
                    $nodeName = 'strong';
                } elseif ($span instanceof EmSpan) {
                    $nodeName = 'em';
                } elseif ($span instanceof HyperlinkSpan) {
                    $nodeName = 'a';
                    if ($span->getLink() instanceof DocumentLink) {
                        $attributes['href'] = $linkResolver ? $linkResolver($span->getLink()) : '';
                    } else {
                        $attributes['href'] = $span->getLink()->getUrl();
                    }
                } else {
                    //throw new \Exception("Unknown span type " . get_class($span));
                    $nodeName = 'span';
                }

                // Make the new span element, and put the text from the meat
                // inside
                $spanNode = $node->ownerDocument->createElement($nodeName, htmlspecialchars($meat->textContent));
                foreach ($attributes as $k => $v) {
                    $spanNode->setAttribute($k, $v);
                }

                // Replace the original meat text node with the span
                $meat->parentNode->replaceChild($spanNode, $meat);

                return;
            }

            // Skip this node if the span start is beyond it
            if ($span->getStart() >= $start + mb_strlen($node->textContent)) {
                return;
            }

            // Loop over child nodes to find the correct one
            if ($node->childNodes) {
                foreach ($node->childNodes as $child) {
                    $nodeLength = mb_strlen($child->textContent);
                    if ($span->getStart() < $start + $nodeLength) {
                        // This is the right node -- recurse
                        return $iterateChildren($child, $start, $span);
                    }
                    $start += $nodeLength;
                }
            }

            // Not found
            return;
        };

        foreach ($spans as $span) {
            if ($span->getEnd() < $span->getStart()) {
                //throw new \Exception("Span of type " . get_class($span) . " starting at {$span->getStart()} ends at {$span->getEnd()} (before it begins)");
                continue;
            }
            $iterateChildren($doc, 0, $span);
        }

        return trim($doc->saveHTML());

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
