<?php

session_start();

require_once("../base/define.php");
require_once("../base/HTML.php");

$user_ID = $_SESSION[$LOGIN_ID];

$html = new HTML();

if ($user_ID == "") {
	$login_success = $_POST['success'];

	$login_success = str_replace("\\", "", $login_success);

	$register_link = $html->a()->href("register.php")->content("Register Now");

	$page_input = $html->hidden()->id("destination_page")->value($destination_page);
	$email = $html->text()->id("login_email")->class("login_input");
	$password = $html->input()->type("password")->id("login_password")->class("login_input");
	$login = $html->button()->value("Log In")->id("login_button")->onClick("login($login_success)");
	$cancel = $html->button()->value("Cancel")->onClick("closeInnerWindow('login')");
	$forgot_password_link = $html->a()->href("javascript: forgotPassword()")->content("Forget your password?");

	$login_title = $html->img()->src("images/login.jpg")->alt("Please Login");
	$login_header = $html->div()->class("title")->add($login_title); // ->class("header")->content("Please Login");
	$login_copy = $html->div()->class("login_copy")->content("Existing users, please log in below. Not signed up yet? ")->add($register_link)->content(" - it's free and easy!");
	$login_error = $html->div()->class("error hidden")->id("login_error");
// $login_copy->set_padding(10);
	$remain_logged_in = $html->checkbox()->id("remain_logged_in");
	$keep_logged_in = $html->div()->class("")->add($remain_logged_in)->content(" Keep me logged in unless I log out.");

	$login_type = $html->hidden()->id("login_type")->value("email");

	$login_table = $html->table(2);
	$login_table->add_datum($login_header, 2);
	$login_table->add_datum($login_copy, 2);
	$login_table->add_datum($login_error, 2);
	$login_table->add_datum("Email")->add_datum($email);
	$login_table->add_datum("Password")->add_datum($password);
	$login_table->add_datum($keep_logged_in, 2);
	$login_table->add_datum($forgot_password_link, 2);
	$login_table->add_datum($login);
	$login_table->add_datum($cancel);

	$login_form_div = $html->div()->class("login")->add($page_input)->add($login_type)->add($login_table);

	echo $login_form_div->html();
}
else {
	$close = $html->button()->value("Close")->onClick("closeInnerWindow('login')");
	$log_out_link = $html->a()->href("log_out.php")->content("here");
	$logged_in_div = $html->div()->class("login login_copy center")->content("You are already logged in. Click ")->add($log_out_link)->content(" to log out.<P>")->add($close);
	echo $logged_in_div->html();
}

?>