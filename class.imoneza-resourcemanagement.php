<?php
    
class iMoneza_ResourceManagement extends iMoneza_API {

    public function __construct()
    {
        $options = variable_get('imoneza_options');
        parent::__construct($options, $options['imoneza_rm_api_key_access'], $options['imoneza_rm_api_key_secret'], IMONEZA__RM_API_URL);
    }

    public function getProperty()
    {
        $request = new iMoneza_RestfulRequest($this);
        $request->method = 'GET';
        $request->uri = '/api/Property/' . $this->accessKey;

        $response = $request->getResponse();

        if ($response->code == '404') {
            throw new Exception('An error occurred with the Resource Management API key. Make sure you have valid Resource Management API keys set in the iMoneza plugin settings.');
        } else {
            return json_decode($response->data, true);
        }
    }

    public function getResource($externalKey, $includePropertyData = false)
    {
        $request = new iMoneza_RestfulRequest($this);
        $request->method = 'GET';
        $request->uri = '/api/Property/' . $this->accessKey . '/Resource/' . $externalKey;

        if ($includePropertyData)
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

    public function putResource($externalKey, $data)
    {
        $request = new iMoneza_RestfulRequest($this);
        $request->method = 'PUT';
        $request->uri = '/api/Property/' . $this->accessKey . '/Resource/' . $externalKey;
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
                        throw new Exception('An error occurred while sending your changes to iMoneza.');
                } else {
                    throw new Exception('An error occurred while sending your changes to iMoneza.');
                }
            }
        }

        return $response;
    }
}
