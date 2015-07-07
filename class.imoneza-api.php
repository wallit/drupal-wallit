<?php

/**
 * Class iMonezaApi
 *
 * Abstract base class for all API classes
 */
abstract class iMonezaApi
{
    public $options;
    public $privateKey;
    public $publicKey;
    public $server;

    /**
     *
     * Constructor
     *
     * @param $options
     * @param $accessKey
     * @param $secretKey
     * @param $server
     */
    protected function __construct($options, $accessKey, $secretKey, $server) {
        $this->options = $options;
        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
        $this->server = $server;
    }
}
