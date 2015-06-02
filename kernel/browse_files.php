<?php

session_start();

include_once("../base/define.php");

if (!isset($_SESSION["{$LOGIN_ID}_user_ID"]))
	include("../404_authentication.php");

session_start(); // For set_kernel() (Uses SESSION serialization)

include_once("../base/HTML.php");

include_once("kernel.php");

$extensions = $_REQUEST['extensions'];		// What extensions to allow, comma delimited. "" for all
$directory = $_REQUEST['directory'];		// What directory to browse
$show_directories = $_REQUEST['show_directories'];	// Whether or not to display directories
$callback = stripslashes($_REQUEST['callback']);	// What to do when $accept is clicked

if ($show_directories == "")
	$show_directories = true;

$kernel_name = "explorer";

if ($extensions != "") {
	$extensions = explode(",", $extensions);	// array(".jpg", ".jpeg", ".gif", ".png");
	$file_filter = new ExtensionFileFilter($extensions, $show_directories);
}

$html = new HTML();

$absolute_directory = "{$_SERVER['DOCUMENT_ROOT']}{$directory}";
$kernel = new Kernel($absolute_directory, $kernel_name, $file_filter);

$kernel->clear_default_dblClick();
$kernel->display_thumbnails();
Kernel::set_kernel($kernel);
$kernel_div = $kernel->get_kernel_divide();

$kernel_div->style("overflow: auto; width: 490px; height: 300px");

$delete = $html->img()->src("kernel/images/delete.jpg")->class("control_icon")->title("Delete Files")->onClick("deleteSelected()");
$refresh = $html->img()->src("kernel/images/refresh.jpg")->class("control_icon")->title("Refresh")->onClick("refreshKernel('{$kernel_name}')");
$create_folder = $html->img()->src("kernel/images/add_folder.jpg")->class("control_icon")->title("Add Folder")->onClick("createDirectory('{$kernel_name}')");
$accept = $html->img()->src("kernel/images/add.jpg")->class("control_icon")->value("Add Files")->onClick($callback);

$upload_frame = $html->iframe()->class("inline")->frameborder(0)->src("kernel/file_upload.php?explorer_name={$kernel_name}&directory={$_SERVER['DOCUMENT_ROOT']}/images&callback=window.parent.refreshKernel('{$kernel_name}')");	// explore(null, '{$kernel_name}', '{$absolute_directory}', '{$element_name}')");
// $upload_frame = $html->form()->method("POST")->enctype("multipart/form-data")->action("");	// For SWF Uploader

$file_select_div = $html->div()->class("right");
$control_div = $html->div()->class("right")->add($upload_frame)->content("<br />")->add($delete)->add($create_folder)->add($accept);

$control_div->set_padding(10);

$file_select_div->add($kernel_div);
$file_select_div->add($control_div);

echo $file_select_div->html();

?>