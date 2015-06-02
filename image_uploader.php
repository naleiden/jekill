<?php

include_once("base/HTML.php");

$form = new Form("image_upload", 1);

$file = new File("image");
$file->action = "onChange=\"uploadNewImage()\"";

$form->add_input("NO_LINE_BREAK", $file);
$form->action = "image_upload_script.php";

$form_div = new Divide("", $form->write_form());

if ($uploaded)
  $form_div->add_datum("<I>Image Uploaded</I>");

$header = new Header("");
$html = new HTMLDocument($header);

$html->add_divide($form_div);

$html->import("javascript/utils.js");
$html->add_script("function uploadNewImage () {
  var form = getElement(\"image_upload\");
  form.submit();
}");

echo $html->write_html();

?>