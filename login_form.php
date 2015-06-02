<?php

$login_header = $html->hgroup()->add( $html->h1()->content("Login") );

if (!$message && isset($_SESSION['message'])) {
	$message = $_SESSION['message'];
	unset($_SESSION['message']);
}

$destination = (isset($_REQUEST['dest'])) ? $_REQUEST['dest'] : $destination;
if ($destination != "" && $message == "" && $error == "" && $_SERVER['PHP_SELF'] != "/register.html")
	$message = "Please log in to continue.";

$email = $html->text()->id("login_email")->value($client_email);
$password = $html->input()->type("password")->id("login_password");
$login = $html->submit()->class("button")->src("/images/v3/login-button.png")->value("Log In");	//->onClick("login()");
// $login = $html->input()->type("image")->src("/images/v3/login-button.png")->value("Log In");	//->onClick("login()");
$destination_hidden = $html->hidden()->id("destination")->value($destination);
// $register_link = $html->a()->href("/register.php?dest={$destination}")->content("Register here.");
$reminder_link = $html->a()->href("javascript: sendEmailPasswordReminder()")->content("Get an Email Reminder");

$email_label = $html->div()->class("field_label")->content("Username or E-mail");
$password_label = $html->div()->class("field_label")->content("Password");
$label_spacer = $html->div()->class("field_label spacer")->content("&nbsp;");

$email_input = $html->div()->class("field_input")->add($email);
$password_input = $html->div()->class("field_input")->add($password);
$login_input = $html->div()->class("field_input")->add($destination_hidden)->add($login)->content("<div id=\"register_option\"><br />Not registered?<br/><a href=\"/register.php?dest={$destination}\">Register here</a>.</div>")->content("<p>Forgot Your Password?<br />")->add($reminder_link);

if (isset($error)) {
	$show_error_script = $html->script()->type("text/javascript")->content("\$(document).ready( function () { \$(\".error\").slideDown() } )");
	$html->script->add($show_error_script);
}

$message_hidden = (isset($message)) ? "" : " hidden";
$message_div = $html->div()->id("message")->class("message{$message_hidden}")->content($message);
$error_div = $html->div()->class("error")->id("error")->content($error);
$email_div = $html->div()->class("field")->add($email_label)->add($email_input);
$password_div = $html->div()->class("field")->add($password_label)->add($password_input);
$control_div = $html->div()->class("field")->add($label_spacer)->add($login_input);

$login_form = $html->form()->id("login_form")->method("POST")->action("login_script.php")->onsubmit("return login()")->add($message_div)->add($error_div)->add($email_div)->add($password_div)->add($control_div);

?>