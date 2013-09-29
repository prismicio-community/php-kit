<?php

namespace prismic;

require_once(VENDORS_PATH . "/fragments.php");

if (!function_exists('curl_init')) {
    throw new \Exception('Prismic needs the CURL PHP extension.');
}
if (!function_exists('json_decode')) {
    throw new \Exception('Prismic needs the JSON PHP extension.');
}

class API {

    private $data;

    function __construct($data) {
        $this->data = $data;
    }

    public function refs($ref) {
        $refs = $this->data->refs;
        foreach($refs as $ref) {
            $refs[$ref->label] = $ref;
        }
        unset($ref);
        return $refs;
    }

     public function bookmarks() {
        return $this->data->bookmarks;
    }

    public function master() {
        $masters = array_filter($this->data->refs, function ($ref) {
            return $ref->isMasterRef == true;
        });
        return $masters[0];
    }

    public function forms() {
        $forms = $this->data->forms;
        foreach($forms as $key => $form) {
            $f = new Form(
                isset($form->name) ? $form->name : NULL,
                $form->method,
                isset($form->rel) ? $form->rel : NULL,
                $form->enctype,
                $form->action,
                $form->fields
            );
            $forms->$key = new SearchForm($this, $f, $f->defaultData());
        }
        return $forms;
    }

    public static function get($url) {
        $response = WS::get($url);
        if (!$response->data) {
            throw new \Exception("HTTP Error: " . $http_status);
        }

        $apiData = new ApiData(
            $response->data->refs,
            $response->data->bookmarks,
            $response->data->types,
            $response->data->tags,
            $response->data->forms,
            $response->data->oauth_initiate,
            $response->data->oauth_token
        );
        return new API($apiData);
    }
}

class SearchForm {

    private $api;
    private $form;
    private $data;

    function __construct($api, $form, $data) {
        $this->api = $api;
        $this->form = $form;
        $this->data = $data;
    }

    public function ref($ref) {
        $data = $this->data;
        $data['ref'] = $ref;
        return new SearchForm($this->api, $this->form, $data);
    }

    private static function parseResult($result) {
        return array_map(function($json) {
            return Document::parse($json);
        }, $result);
    }

    public function submit() {
        if($this->form->method == 'GET' && $this->form->enctype == 'application/x-www-form-urlencoded' && $this->form->action) {
            $url = $this->form->action . '?' . http_build_query($this->data);
            $response = WS::get($url);
            if($response->status != 200) {
                throw new \Exception("Http error: " . $response->status);
            } else {
                return self::parseResult($response->data);
            }
        } else {
            throw new \Exception("Form type not supported");
        }
    }

    public function query($q) {
        function strip($str) {
            $trimmed = trim($str);
            $drop1 = substr($trimmed, 1, strlen($trimmed));
            $dropR1 = substr($drop1, 0, strlen($drop1) - 1);
            return $dropR1;
        }

        $field = $this->form->fields->q;
        $maybeDefault = property_exists($field, "default") ? $field->default : NULL;
        $q1 = isset($maybeDefault) ? strip($maybeDefault) : "";
        $data = $this->data;
        $data['q'] = '[' . $q1 . strip($q) . ']';
        return new SearchForm($this->api, $this->form, $data);
    }
}

class Document {

    private $id;
    private $type;
    private $href;
    private $tags;
    private $slugs;
    private $fragments;

    function __construct($id, $type, $href, $tags, $slugs, $fragments) {
        $this->id = $id;
        $this->type = $type;
        $this->href = $href;
        $this->tags = $tags;
        $this->slugs = $slugs;
        $this->fragments = $fragments;
    }

    public function slug() {
        return $this->slugs[0];
    }

    public function containsSlug($slug) {
        $found = array_filter($this->slugs, function($s) {
            return $s == $slug;
        });
        return count($found) > 0;
    }

    public function get($field) {
        if (!array_key_exists($field, $this->fragments)) {
            return null;
        }
        return $this->fragments[$field];
    }

    public function getText($field) {
        if (!array_key_exists($field, $this->fragments)) {
            return "";
        }
        return $this->fragments[$field]->asText();
    }

    public function getHtml($field, $linkResolver = null) {
        if (!array_key_exists($field, $this->fragments)) {
            return "";
        }
        return $this->fragments[$field]->asHtml($linkResolver);
    }

