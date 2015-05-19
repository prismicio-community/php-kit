<?php
/**
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
use Prismic\Fragment\Block\TextBlock;
use Prismic\Fragment\Link\DocumentLink;
use Prismic\Fragment\Link\FileLink;
use Prismic\Fragment\Link\ImageLink;
use Prismic\Fragment\Link\WebLink;
use Prismic\Fragment\Span\EmSpan;
use Prismic\Fragment\Span\HyperlinkSpan;
use Prismic\Fragment\Span\StrongSpan;
use Prismic\Fragment\Span\LabelSpan;

/**
 * This class embodies a StructuredText fragment.
 *
 * Technically, a StructuredText fragment is not much more than an array of blocks,
 * but there are many things to do with this fragment, including in the HTML serialization,
 * but not only. It is arguably the most powerful and manipulable way any CMS stores
 * structured text nowadays.
 */
class StructuredText implements FragmentInterface
{
    /**
     * @var array  the array of Prismic\Fragment\Block\BlockInterface objects
     */
    private $blocks;

    /**
     * Constructs a StructuredText object.
     *
     * @param array    $blocks   the array of \Prismic\Fragment\Block\BlockInterface objects
     */
    public function __construct($blocks)
    {
        $this->blocks = $blocks;
    }

    /**
     * Returns the array of blocks.
     *
     * @api
     *
     * @return array the array of \Prismic\Fragment\Block\BlockInterface objects
     */
    public function getBlocks()
    {
        return $this->blocks;
    }

    /**
     * Builds a text version of the StructuredText fragment.
     *
     * @api
     *
     * @return string the text version of the StructuredText fragment
     */
    public function asText()
    {
        $result = array_map(function ($block) {
            return $block instanceof TextBlock ? $block->getText() : '';
        }, $this->blocks);

        return join("\n\n", $result);
    }

    /**
     * Returns the first preformatted block in the StructuredText fragment.
     *
     * @api
     *
     * @return \Prismic\Fragment\Block\PreformattedBlock the first preformatted block in the StructuredText fragment
     */
    public function getFirstPreformatted()
    {
        $blocks = $this->getPreformatted();

        return reset($blocks);
    }

    /**
     * Returns an array of all preformatted blocks in the StructuredText fragment,
     * as \Prismic\Fragment\Block\PreformattedBlock objects.
     *
     * @api
     *
     * @return array all preformatted blocks in the StructuredText fragment
     */
    public function getPreformatted()
    {
        return array_filter($this->blocks, function ($block) {
            return ($block instanceof PreformattedBlock);
        });
    }

    /**
     * Returns the first paragraph block in the StructuredText fragment.
     *
     * @api
     *
     * @return \Prismic\Fragment\Block\ParagraphBlock the first paragraph block in the StructuredText fragment
     */
    public function getFirstParagraph()
    {
        $blocks = $this->getParagraphs();

        return reset($blocks);
    }

    /**
     * Returns an array of all paragraph blocks in the StructuredText fragment,
     * as \Prismic\Fragment\Block\ParagraphBlock objects.
     *
     * @api
     *
     * @return array all paragraph blocks in the StructuredText fragment
     */
    public function getParagraphs()
    {
        return array_filter($this->blocks, function ($block) {
            return ($block instanceof ParagraphBlock);
        });
    }

    /**
     * Returns the first image block in the StructuredText fragment.
     *
     * @api
     *
     * @return \Prismic\Fragment\Block\ImageBlock the first image block in the StructuredText fragment
     */
    public function getFirstImage()
    {
        $blocks = $this->getImages();

        return reset($blocks);
    }

    /**
     * Returns an array of all image blocks in the StructuredText fragment,
     * as \Prismic\Fragment\Block\ImageBlock objects.
     *
     * @api
     *
     * @return array all image blocks in the StructuredText fragment
     */
    public function getImages()
    {
        return array_filter($this->blocks, function ($block) {
            return ($block instanceof ImageBlock);
        });
    }

