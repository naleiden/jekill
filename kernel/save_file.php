<?php

include("../passive_authentication.php");

$filename = $_POST['filename'];
$contents = $_POST['contents'];

$file_handle = fopen($filename, "w+");

/*
$contents = str_replace("\\\\", "\\", $contents);
$contents = str_replace("\\\"", "\"", $contents);
$contents = str_replace("\\\'", "\'", $contents);
*/
$contents = stripslashes($contents);

// echo $contents;

fwrite($file_handle, $contents);

fclose($file_handle);

?>