<?php
/**
 * @file
 * Contains the generic request structure for API requests.
 */

/**
 * Class iMonezaRestfulRequest.
 *
 * Represents a request any iMoneza API.
 */
class IMonezaRestfulRequest {

  private $api;

  public $method;
  public $uri;
  public $getParameters;
  public $accept;
  public $body;
  public $contentType;

  /**
   * Constructor.
   *
   * @param object $api
   *    API object we're operating with.
   */
  public function __construct($api) {
    $this->api = $api;
    $this->getParameters = array();
    $this->method = 'GET';
    $this->accept = 'application/json';
    $this->body = '';
    $this->contentType = '';
  }

  /**
   * Returns the response from the API request.
   *
   * @return object
   *    Response object.
   *
   * @throws Exception
   *    Exception thrown for I/O issues.
   */
  public function getResponse() {
    $this->method = strtoupper($this->method);
    if ($this->method != 'GET' && $this->method != 'POST'
      && $this->method != 'PUT' && $this->method != 'DELETE'
    ) {
      throw new Exception('Invalid method');
    }

    if ($this->method != 'POST' && $this->method != 'PUT'
      && $this->body != ''
    ) {
      throw new Exception('You can only specify a body with a POST');
    }

    if ($this->method != 'POST' && $this->method != 'PUT'
      && $this->contentType != ''
    ) {
      throw new Exception('You can only specify a content type with a POST');
    }

    if ($this->body != '' && $this->contentType == '') {
      throw new Exception('You must provide a content type with a body');
    }

    if (strpos($this->uri, '?') !== FALSE) {
      throw new Exception('Illegal character in URI - make sure you '
        . 'include query string parameters in the getParams dictionary, '
        . 'not the URI');
    }

    $timestamp = gmdate('D, d M Y H:i:s \G\M\T');

    $sorted_params = $this->getSortedParams();
    $param_strings = $this->getParamString($sorted_params);

    $base_string = implode("\n", array(
        $this->method,
        $timestamp,
        strtolower($this->uri),
        $param_strings,
      )
    );

    $hash = base64_encode(
      hash_hmac('sha256', $base_string, $this->api->secretKey, TRUE));

    $url = $this->api->server . $this->uri;
    if (count($this->getParameters) > 0) {
      $get_param_strings = array();
      foreach ($this->getParameters as $key => $value) {
        $get_param_strings[] = $key . '=' . rawurlencode($value);
      }

      $url .= '?' . implode('&', $get_param_strings);
    }

    $raw_response = drupal_http_request($url,
      array(
        'method' => $this->method,
        'data' => $this->body,
        'headers' => array(
          'Timestamp' => $timestamp,
          'Authentication' => $this->api->accessKey . ':' . $hash,
          'Accept' => $this->accept,
          'Content-Type' => $this->contentType,
        ),
      ));

    return $raw_response;
  }

  /**
   * Sorts the stored parameters sorted in the proper order for authentication.
   *
   * @return array
   *    Sorted array of parameters stored for this request.
   */
  private function getSortedParams() {
    $sorted_params = array();
    foreach ($this->getParameters as $key => $value) {
      $sorted_params[strtolower($key)] = strtolower($value);
    }

    ksort($sorted_params);

    return $sorted_params;
  }

  /**
   * Creates the REST parameter string.
   *
   * @param array $sorted_params
   *    Sorted array of parameters to be inserted into a query string.
   *
   * @return string
   *    The query string query portion.
   */
  private function getParamString($sorted_params) {
    $param_strings = array();
    foreach ($sorted_params as $key => $value) {
      $param_strings[] = $key . '=' . $value;
    }

    return implode('&', $param_strings);
  }

}
