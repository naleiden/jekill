<?php

session_start();

include_once("../base/HTML.php");

include_once("verifier.php");

$divide_ID = $_POST['divide_ID'];

session_regenerate_ID();
$session_ID = session_id();

$directory_handle = opendir("./verification");
$now = time();
while ($file = readdir($directory_handle)) {
  if ($file[0] == '.')	// "." or ".." or system file.
	continue;

  $last_modified = filemtime("verification/$file");
  if ($last_modified < $now - 60)
    unlink("verification/$file");
}

$verification_image_filename = get_verification_image($session_ID);

$html = new HTML();

$verification_image = $html->img()->src("/verification/$verification_image_filename?time=" . time());
$verify_text = $html->text()->id("verify_text");
$reload_link = $html->a()->href("javascript: loadVerification('$divide_ID')")->content("Can't read it? Click here.");

$verification_div = $html->div()->add($verification_image)->content("<BR><SPAN class=\"small_text\">To Prevent Spam, Please Enter Text from Image Above</SPAN><BR>")->add($verify_text)->content("<BR>")->add($reload_link->write_link());

$verification_div->style("font-family: Arial; font-size: 8pt; padding: 3px");

echo $verification_div->html();

?>