<?php

namespace prismic;

if (!function_exists('curl_init')) {
    throw new \Exception('Prismic needs the CURL PHP extension.');
}
if (!function_exists('json_decode')) {
    throw new \Exception('Prismic needs the JSON PHP extension.');
}

class API {

    private $data;

    /**
     * @param $url full qualified URL of the Prismic server to access
     */
    function __construct($data) {
        $this->data = $data;
    }

    /**
     * @return mixed Array of the references present on the server
     */
    public function refs($ref) {
        $refs = $this->data->refs;
        foreach($refs as $ref) {
            $refs[$ref->label] = $ref;
        }
        unset($ref);
        return $refs;
    }

    /**
     * @return array of bookmarks present on the server
     */
    public function bookmarks() {
        return $this->data->bookmarks;
    }

    /**
     * @return the master reference
     */
    public function master() {
        $masters = array_filter($this->data->refs, function ($ref) { return $ref->isMasterRef == true; });
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

    /**
     * @param $ref
     * @param $id
     * @return mixed A prismatic\Document if found, null otherwise
     */
    public function document($ref, $id) {
        return $this->forms()->everything->query($ref, "[[at(document.id, \"" . $id . "\")]]")[0];
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

    function ref($ref) {
        $data = $this->data;
        $data['ref'] = $ref;
        return new SearchForm($this->api, $this->form, $data);
    }

    private static function parseResult($json) {
        $documents = array();
        foreach($json as $doc) {
            array_push($documents, new Document($doc));
        }
        return $documents;
    }

    function submit() {
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

    function query($ref, $q = null) {
        if (property_exists($ref, "ref")) {
            $ref = $ref->ref;
        }
        $queryParameters = array();
        foreach($this->form->fields as $key=>$field) {
            if (property_exists($field, "default")) {
                $queryParameters[$key] = $field->default;
            }
        }
        $queryParameters["ref"] = $ref;
        if ($q) {
            $queryParameters["q"] = $q;
        }
        $jsonDocList = json_decode(API::get($this->form->action . "?" . http_build_query($queryParameters)));
        $documents = array();
        foreach($jsonDocList as $jsonDoc) {
            array_push($documents, new Document($jsonDoc));
        }
        return $documents;
    }
}

class Document {

    private $data;
    private $fragments;

    function __construct($data) {
        $this->data = $data;
        $this->fragments = array();
        foreach($this->data->data as $type=>$fields) {
            foreach($fields as $key=>$value) {
                $fragment = null;
                if (is_object($value) && property_exists($value, "type")) {
                    if ($value->type === "Link") {
                        $fragment = new Link($value->value);
                    }
                    if ($value->type === "Number") {
                        $fragment = new Number($value->value);
                    }
                    if ($value->type === "Color") {
                        $fragment = new Color($value->value);
                    }
                    if ($value->type === "Image") {
                        $fragment = new Image($value->value);
                    }
                    if ($value->type === "StructuredText") {
                        $fragment = new StructuredText($value->value);
                    }
                }
                if ($fragment) {
                    $this->fragments[$type . "." . $key] = $fragment;
                }
            }
        }
    }

    public function id() {
        return $this->data->id;
    }

    public function slug() {
        return $this->data->slugs[0];
    }

    public function tags() {
        return $this->data->tags;
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
}

// Fragment types

class Fragment {

    public function getImage($view) {
        return null;
    }

    public function asText() {
        return "";
    }

    public function asHtml($linkResolver = null) {
        return "";
    }
}

class Link extends Fragment {

    private $data;

    function __construct($data) {
        $this->data = $data;
    }

    public function asHtml($linkResolver = null) {
        return "";
    }
}

class Number extends Fragment {

    private $data;

    function __construct($data) {
        $this->data = $data;
    }

    public function asText() {
        return $this->data;
    }

    public function asHtml($linkResolver = null) {
        return $this->data;
    }

}

class Color extends Fragment {

    private $data;

    function __construct($data) {
        $this->data = $data;
    }

    public function asHtml($linkResolver = null) {
        return "";
    }

}

class Image extends Fragment {

    private $data;

    function __construct($data) {
        $this->data = $data;
    }

    public function asHtml($linkResolver = null) {
        return "";
    }

    public function getImage($view) {
        if (property_exists($this->data->views, $view)) {
            return $this->data->views->$view;
        }
        return null;
    }

}

class StructuredText extends Fragment {

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

    // TODO: Resolve spans within blocks
    public function asHtml($linkResolver = null) {
        $result = array_map(function ($block) {
            if ($block->type === "paragraph") {
                return "<p>" . $block->text . "</p>";
            }
            if ($block->type === "heading1") {
                return "<h1>" . $block->text . "</h1>";
            }
            if ($block->type === "heading2") {
                return "<h2>" . $block->text . "</h2>";
            }
            if ($block->type === "heading3") {
                return "<h3>" . $block->text . "</h3>";
            }
            if ($block->type === "heading4") {
                return "<h4>" . $block->text . "</h4>";
            }
        }, $this->blocks);
        return join("\n\n", $result);
    }

    public function getImage($view) {
        // TODO: Find the first image
        return null;
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

    function defaultData() {
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