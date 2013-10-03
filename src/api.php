<?php



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

    public static function post($url, $data) {
        $ch = curl_init();
        $opts = self::$CURL_OPTS;
        $opts[CURLOPT_URL] =  $url;
        $opts[CURLOPT_POST] = count($data);
        $opts[CURLOPT_POSTFIELDS] = http_build_query($data);
        curl_setopt_array($ch, $opts);
        $result = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $data = json_decode($result);
        return new WSResponse($status, $data);
    }

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

    public static function check($response) {
        if($response->status != 200) {
            if($response->status == 401) {
                throw new UnauthorizedException();
            }
            else if($response->status == 403) {
                throw new ForbiddenException();
            }
            else if($response->status == 404) {
                throw new NotFoundException();
            }
            else {
                throw new \Exception("HTTP error: " . $response->status);
            }
        }
    }
}