    public function getImage($field, $view) {
        if (!array_key_exists($field, $this->fragments)) return null;
        $fragment = $this->fragments[$field];
        return $fragment->getImage($view);
    }

    public function asHtml() {
        $html = NULL;
        foreach($this->fragments as $field=>$v) {
            $html = $html . '<section data-field="'. $field .'">'. $this->getHtml($field) .'</section>';
        };
        return $html;
    }

    public static function parse($json) {
        $fragments = array();
        foreach($json->data as $type=>$fields) {
            foreach($fields as $key=>$value) {
                $fragment = null;
                if (is_object($value) && property_exists($value, "type")) {

                    if ($value->type === "Image") {
                        $data = $value->value;
                        $views = array();
                        foreach($value->value->views as $key => $jsonView) {
                            $views[$key] = ImageView::parse($jsonView);
                        }
                        $mainView = ImageView::parse($data->main, $views);
                        $fragment = new Image($mainView, $views);
                    }

                    if ($value->type === "Color") {
                        $fragment = new Color($value->value);
                    }

                    if ($value->type === "Number") {
                        $fragment = new Number($value->value);
                    }

                    if ($value->type === "Date") {
                        $fragment = new Date($value->value);
                    }

                    if ($value->type === "Text") {
                        $fragment = new Text($value->value);
                    }

                    if ($value->type === "Select") {
                        $fragment = new Text($value->value);
                    }

                    if ($value->type === "Embed") {
                        $fragment = Embed::parse($value->value);
                    }

                    if ($value->type === "Link.web") {
                        $fragment = WebLink::parse($value->value);
                    }

                    if ($value->type === "Link.document") {
                        $fragment = DocumentLink::parse($value->value);
                    }

                    if ($value->type === "StructuredText") {
                        $fragment = StructuredText::parse($value->value);
                    }
                }

                if (isset($fragment)) {
                    $fragments[$type . "." . $key] = $fragment;
                }
            }
        }
        return new Document($json->id, $json->type, $json->href, $json->tags, $json->slugs, $fragments);
    }

    public function __get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }
}

class Form {

    private $maybeName;
    private $method;
    private $maybeRel;
    private $enctype;
    private $action;
    private $fields;

    function __construct($maybeName, $method, $maybeRel, $enctype, $action, $fields) {
        $this->maybeName = $maybeName;
        $this->method = $method;
        $this->maybeRel = $maybeRel;
        $this->enctype = $enctype;
        $this->action = $action;
        $this->fields = $fields;
    }

    public function defaultData() {
        $dft = array();
        foreach($this->fields as $key=>$field) {
            if (property_exists($field, "default")) {
                $queryParameters[$key] = $field->default;
            }
        }
        return $dft;
    }

    public function __get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }
}

class ApiData {

    private $refs;
    private $bookmarks;
    private $types;
    private $tags;
    private $forms;
    private $oauth_initiate;
    private $oauth_token;

    function __construct($refs, $bookmarks, $types, $tags, $forms, $oauth_initiate, $oauth_token) {
        $this->refs = $refs;
        $this->bookmarks = $bookmarks;
        $this->types = $types;
        $this->tags = $tags;
        $this->forms = $forms;
        $this->oauth_initiate = $oauth_initiate;
        $this->oauth_token = $oauth_token;
    }

    public function __get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }
}

/** Utils */

class WS {

    /**
     * Default options for curl.
     */
    public static $CURL_OPTS = array(
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_USERAGENT      => 'prismic-php-0.1',
        CURLOPT_HTTPHEADER     => array('Accept: application/json')
    );

    public static function get($url) {
        $ch = curl_init();
        $opts = self::$CURL_OPTS;
        $opts[CURLOPT_URL] = $url;

        // disable the 'Expect: 100-continue' behaviour. This causes CURL to wait
        // for 2 seconds if the server does not support this header.
        if (isset($opts[CURLOPT_HTTPHEADER])) {
            $existing_headers = $opts[CURLOPT_HTTPHEADER];
            $existing_headers[] = 'Expect:';
            $opts[CURLOPT_HTTPHEADER] = $existing_headers;
        } else {
            $opts[CURLOPT_HTTPHEADER] = array('Expect:');
        }

        curl_setopt_array($ch, $opts);
        $result = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $data = json_decode($result);
        return new WSResponse($status, $data);
    }
}

class WSResponse {

    private $status;
    private $data;

    function __construct($status, $data) {
        $this->status = $status;
        $this->data = $data;
    }

    public function __get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }
}