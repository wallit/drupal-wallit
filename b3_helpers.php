<?php
/**
 * @file
 * Helper functions.
 */

/**
 * Helper function to read a file into a string.
 *
 * @param string $file
 *    Filename.
 *
 * @return string
 *    File contents.
 */
function imoneza_read_file_contents($file) {
  ob_start();
  include $file;
  $ret_val = ob_get_contents();
  ob_end_clean();

  return $ret_val;
}

/**
 * Class ImonezaStdObject.
 *
 * Class that allows for some helpful dynamic method invocation stuff.
 */
class ImonezaStdObject {

  /**
   * Constructor.
   *
   * @param array $argumens
   *    Arguments to be made into properties.
   */
  public function __construct(array $arguments = array()) {
    if (!empty($arguments)) {
      foreach ($arguments as $property => $argument) {
        $this->{$property} = $argument;
      }
    }
  }

  /**
   * Invoked on method calls.
   *
   * @param string $method
   *    Method name.
   * @param array $arguments
   *    Arguments.
   *
   * @return mixed
   *    Whatever the method being invoked returns.
   *
   * @throws Exception
   *    Thrown for any exceptions thrown by the method.
   */
  public function __call($method, $arguments) {
    $arguments = array_merge(array("stdObject" => $this), $arguments);
    // Note: method argument 0 will always
    // referred to the main class ($this).
    if (isset($this->{$method}) && is_callable($this->{$method})) {
      return call_user_func_array($this->{$method}, $arguments);
    }
    else {
      throw
      new Exception("Fatal error: Call to undefined method "
        . "stdObject::{$method}()");
    }
  }

}
