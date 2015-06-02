<?php

require_once("../base/define.php");
require_once("../base/email.php");
require_once("../base/mysql_connection.php");

$email_address = $_POST['email'];

$new_password = "";

$LETTER_POOL = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

for ($i=0; $i<8; $i++) {
  $index = rand(0, 45);
  $new_password .= $LETTER_POOL[$index];
}

$user_count = $mysql_connection->count("user", "user_ID", "WHERE email = '$email_address'");

if ($user_count < 1) {
	echo "0";
	exit;
}

$query = "UPDATE user SET password = PASSWORD('$new_password') WHERE email = '$email_address'";
$mysql_connection->query($query);

$body = "This is an automatically generated message from {$COMPANY_NAME}. Please do not reply to this email.<P>A new password has been generated for you at <a href=\"{$COMPANY_URL}\">{$COMPANY_DOMAIN}</A>.<P>Your new password is:<BR><B><FONT color=\"#990000\">{$new_password}</FONT></B><P>This password can be changed by logging in and viewing your <a href=\"{$COMPANY_URL}\">Account</A>.";

$email = new Email($email_address, "{$COMPANY_NAME} - Password reset", $body, "do_not_reply@{$COMPANY_DOMAIN}", "", $COMPANY_NAME);

echo $email->send();

?>