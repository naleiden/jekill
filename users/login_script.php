<?php

require_once("../base/database_schema.php");
require_once("../base/define.php");
require_once("../base/mysql_connection.php");
require_once("../base/schema_manager.inc");

session_start();

$user_table = (isset($_POST['user_type'])) ? mysql_real_escape_string($_POST['user_type']) : "user";

$_POST["{$user_table}_authentication"] = $_POST['user_authentication'];
if (!SchemaManager::authenticate_form_data($user_table, $_POST)) {
	echo "Error: Unverified Login Request";
	exit;
}

// $user_field = (isset($_POST['login_type'])) ? mysql_real_escape_string($_POST['login_type']) : "username";
$password_field = (isset($_POST['password_field'])) ? mysql_real_escape_string($_POST['password_field']) : "password";
$login_type = mysql_real_escape_string($_POST['login_type']);
$user_identifier = mysql_real_escape_string($_POST['user_identifier']);
$password = mysql_real_escape_string($_POST['password']);
$remain_logged_in = ($_POST['remain_logged_in'] == "true");

$password_test = "'{$password}'";
if ($SCHEMA['user']['password'][FIELD_TYPE] == PASSWORD)
	$password_test = "password('{$password}')";		// Deprecated.

$table_unique_identifier = SchemaManager::get_table_unique_identifier($user_table);
$query = "SELECT * FROM {$user_table} WHERE {$login_type} = '{$user_identifier}' AND {$password_field} = {$password_test}";
// echo $query;

$results = $mysql_connection->sql($query);

if ($results->has_next()) {
	$row = $results->next();

	$user_ID = $row[$table_unique_identifier];
	session_regenerate_id(true);
	$user_auth_hash = sha1($LOGIN_SALT . $user_ID);

	$_SESSION["{$LOGIN_ID}_user_ID"] = $user_ID;
	$_SESSION["{$LOGIN_ID}_user_type"] = $user_table;
	$_SESSION["{$LOGIN_ID}_permissions"] = $row['permissions'];
	$_SESSION["{$LOGIN_ID}_user_agent"] = $_SERVER['HTTP_USER_AGENT'];
	$_SESSION["{$LOGIN_ID}_auth_hash"] = $user_auth_hash;

	if ($remain_logged_in) {
		// echo "Setting cookie: {$LOGIN_ID}_COOKIE";
		setcookie("{$LOGIN_ID}_auth", $user_auth_hash, time()+60*60*24*30);
	}
	else setcookie("{$LOGIN_ID}_auth", $user_auth_hash, time() - 3600);

	// $login_update = "UPDATE user SET last_login = current_login, current_login = UNIX_TIMESTAMP() WHERE {$table_unique_identifier} = '$user_ID'";
	// $mysql_connection->query($login_update);

	echo "1";	// $user_ID;
}
else {
	echo "0";
}

?>