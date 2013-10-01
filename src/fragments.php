<?php

namespace prismic;

interface Fragment {}
interface Link extends Fragment {}

class WebLink implements Link {
    private $url;
    private $maybeContentType;

    function __construct($url, $maybeContentType=null) {
        $this->url = $url;
        $this->maybeContentType = $maybeContentType;
    }

    public function asHtml($linkResolver = null) {
        return '<a href="'. $this->url .'">$url</a>';
    }

    public static function parse($json) {
        return new WebLink($json->url);
    }

    public function __get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }
}

class MediaLink implements Link {

    private $url;
    private $contentType;
    private $size;
    private $filename;

    function __construct($url, $kind, $size, $filename) {
        $this->url = $url;
        $this->kind = $kind;
        $this->size = $size;
        $this->filename = $filename;
    }

    public function asHtml($linkResolver = null) {
        return '<a href="'. $this->url .'">'. $this->filename .'</a>';
    }

    public function __get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    public static function parse($json) {
        return new MediaLink(
            $json->file->url,
            $json->file->kind,
            $json->file->size,
            $json->file->name
        );
    }
}

class DocumentLink implements Link {

    private $id;
    private $type;
    private $tags;
    private $slug;
    private $isBroken;

    function __construct($id, $type, $tags, $slug, $isBroken) {
        $this->id = $id;
        $this->type = $type;
        $this->tags = $tags;
        $this->slug = $slug;
        $this->isBroken = $isBroken;
    }

    public function asHtml($linkResolver) {
        return '<a href="' . $linkResolver($this) . '">' . $this->slug . '</a>';
    }

    public static function parse($json) {
        return new DocumentLink(
            $json->document->id,
            $json->document->type,
            $json->document->tags,
            $json->document->slug,
            $json->isBroken
        );
    }

    public function __get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }
}

class Number implements Fragment {

    private $data;

    function __construct($data) {
        $this->data = $data;
    }

    public function asText() {
        return $this->data;
    }

    public function asHtml() {
        return '<span class="number">' . $this->data . '</span>';
    }
}

class Text implements Fragment {

    private $value;

    function __construct($value) {
        $this->value = $value;
    }

    public function asHtml() {
        return '<span class="text">' .$this->value . '</span>';
    }
}

class Date implements Fragment {

    private $value;

    function __construct($value) {
        $this->value = $value;
    }

    public function asHtml() {
        return '<time>'. $this->value .'</time>';
    }
}

class Embed implements Fragment {

    private $type;
    private $provider;
    private $url;
    private $maybeWidth;
    private $maybeHeight;
    private $maybeHtml;
    private $oembedJson;

    function __construct($type, $provider, $url, $maybeWidth, $maybeHeigth, $maybeHtml, $oembedJson) {
        $this->type = $type;
        $this->provider = $provider;
        $this->url = $url;
        $this->maybeWidth = $maybeWidth;
        $this->maybeHeight = $maybeHeigth;
        $this->maybeHtml = $maybeHtml;
        $this->oembedJson = $oembedJson;
    }

    public function asHtml() {
        if(isset($this->maybeHtml)) {
            return '<div data-oembed="' . $this->url . '" data-oembed-type="'. strtolower($this->type) . '" data-oembed-provider="' . strtolower($this->provider) . '">' . $this->maybeHtml . '</div>';
        } else {
            return "";
        }
    }

    public static function parse($json) {
        return new Embed(
            $json->oembed->type,
            $json->oembed->provider_name,
            $json->oembed->embed_url,
            $json->oembed->width,
            $json->oembed->height,
            $json->oembed->html,
            $json->oembed
        );
    }
}

class Color implements Fragment {

    private $data;

    function __construct($data) {
        $this->data = $data;
    }

    public function asHtml() {
        return '<span class="color">' . $this->data . '</span>';
    }
}

class ImageView {

    private $url;
    private $width;
    private $height;

    function __construct($url, $width, $height) {
        $this->url = $url;
        $this->width = $width;
        $this->height = $height;
    }

    public function asHtml() {
        return '<img src="' . $this->url . '" width="' . $this->width . '" height="' . $this->height . '"/>';
    }

    public function ratio() {
        return $this->width / $this->height;
    }

    public static function parse($json) {
        return new ImageView(
            $json->url,
            $json->dimensions->width,
            $json->dimensions->height
        );
    }
}

class Image implements Fragment {

    private $main;
    private $views;

    function __construct($main, $views) {
        $this->main = $main;
        $this->views = $views;
    }

    public function asHtml() {
        return $this->main->asHtml();
    }

    public function getView($key) {
        if(strtolower($key) == "main") {
            return $this->main;
        } else {
            return $this->views[$key];
        }
    }
}

class StructuredText implements Fragment {

    private $blocks;

    function __construct($blocks) {
        $this->blocks = $blocks;
    }

    public function asText() {
        $result = array_map(function ($block) {
        return $block->text;
    }, $this->blocks);
        return join("\n\n", $result);
    }

