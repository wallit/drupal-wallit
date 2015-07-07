<?php
/**
 * Contains the iMoneza resource management api
 * @file class.imoneza-resourcemanagement.php
 */
/**
 * Class iMonezaResourceManagement
 *
 * Provides access to the Resource Management API for creating resources,
 * managing pricing, etc.
 */
class iMonezaResourceManagement extends iMonezaApi
{

    /**
     * Constructor
     */
    public function __construct() {
        $options = variable_get('imoneza_options');
        parent::__construct($options, $options['imoneza_rm_api_key_access'],
            $options['imoneza_rm_api_key_secret'], IMONEZA__RM_API_URL);
    }

    /**
     * Get the property metadata from iMoneza.
     * @return mixed
     * @throws Exception
     */
    public function getProperty() {
        $request = new iMonezaRestfulRequest($this);
        $request->method = 'GET';
        $request->uri = '/api/Property/' . $this->accessKey;

        $response = $request->getResponse();

        if ($response->code == '404') {
            throw new Exception('An error occurred with the Resource '
                . 'Management API key. Make sure you have valid Resource '
                . 'Management API keys set in the iMoneza plugin settings.');
        } else {
            return json_decode($response->data, true);
        }
    }

    /**
     * Get the resource metadata from iMoneza.
     * @param $external_key
     * @param bool $include_property_data
     * @return array|mixed
     * @throws Exception
     */
    public function getResource($external_key, $include_property_data = false) {
        $request = new iMonezaRestfulRequest($this);
        $request->method = 'GET';
        $request->uri = '/api/Property/' . $this->accessKey . '/Resource/' .
            $external_key;

        if ($include_property_data)
            $request->getParameters['includePropertyData'] = 'true';

        $response = $request->getResponse();

        if ($response->code == '404') {
            return array('IsManaged' => 0);
        } else {
            $retObj = json_decode($response->data, true);
            $retObj['IsManaged'] = 1;
            return $retObj;
        }
    }

    /**
     * Creates a resource with iMoneza.
     * @param $external_key
     * @param $data
     * @return object
     * @throws Exception
     */
    public function putResource($external_key, $data) {
        $request = new iMonezaRestfulRequest($this);
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
            } else {
                if (isset($response->data)) {
                    $obj = json_decode($response->data);
                    if (isset($obj->Message))
                        throw new Exception($obj->Message);
                    else
                        throw new Exception('An error occurred while sending '
                        . 'your changes to iMoneza.');
                } else {
                    throw new Exception('An error occurred while sending your '
                        . 'changes to iMoneza.');
                }
            }
        }

        return $response;
    }
}
