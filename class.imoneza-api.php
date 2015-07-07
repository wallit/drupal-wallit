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
abstract class iMonezaApi {
  public $options;
  public $privateKey;
  public $publicKey;
  public $server;

  /**
   *
   * Constructor.
   *
   * @param array $options
   *    Array of API options for the particular api.
   * @param string $accessKey
   *    Access key for API.
   * @param string $secretKey
   *    Secret key for API.
   * @param string $server
   *    Fully qualified server name.
   */
  protected function __construct($options, $accessKey, $secretKey, $server) {
    $this->options = $options;
    $this->accessKey = $accessKey;
    $this->secretKey = $secretKey;
    $this->server = $server;
  }

}