    public function asHtml($linkResolver=null) {
        if(!isset($blocks)) {
            $blocks = $this->blocks;
        }
        $groups = array();
        foreach($this->blocks as $block) {
            $count = count($groups);
            if($count > 0) {
                $lastOne = $groups[$count - 1];
                if('ul' == $lastOne->maybeTag && ($block instanceof ListItemBlock) && !$block->ordered) {
                    $lastOne->addBlock($block);
                }
                else if('ol' == $lastOne->maybeTag && ($block instanceof ListItemBlock) && $block->ordered) {
                    $lastOne->addBlock($block);
                }
                else if(($block instanceof ListItemBlock) && !$block->ordered) {
                    $newGroup = new Group("ul", array());
                    $newGroup->addBlock($block);
                    array_push($groups, $newGroup);
                }
                else if(($block instanceof ListItemBlock) && $block->ordered) {
                    $newGroup = new Group("ol", array());
                    $newGroup->addBlock($block);
                    array_push($groups, $newGroup);
                }
                else {
                    $newGroup = new Group(NULL, array());
                    $newGroup->addBlock($block);
                    array_push($groups, $newGroup);
                }
            } else {
                $newGroup = new Group(NULL, array());
                $newGroup->addBlock($block);
                array_push($groups, $newGroup);
            }
        }
        $html = "";
        foreach($groups as $group) {
            $maybeTag = $group->maybeTag;
            if(isset($maybeTag)) {
                $html = $html . "<" . $group->maybeTag . ">";
                foreach($group->blocks as $block) {
                    $html = $html . StructuredText::asHtmlBlock($block, $linkResolver);
                }
                $html = $html . "</" . $group->maybeTag . ">";
            } else {
                foreach($group->blocks as $block) {
                    $html = $html . StructuredText::asHtmlBlock($block, $linkResolver);
                }
            }
        }
        return $html;
    }

    public static function asHtmlBlock($block, $linkResolver=null) {
        if($block instanceof HeadingBlock) {
            return '<h' . $block->level . '>' . StructuredText::asHtmlText($block->text, $block->spans, $linkResolver) . '</h' . $block->level . '>';
        }
        else if($block instanceof ParagraphBlock) {
            return '<p>' . StructuredText::asHtmlText($block->text, $block->spans, $linkResolver) . '</p>';
        }
        else if($block instanceof ListItemBlock) {
            return '<li>' . StructuredText::asHtmlText($block->text, $block->spans, $linkResolver) . '</li>';
        }
        else if($block instanceof ImageBlock) {
            return '<p>' . $block->view->asHtml($linkResolver) . '</p>';
        }
        else if($block instanceof EmbedBlock) {
            return $block->obj->asHtml();
        }
        return "";
    }

    public static function asHtmlText($text, $spans, $linkResolver=null) {
        if(!empty($spans)) {
            $starts = array();
            for($i = count($spans) - 1; $i >= 0; $i--) {
                array_push($starts, $spans[$i]);
            }

            $endings = array();
            $result = "";
            $pos = 0;

            $getStartAndEnd = function($span, $linkResolver=null) {
                if($span instanceof StrongSpan) {
                    return array("<strong>", "</strong>");
                }
                if($span instanceof EmSpan) {
                    return array("<em>", "</em>");
                }
                if($span instanceof HyperlinkSpan) {
                    if($span->link instanceof WebLink) {
                        return array('<a href="' . $span->link->url . '">', '</a>');
                    }
                    else if($span->link instanceof MediaLink) {
                        return array('<a href="' . $span->link->url . '">', '</a>');
                    } else if($span->link instanceof DocumentLink) {
                        $url = $linkResolver ? $linkResolver($span->link) : '';
                        return array('<a href="' . $url .'">', '</a>');
                    } else if($span->link instanceof MediaLink) {
                        return array('<a href="' . $span->link->url .'">', '</a>');
                    }
                }
                return array('', '');
            };

            $peek = function($array) {
                return $array[count($array) - 1];
            };

            $peekStart = function($span) {
                return empty($span) ? PHP_INT_MAX : $span[count($span) - 1]->start;
            };

            $peekEnd = function($span) {
                return empty($span) ? PHP_INT_MAX : $span[count($span) - 1]->end;
            };

            while(!(empty($starts) && empty($endings))) {
                $next = min($peekStart($starts), $peekEnd($endings));
                if($next > $pos) {
                    $result = $result . substr($text, 0, $next - $pos);
                    $text = substr($text, $next - $pos);
                    $pos = $next;
                } else {
                    $spansToApply = "";
                    while(min($peekStart($starts), $peekEnd($endings)) == $pos) {
                        if(!empty($endings) && $peek($endings)->end == $pos) {
                            $spansToApply = $spansToApply . $getStartAndEnd(array_pop($endings), $linkResolver)[1];
                        }
                        else if(!empty($starts) && $peek($starts)->start == $pos) {
                            $start = array_pop($starts);
                            array_push($endings, $start);
                            $spansToApply = $spansToApply . $getStartAndEnd($start, $linkResolver)[0];
                        }
                    }
                    $result = $result . $spansToApply;
                }
            }
            return $result . (strlen($text) > 0 ? $text : '');
        } else {
            return $text;
        }
    }

