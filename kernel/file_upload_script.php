<?php

include("../passive_authentication.php");

$explorer_name = $_REQUEST['explorer_name'];
$directory = $_REQUEST['directory'];
$callback = stripslashes($_REQUEST['callback']);

if ($directory != "" && $directory[strlen($directory)-1] != "/")
	$directory .= "/";

$uploaded_filename = $directory . $_FILES['kernel_upload']['name'];
//echo $uploaded_filename;
move_uploaded_file($_FILES['kernel_upload']['tmp_name'], $uploaded_filename) or die("Could not upload file");

header("Location: file_upload.php?explorer_name={$explorer_name}&directory={$directory}&callback={$callback}&file_uploaded=1");
exit;

?>