<?php

include("../passive_authentication.php");

require_once("../base/HTML.php");

$explorer_name = $_REQUEST['explorer_name'];
$directory = $_REQUEST['directory'];
$callback = stripslashes($_REQUEST['callback']);
$pre_upload = stripslashes($_REQUEST['pre_upload']);
$file_uploaded = $_REQUEST['file_uploaded'];

$html = new HTML();
$html->import_style("../schema/css/control_panel.php");
$html->import("../schema/js/jquery.js");

$explorer_name_hidden = $html->hidden()->id("explorer_name")->value($explorer_name);
$directory_hidden = $html->hidden()->id("directory")->value($directory);
$callback_hidden = $html->hidden()->id("callback")->value($callback);
$file = $html->file()->id("kernel_upload")->onChange("uploadFile()");	// this.form.submit());
// $upload = $html->submit()->value("Upload");

$upload_message = "Upload a New File";
if ($file_uploaded)
	$upload_message = "File Uploaded. Upload Another File";

$form_label = $html->div()->id("upload_form_label")->content($upload_message);
$form = $html->form()->id("file_upload")->method("POST")->action("file_upload_script.php")->enctype("multipart/form-data");

$form->add($explorer_name_hidden)->add($directory_hidden)->add($callback_hidden)->add($file);	// ->add($upload);

$html->add($form_label)->add($form);

if ($file_uploaded != "")
	$html->script->add( $html->script()->type("text/javascript")->content($callback) );

$html->script->add( $html->script()->type("text/javascript")->content("function uploadFile () {
	var directory = window.parent.getCurrentDirectory(\"{$explorer_name}\");

	if (directory != \"\")
		\$(\"#directory\").val(directory);
	\$(\"#upload_form_label\").html(\"Uploading...\");
	\$(\"#file_upload\").submit();
}") );

echo $html->html();

?>