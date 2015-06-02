<?php

include("../passive_authentication.php");

require_once("../base/HTML.php");

require_once("kernel.php");

$html = new HTML();

$kernel = Kernel::get_kernel();

echo getcwd() . " vs " . $kernel->get_directory();

$name = $_POST['name'];
$filename = "../" . $_POST['filename'];	// "../" escape from 'kernel' directory.

if (filesize($filename) > 500000) {
	echo "The selected file is too big to be edited here.";
	exit;
}

$lines = file($filename);

$file_content = "";
$i = 0;
foreach ($lines as $line) {
	$file_content .= "$line";
	$i++;
}

if ($i > 40)
	$num_lines = 35;
else if ($i < 10)
	$num_lines = 20;
else $num_lines = $i + 5;

$editor_name = $name . "_editor";
$editor = $html->textarea()->id($editor_name)->cols(80)->rows($num_lines)->content($file_content);
$save = $html->button()->value("Save")->onClick("saveFile('$editor_name', '$filename')");

$editor_div = $html->div()->add($editor->input())->add($save->input());

echo $editor_div->html();

?>