    /**
     * Returns the first heading block in the StructuredText fragment.
     *
     * @api
     *
     * @return \Prismic\Fragment\Block\HeadingBlock the first heading block in the StructuredText fragment
     */
    public function getFirstHeading()
    {
        $blocks = $this->getHeadings();

        return reset($blocks);
    }

    /**
     * Returns an array of all heading blocks in the StructuredText fragment,
     * as \Prismic\Fragment\Block\HeadingBlock objects.
     *
     * @api
     *
     * @return array all heading blocks in the StructuredText fragment
     */
    public function getHeadings()
    {
        return array_filter($this->blocks, function ($block) {
            return ($block instanceof HeadingBlock);
        });
    }

    /**
     * Builds a HTML version of the StructuredText fragment.
     *
     * @api
     *
     * @param \Prismic\LinkResolver $linkResolver the link resolver
     *
     * @param lambda $htmlSerializer an optional function to generate custom HTML code
     * @return string the HTML version of the StructuredText fragment
     */
    public function asHtml($linkResolver = null, $htmlSerializer = null)
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
                $tag = (($block instanceof ListItemBlock) && !$block->isOrdered() ? "ul" : (($block instanceof ListItemBlock) && $block->isOrdered() ? "li" : null ));
                $newBlockGroup = new BlockGroup($tag, array());
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
                    $html = $html . StructuredText::asHtmlBlock($block, $linkResolver, $htmlSerializer);
                }
                $html = $html . "</" . $group->getTag() . ">";
            } else {
                foreach ($group->getBlocks() as $block) {
                    $html = $html . StructuredText::asHtmlBlock($block, $linkResolver, $htmlSerializer);
                }
            }
        }

        return $html;
    }

    /**
     * Transforms a block into HTML (for internal use)
     *
     * @param \Prismic\Fragment\Block\BlockInterface $block a given block
     * @param \Prismic\LinkResolver $linkResolver the link resolver
     *
     * @param lambda $htmlSerializer
     * @return string the HTML version of the block
     */
    public static function asHtmlBlock($block, $linkResolver = null, $htmlSerializer = null)
    {
        $content = "";
        if ($block instanceof HeadingBlock ||
            $block instanceof ParagraphBlock ||
            $block instanceof ListItemBlock ||
            $block instanceof PreformattedBlock)
        {
            $content = StructuredText::insertSpans($block->getText(), $block->getSpans(), $linkResolver, $htmlSerializer);
        }
        return StructuredText::serialize($block, $content, $linkResolver, $htmlSerializer);
    }

    /**
     * Transforms a text block into HTML (for internal use)
     *
     * @param string                 $text          the raw text of the block
     * @param array                  $spans         the spans of the block, as an array of \Prismic\Fragment\Span\SpanInterface objects
     * @param \Prismic\LinkResolver  $linkResolver  the link resolver
     *
     * @return string the HTML version of the block
     */
    public static function insertSpans($text, array $spans, $linkResolver = null, $htmlSerializer = null)
    {
        if (empty($spans)) {
            return htmlentities($text, null, 'UTF-8');
        }

        $tagsStart = array();
        $tagsEnd = array();

        foreach ($spans as $span) {
            if (!array_key_exists($span->getStart(), $tagsStart)) {
                $tagsStart[$span->getStart()] = array();
            }
            if (!array_key_exists($span->getEnd(), $tagsEnd)) {
                $tagsEnd[$span->getEnd()] = array();
            }

            array_push($tagsStart[$span->getStart()], $span);
            array_push($tagsEnd[$span->getEnd()], $span);
        }

        $c = null;
        $html = "";
        $stack = array();
        for ($pos = 0, $len = strlen($text) + 1; $pos < $len; $pos++) { // Looping to length + 1 to catch closing tags
            if (array_key_exists($pos, $tagsEnd)) {
                foreach ($tagsEnd[$pos] as $endTag) {
                    // Close a tag
                    $tag = array_pop($stack);
                    // Continue only if block contains content.
                    if ($tag && $tag["span"]) {
                        $innerHtml = trim(StructuredText::serialize($tag["span"], $tag["text"], $linkResolver, $htmlSerializer));
                      if (count($stack) == 0) {
                          // The tag was top level
                          $html .= $innerHtml;
                      } else {
                          // Add the content to the parent tag
                          $last = array_pop($stack);
                          $last["text"] = $last["text"] . $innerHtml;
                          array_push($stack, $last);
                      }
                    }
                }
            }
            if (array_key_exists($pos, $tagsStart)) {
                // Sort bigger tags first to ensure the right tag hierarchy
                $sspans = $tagsStart[$pos];
                $spanSort = function ($a, $b) {
                    return ($b->getEnd() - $b->getStart()) - ($a->getEnd() - $a->getStart());
                };
                usort($sspans, $spanSort);
                foreach ($sspans as $span) {
                    // Open a tag
                    array_push($stack, array(
                        "span" => $span,
                        "text" => ""
                    ));
                }
            }
            if ($pos < strlen($text)) {
                $c = mb_substr($text, $pos, 1, 'UTF-8');
                if (count($stack) == 0) {
                    // Top-level text
                    $html .= htmlentities($c, null, 'UTF-8');
                } else {
                    // Inner text of a span
                    $last_idx = count($stack) - 1;
                    $last = $stack[$last_idx];
                    $stack[$last_idx] = array(
                        "span" => $last["span"],
                        "text" => $last["text"] . htmlentities($c, null, 'UTF-8')
                    );
                }
            }
        }

        return $html;
    }

    /**
     * Return the HTML representation of $element
     *
     * @param BlockInterface|SpanInterface $element block or span to serialize
     * @param string $content inner html of the element
     * @param LinkResolver $linkResolver
     * @param HtmlSerializer $htmlSerializer
     */
    private static function serialize($element, $content, $linkResolver, $htmlSerializer) {
        if (!is_null($htmlSerializer)) {
            $custom = $htmlSerializer($element, $content);
            if (!is_null($custom)) {
                return $custom;
            }
        }

        $classCode = "";
        $label = $element->getLabel();
        if (!is_null($label)) {
            $classCode = ' class="' . $label . '"';
        }
        // Blocks
        if ($element instanceof HeadingBlock) {
            return nl2br('<h' . $element->getLevel() . $classCode . '>' . $content . '</h' . $element->getLevel() . '>');
        } elseif ($element instanceof ParagraphBlock) {
            return nl2br('<p' . $classCode . '>' . $content . '</p>');
        } elseif ($element instanceof ListItemBlock) {
            return nl2br('<li' . $classCode . '>' . $content . '</li>');
        } elseif ($element instanceof ImageBlock) {
            return nl2br('<p class="block-img' . (is_null($label) ? '' : (' ' . $label)) . '">' . $element->getView()->asHtml($linkResolver) . '</p>');
        } elseif ($element instanceof EmbedBlock) {
            return nl2br($element->getObj()->asHtml());
        } elseif ($element instanceof PreformattedBlock) {
            return '<pre' . $classCode . '>' . $content . '</pre>';
        }

        // Spans
        $attributes = array();
        if ($element instanceof StrongSpan) {
            $nodeName = 'strong';
        } elseif ($element instanceof EmSpan) {
            $nodeName = 'em';
        } elseif ($element instanceof HyperlinkSpan) {
            $nodeName = 'a';
            if ($element->getLink() instanceof DocumentLink) {
                $attributes['href'] = $linkResolver ? $linkResolver($element->getLink()) : '';
            } else {
                $attributes['href'] = $element->getLink()->getUrl();
            }
            if ($attributes['href'] === null) {
                // We have no link (LinkResolver said it is not valid,
                // or something else went wrong). Abort this span.
                return $content;
            }
        } else {
            //throw new \Exception("Unknown span type " . get_class($span));
            $nodeName = 'span';
        }
        if ($element->getLabel() != NULL) {
            $attributes['class'] = $element->getLabel();
        }

        $html = '<' . $nodeName;
        foreach ($attributes as $k => $v) {
            $html .= (' ' . $k . '="' . $v . '"');
        }
        $html .= ('>' . $content . '</' . $nodeName . '>');
        return $html;
    }

    /**
     * Parses a given span (for internal use)
     *
     * @param  \stdClass  $json the json bit retrieved from the API that represents a span.
     *
     * @return \Prismic\Fragment\Span\SpanInterface  the manipulable object for that span.
     */
    public static function parseSpan($json)
    {
        $type = $json->type;
        $start = $json->start;
        $end = $json->end;
        $label = property_exists($json, "label") ? $json->label : NULL;

        if ("strong" == $type) {
            return new StrongSpan($start, $end, $label);
        }

        if ("em" == $type) {
            return new EmSpan($start, $end, $label);
        }

        if ("hyperlink" == $type && ($link = self::extractLink($json->data))) {
            return new HyperlinkSpan($start, $end, $link, $label);
        }

        if ("label" == $type) {
            if(property_exists($json, 'data') && property_exists($json->data, 'label')) {
                $label = $json->data->label;
            }
            return new LabelSpan($start, $end, $label);
        }

        return null;
    }

    /**
     * Parses a given text block (for internal use)
     *
     * @param  \stdClass  $json the json bit retrieved from the API that represents a text block.
     *
     * @return \Prismic\Fragment\ParsedText  the parsed information for that text block.
     */
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

    /**
     * Parses a given block (for internal use)
     *
     * @param  \stdClass  $json the json bit retrieved from the API that represents a block.
     *
     * @return \Prismic\Fragment\Block\BlockInterface  the manipulable object for that block.
     */
    public static function parseBlock($json)
    {
        $label = NULL;
        if (property_exists($json, "label")) {
            $label = $json->label;
        }
        if (preg_match('/heading(\d)/', $json->type, $level)) {
            $p = StructuredText::parseText($json);

            return new HeadingBlock($p->getText(), $p->getSpans(), $level[1], $label);
        }

        if ($json->type == 'paragraph') {
            $p = StructuredText::parseText($json);

            return new ParagraphBlock($p->getText(), $p->getSpans(), $label);
        }

        if ($json->type == 'list-item') {
            $p = StructuredText::parseText($json);

            return new ListItemBlock($p->getText(), $p->getSpans(), false, $label);
        }

        if ($json->type == 'o-list-item') {
            $p = StructuredText::parseText($json);

            return new ListItemBlock($p->getText(), $p->getSpans(), true, $label);
        }

        if ($json->type == 'image') {
            $view = ImageView::parse($json);

            return new ImageBlock($view, $label);
        }

        if ($json->type == 'embed') {
            return new EmbedBlock(Embed::parse($json), $label);
        }

        if ($json->type == 'preformatted') {
            $p = StructuredText::parseText($json);

            return new PreformattedBlock($p->getText(), $p->getSpans(), $label);
        }

        return null;
    }

    /**
     * Parses a given StructuredText fragment (for internal use)
     *
     * @param  \stdClass  $json the json bit retrieved from the API that represents a StructuredText fragment.
     *
     * @return \Prismic\Fragment\StructuredText  the manipulable object for that StructuredText fragment.
     */
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

    /**
     * Parses and extracts a link of absolutely any kind.
     *
     * @param  \stdClass $data the json bit retrieved from the API that represents a Link fragment.
     *
     * @return \Prismic\Fragment\Link\LinkInterface  the manipulable object for that Link fragment.
     */
    public static function extractLink($data)
    {
        switch ($data->type) {
            case 'Link.web':
                return WebLink::parse($data->value);
            case 'Link.document':
                return DocumentLink::parse($data->value);
            case 'Link.file';
                return FileLink::parse($data->value);
            case 'Link.image';
                return ImageLink::parse($data->value);
            default:
                return null;
        }
    }

}
