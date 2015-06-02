<?php

require_once("base/HTML.php");

/*******************************************/
/* Create required, client-specific files. */
/*******************************************/

if (!is_file("base/database_schema.php")) {
	$handle = fopen("base/database_schema.php", "w");
	fwrite($handle, "<?php

require_once(\"define.php\");
require_once(\"schema_manager.php\");

\$_SETTINGS = array(TABLE_NAME => \"settings\", TABLE_PROCESSOR => \"/schema/save_settings.php\");
\$COPY = array();
\$SCHEMA = array();

?>");
	fclose($handle);
}
if (!is_file("base/settings.php")) {
	$handle = fopen("base/settings.php", "w");
	fwrite($handle, "<?php\n\n?>");
	fclose($handle);
}

/******************************/
/* Poll for MySQL information */
/******************************/

$style = array("schema/control_panel.css", "schema/css/form.css");
$html = new HTML("Install Jekill", $style);

$username = $html->text()->id("username");
$password = $html->input()->type("password")->id("password");

$mysql_host = $html->text()->id("mysql_host");
$mysql_database = $html->text()->id("mysql_database");
$mysql_username = $html->text()->id("mysql_username");
$mysql_password = $html->input()->type("password")->id("mysql_password");

$dev_database = $html->text()->id("dev_database");
$dev_username = $html->text()->id("dev_username");
$dev_password = $html->input()->type("password")->id("dev_password");

$submit = $html->submit()->value("Submit");

/* Label Divs */
$username_label = $html->div()->class("field_label")->content("Username");
$password_label = $html->div()->class("field_label")->content("Password");

$mysql_host_label = $html->div()->class("field_label")->content("MySQL Host");
$mysql_database_label = $html->div()->class("field_label")->content("MySQL Database");
$mysql_username_label = $html->div()->class("field_label")->content("MySQL Username");
$mysql_password_label = $html->div()->class("field_label")->content("MySQL Password");

$dev_database_label = $html->div()->class("field_label")->content("Dev. MySQL Database");
$dev_username_label = $html->div()->class("field_label")->content("Dev. MySQL Username");
$dev_password_label = $html->div()->class("field_label")->content("Dev. MySQL Password");
$label_spacer = $html->div()->class("field_label")->content("&nbsp;");

/* Input Divs */
$username_input = $html->div()->class("field_input")->add($username);
$password_input = $html->div()->class("field_input")->add($password);

$mysql_host_input = $html->div()->class("field_input")->add($mysql_host);
$mysql_database_input = $html->div()->class("field_input")->add($mysql_database);
$mysql_username_input = $html->div()->class("field_input")->add($mysql_username);
$mysql_password_input = $html->div()->class("field_input")->add($mysql_password);

$dev_database_input = $html->div()->class("field_input")->add($dev_database);
$dev_username_input = $html->div()->class("field_input")->add($dev_username);
$dev_password_input = $html->div()->class("field_input")->add($dev_password);

$submit_input = $html->div()->class("field_input")->add($submit);

$username_div = $html->div()->class("field")->add($username_label)->add($username_input);
$password_div = $html->div()->class("field")->add($password_label)->add($password_input);
$mysql_host_div = $html->div()->class("field")->add($mysql_host_label)->add($mysql_host_input);
$mysql_database_div = $html->div()->class("field")->add($mysql_database_label)->add($mysql_database_input);
$mysql_username_div = $html->div()->class("field")->add($mysql_username_label)->add($mysql_username_input);
$mysql_password_div = $html->div()->class("field")->add($mysql_password_label)->add($mysql_password_input);
$dev_database_div = $html->div()->class("field")->add($dev_database_label)->add($dev_database_input);
$dev_username_div = $html->div()->class("field")->add($dev_username_label)->add($dev_username_input);
$dev_password_div = $html->div()->class("field")->add($dev_password_label)->add($dev_password_input);
$submit_div = $html->div()->class("field")->add($label_spacer)->add($submit_input);

$control_panel = $html->div()->id("control_panel_body");
$install_form = $html->form()->method("POST")->action("install_script.php");

$install_form->add($username_div);
$install_form->add($password_div);
$install_form->add($mysql_host_div);
$install_form->add($mysql_database_div);
$install_form->add($mysql_username_div);
$install_form->add($mysql_password_div);
$install_form->add($dev_database_div);
$install_form->add($dev_username_div);
$install_form->add($dev_password_div);
$install_form->add($submit_div);

$control_panel->add($install_form);

$html->add($control_panel);

echo $html->html();

?>