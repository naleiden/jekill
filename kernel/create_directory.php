<?php

include("../passive_authentication.php");

$directory = $_REQUEST['directory'];
$directory_name = $_REQUEST['directory_name'];

chdir($directory);
mkdir($directory_name);

?>