    public static function parseSpan($json) {
        $type = $json->type;
        $start = $json->start;
        $end = $json->end;

        if("strong" == $type) {
            return new StrongSpan($start, $end);
        }

        if("em" == $type) {
            return new EmSpan($start, $end);
        }

        $link;
        if("hyperlink" == $type) {
            $linkType = $json->data->type;
            if("Link.web" == $linkType) {
                $link = WebLink::parse($json->data->value);
            } else if("Link.document" == $linkType) {
                $link = DocumentLink::parse($json->data->value);
            } else if("Link.file" == $linkType) {
                $link = MediaLink::parse($json->data->value);
            }
        }

        if(isset($link)) {
            return new HyperlinkSpan($start, $end, $link);
        } else {
            return null;
        }
    }

    public static function parseText($json) {
        $text = $json->text;
        $spans = array();
        foreach($json->spans as $spanJson) {
            $span = StructuredText::parseSpan($spanJson);
            if(isset($span)) {
                array_push($spans, $span);
            }
        }
        return new ParsedText($text, $spans);
    }

    public static function parseBlock($json) {
        if($json->type == 'heading1') {
            $p = StructuredText::parseText($json);
            return new HeadingBlock($p->text, $p->spans, 1);
        }

        if($json->type == 'heading2') {
            $p = StructuredText::parseText($json);
            return new HeadingBlock($p->text, $p->spans, 2);
        }

        if($json->type == 'heading3') {
            $p = StructuredText::parseText($json);
            return new HeadingBlock($p->text, $p->spans, 3);
        }

        if($json->type == 'heading4') {
            $p = StructuredText::parseText($json);
            return new HeadingBlock($p->text, $p->spans, 4);
        }

        if($json->type == 'paragraph') {
            $p = StructuredText::parseText($json);
            return new ParagraphBlock($p->text, $p->spans);
        }

        if($json->type == 'list-item') {
            $p = StructuredText::parseText($json);
            return new ListItemBlock($p->text, $p->spans, false);
        }

        if($json->type == 'o-list-item') {
            $p = StructuredText::parseText($json);
            return new ListItemBlock($p->text, $p->spans, true);
        }

        if($json->type == 'image') {
            $view = ImageView::parse($json);
            return new ImageBlock($view);
        }

        if($json->type == 'embed') {
            return new EmbedBlock(Embed::parse($json));
        }

        return null;
    }

    public static function parse($json) {
        $blocks = array();
        foreach($json as $blockJson) {
            $maybeBlock = StructuredText::parseBlock($blockJson);
            if(isset($maybeBlock)) {
                array_push($blocks, $maybeBlock);
            }
        }
        return new StructuredText($blocks);
    }
}

class Group {
    private $maybeTag;
    private $blocks;

    function __construct($maybeTag, $blocks) {
        $this->maybeTag = $maybeTag;
        $this->blocks = $blocks;
    }

    public function addBlock($block) {
        array_push($this->blocks, $block);
    }

    public function __get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }
}

class ParsedText {

    private $text;
    private $spans;

    function __construct($text, $spans) {
        $this->text = $text;
        $this->spans = $spans;
    }

    public function __get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }
}

// BLOCKS

interface Block {}

class HeadingBlock implements Block {

    private $text;
    private $spans;
    private $level;

    function __construct($text, $spans, $level) {
        $this->text = $text;
        $this->spans = $spans;
        $this->level = $level;
    }

    public function __get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }
}

class ParagraphBlock implements Block {

    private $text;
    private $spans;

    function __construct($text, $spans) {
        $this->text = $text;
        $this->spans = $spans;
    }

    public function __get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }
}

class ListItemBlock implements Block {

    private $text;
    private $spans;
    private $ordered;

    function __construct($text, $spans, $ordered) {
        $this->text = $text;
        $this->spans = $spans;
        $this->ordered = $ordered;
    }

    public function __get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }
}

class ImageBlock implements Block {

    private $view;

    function __construct($view) {
        $this->view = $view;
    }

    public function __get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }
}

class EmbedBlock implements Block {

    private $obj;

    function __construct($obj) {
        $this->obj = $obj;
    }

    public function __get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }
}

// SPAN

interface Span {}

class EmSpan implements Span {

    private $start;
    private $end;

    function __construct($start, $end) {
        $this->start = $start;
        $this->end = $end;
    }

    public function __get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }
}

class StrongSpan implements Span {

    private $start;
    private $end;

    function __construct($start, $end) {
        $this->start = $start;
        $this->end = $end;
    }

    public function __get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }
}

class HyperlinkSpan implements Span {

    private $start;
    private $end;
    private $link;

    function __construct($start, $end, $link) {
        $this->start = $start;
        $this->end = $end;
        $this->link = $link;
    }

    public function __get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }
}

?>