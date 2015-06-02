<?php

require_once("base/HTML.php");

$directory = $_REQUEST['directory'];
$callback = stripslashes($_REQUEST['callback']);

$html = new HTML();

$upload_label = $html->div()->content("Upload file from computer");
$directory_hidden = $html->hidden()->id("directory")->value($directory);
$callback_hidden = $html->hidden()->id("callback")->value($callback);
$file_input = $html->file()->id("file")->onChange("this.form.submit()");

$form = $html->form()->method("post")->enctype("multipart/form-data")->action("save_uploaded_file.php")->add($upload_label)->add($directory_hidden)->add($callback_hidden)->add($file_input);

echo $form->html();

?>