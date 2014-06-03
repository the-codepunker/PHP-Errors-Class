<?php 
//Here's an example error, triggered to see how things work
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR ."Errors.php");
$errors = new Error();

include_once("a_non_existent_file.php");