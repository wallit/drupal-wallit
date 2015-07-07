<?php
/**
 * Helper functions.
 * @file b3_helpers.php
 */

/**
 * Helper function to read a file into a string.
 * @param $file
 * @return string
 */
function imoneza_read_file_contents($file) {
    ob_start();
    include $file;
    $retVal = ob_get_contents();
    ob_end_clean();
    return $retVal;
}

/**
 * Class ImonezaStdObject
 *
 * Class that allows for some helpful dynamic method invocation stuff.
 */
class ImonezaStdObject
{
    public function __construct(array $arguments = array()) {
        if (!empty($arguments)) {
            foreach ($arguments as $property => $argument) {
                $this->{$property} = $argument;
            }
        }
    }

    public function __call($method, $arguments) {
        $arguments = array_merge(array("stdObject" => $this), $arguments);
        // Note: method argument 0 will always
        // referred to the main class ($this).
        if (isset($this->{$method}) && is_callable($this->{$method})) {
            return call_user_func_array($this->{$method}, $arguments);
        } else {
            throw
            new Exception("Fatal error: Call to undefined method "
            . "stdObject::{$method}()");
        }
    }
}
