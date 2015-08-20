<?php
/**
 * @file
 * Contains the iMonezaApi base class.
 */

/**
 * Class iMonezaApi.
 *
 * Abstract base class for all API classes.
 */
abstract class IMonezaApi {
  public $options;
  public $privateKey;
  public $publicKey;
  public $server;

  /**
   * Constructor.
   *
   * @param object $options
   *    Array of API options for the particular api.
   * @param string $access_key
   *    Access key for API.
   * @param string $secret_key
   *    Secret key for API.
   * @param string $server
   *    Fully qualified server name.
   */
  protected function __construct($options, $access_key, $secret_key, $server) {
    $this->options = $options;
    $this->accessKey = $access_key;
    $this->secretKey = $secret_key;
    $this->server = $server;
  }

}
