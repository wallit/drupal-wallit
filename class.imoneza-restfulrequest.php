<?php

/**
 * Class iMonezaRestfulRequest
 *
 * Represents a request any iMoneza API.
 */
class iMonezaRestfulRequest
{

    private $api;

    public $method;
    public $uri;
    public $getParameters;
    public $accept;
    public $body;
    public $contentType;

    public function __construct($api) {
        $this->api = $api;
        $this->getParameters = array();
        $this->method = 'GET';
        $this->accept = 'application/json';
        $this->body = '';
        $this->contentType = '';
    }

    public function getResponse() {
        $this->method = strtoupper($this->method);
        if ($this->method != 'GET' && $this->method != 'POST'
            && $this->method != 'PUT' && $this->method != 'DELETE')

            throw new Exception('Invalid method');

        if ($this->method != 'POST' && $this->method != 'PUT'
            && $this->body != '')

            throw new Exception('You can only specify a body with a POST');

        if ($this->method != 'POST' && $this->method != 'PUT'
            && $this->contentType != '')

            throw new Exception('You can only specify a content type with a POST');

        if ($this->body != '' && $this->contentType == '')
            throw new Exception('You must provide a content type with a body');

        if (strpos($this->uri, '?') !== FALSE)
            throw new Exception('Illegal character in URI - make sure you '
            . 'include query string parameters in the getParams dictionary, '
            . 'not the URI');

        $timestamp = gmdate('D, d M Y H:i:s \G\M\T');

        $sortedParams = $this->getSortedParams();
        $paramStrings = $this->getParamString($sortedParams);

        $baseString = implode("\n", array($this->method, $timestamp,
            strtolower($this->uri), $paramStrings));

        $hash = base64_encode(
            hash_hmac('sha256', $baseString, $this->api->secretKey, true));


        $url = $this->api->server . $this->uri;
        if (count($this->getParameters) > 0) {
            $get_param_strings = array();
            foreach ($this->getParameters as $key => $value)
                $get_param_strings[] = $key . '=' . rawurlencode($value);

            $url .= '?' . implode('&', $get_param_strings);
        }

        $raw_response = drupal_http_request($url, array(
            'method' => $this->method,
            'data' => $this->body,
            'headers' => array(
                'Timestamp' => $timestamp,
                'Authentication' => $this->api->accessKey . ':' . $hash,
                'Accept' => $this->accept,
                'Content-Type' => $this->contentType
            )
        ));

        return $raw_response;
    }

    /**
     * Sorts the stored parameters sorted in the proper order for
     * authentication.
     *
     * @return array
     */
    private function getSortedParams() {
        $sorted_params = array();
        foreach ($this->getParameters as $key => $value)
            $sorted_params[strtolower($key)] = strtolower($value);
        ksort($sorted_params);

        return $sorted_params;
    }

    /**
     * @param $sorted_params
     * @return string
     */
    private function getParamString($sorted_params) {
        $param_strings = array();
        foreach ($sorted_params as $key => $value)
            $param_strings[] = $key . '=' . $value;

        return implode('&', $param_strings);
    }

}
