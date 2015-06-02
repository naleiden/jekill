<?php

include("../passive_authentication.php");

$directory = $_REQUEST['directory'];
$files = explode(",", $_REQUEST['filename']);

foreach ($files AS $file) {
	$full_path = "{$_SERVER['DOCUMENT_ROOT']}{$file}";

	if (is_dir($full_path))
		rmdir($full_path);
	else unlink($full_path);
}

?>