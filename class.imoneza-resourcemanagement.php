<?php
/**
 * @file
 * Contains the iMoneza resource management api.
 */

/**
 * Class iMonezaResourceManagement.
 *
 * Provides access to the Resource Management API for creating resources,
 * managing pricing, etc.
 */
class IMonezaResourceManagement extends IMonezaApi {

  /**
   * Constructor.
   */
  public function __construct() {
    $options = variable_get('imoneza_options');
    parent::__construct($options, $options['imoneza_rm_api_key_access'],
      $options['imoneza_rm_api_key_secret'], IMONEZA__RM_API_URL);
  }

  /**
   * Get the property metadata from iMoneza.
   *
   * @return mixed
   *    Decoded json object returned representing the property.
   *
   * @throws Exception
   *    Throws exception on I/O issues.
   */
  public function getProperty() {
    $request = new IMonezaRestfulRequest($this);
    $request->method = 'GET';
    $request->uri = '/api/Property/' . $this->accessKey;

    $response = $request->getResponse();

    if ($response->code == '404') {
      throw new Exception('An error occurred with the Resource '
        . 'Management API key. Make sure you have valid Resource '
        . 'Management API keys set in the iMoneza plugin settings.');
    }
    else {
      return json_decode($response->data, TRUE);
    }
  }

  /**
   * Get the resource metadata from iMoneza.
   *
   * @param string $external_key
   *    External key corresponding to the resource in the iMoneza system.
   * @param bool $include_property_data
   *    Boolean indicating whether to include property data in the response.
   *
   * @return array|mixed
   *    Returns a array corresponding to the json response.
   *
   * @throws Exception
   *    Throws an exception on I/O issues.
   */
  public function getResource($external_key, $include_property_data = FALSE) {
    $request = new IMonezaRestfulRequest($this);
    $request->method = 'GET';
    $request->uri = '/api/Property/' . $this->accessKey . '/Resource/' .
      $external_key;

    if ($include_property_data) {
      $request->getParameters['includePropertyData'] = 'true';
    }

    $response = $request->getResponse();

    if ($response->code == '404') {
      return array('IsManaged' => 0);
    }
    else {
      $ret_obj = json_decode($response->data, TRUE);
      $ret_obj['IsManaged'] = 1;

      return $ret_obj;
    }
  }

  /**
   * Creates a resource with iMoneza.
   *
   * @param string $external_key
   *    External key corresponding to the resource to be stored in iMoneza.
   * @param object $data
   *    Resource metadata.
   *
   * @return object
   *    Object containing iMoneza response data.
   *
   * @throws Exception
   *    Throws exception on I/O issues.
   */
  public function putResource($external_key, $data) {
    $request = new IMonezaRestfulRequest($this);
    $request->method = 'PUT';
    $request->uri = '/api/Property/' . $this->accessKey . '/Resource/' . $external_key;
    $request->body = json_encode($data);
    $request->contentType = 'application/json';

    $response = $request->getResponse();

    if ($response->code != '200') {
      if (IMONEZA__DEBUG) {
        echo '<html><pre>';
        print_r($response);
        echo '</pre></html>';
        die();
      }
      else {
        if (isset($response->data)) {
          $obj = json_decode($response->data);
          if (isset($obj->Message)) {
            throw new Exception($obj->Message);
          }
          else {
            throw new Exception('An error occurred while sending '
              . 'your changes to iMoneza.');
          }
        }
        else {
          throw new Exception('An error occurred while sending your '
            . 'changes to iMoneza.');
        }
      }
    }

    return $response;
  }

}
