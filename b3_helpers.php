<?php
/**
 * Created by PhpStorm.
 * User: Chris Thompson
 * Date: 5/13/2015
 * Time: 11:33 AM
 */

function read_file_contents($file){
    ob_start();
    include $file;
    $retVal = ob_get_contents();
    ob_end_clean();
    return  $retVal;
}