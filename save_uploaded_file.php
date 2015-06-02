<?php

require_once("base/HTML.php");

$directory = $_REQUEST['directory'];
$callback = stripslashes($_REQUEST['callback']);
$filename = $_REQUEST['filename'];

$html = new HTML();

if ($filename == "")
	$filename = $_FILES['file']['name'];

copy($_FILES['file']['tmp_name'], "{$directory}/{$filename}") or die("Could not copy file: {$directory}/{$filename}");

$refresh_script = $html->script()->type("text/javascript")->content($callback);
$html->script->add($refresh_script);

echo $html->html();

?>