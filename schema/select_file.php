<?php

include("../passive_authentication.php");

session_start();

require_once("../base/database_schema.php");
require_once("../base/HTML.php");
require_once("../kernel/kernel.php");

$table_name = $_REQUEST['table_name'];
$field_name = $_REQUEST['field_name'];
$suffix = $_REQUEST['suffix'];
$extension = $_REQUEST['extensions'];
$directory = $_REQUEST['directory'];

$html = new HTML();

chdir($_SERVER['DOCUMENT_ROOT']);

$extensions = $SCHEMA[$table_name][$input_name][FIELD_EXTENSIONS];
if ($extensions != "")
	$file_filter = new ExtensionFileFilter($extensions);

if ($directory == "")
	$root_folder = $SCHEMA[$table_name][$field_name][ROOT_DIRECTORY];
else $root_folder = $directory;

$kernel = new Kernel($root_folder, "file_selector", $file_filter);

if ($extensions == "") {
	$kernel->clear_default_dblClick();
	$kernel->set_dblClick("*", "chooseFile");
/*
	$kernel->prepend_dblClick(".flv", "chooseFile");
	$kernel->prepend_dblClick(".mov", "chooseFile");
	$kernel->prepend_dblClick(".m4v", "chooseFile");
	$kernel->prepend_dblClick(".mp4", "chooseFile");
	$kernel->prepend_dblClick(".mp3", "chooseFile");
*/
}
else {
	if (!is_array($extensions))
		$kernel->prepend_dblClick($extensions, "chooseFile");
	else {
		foreach ($extensions as $extension)
			$kernel->prepend_dblClick($extension, "chooseFile");
	}
}

/* Serialize so dblClick preferences are preserved. */
$_SESSION['kernel'] = serialize($kernel);

$browse_input = $html->hidden()->id("browse_input")->value("{$field_name}{$suffix}");
$kernel_div = $kernel->get_kernel_divide();

echo $browse_input->html();
echo $kernel_div->html();

?>