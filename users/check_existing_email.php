<?php

session_start();

include_once("../base/define.php");
include_once("../base/mysql_connection.php");

$user_ID = $_SESSION[$LOGIN_ID];
$email = $_POST['email'];

/* If the username is the same as the previously registered username, and we are
   editing (i.e., not registering), allow a match with username associated with
   the current user_ID. */

if ($user_ID != "" && isset($_POST['edit'])) {
	$query = "SELECT username FROM users WHERE user_ID = '$user_ID'";
	$results = $mysql_connection->sql($query);
	if ($results->has_next()) {
		$row = $results->next();
		$previously_registered_email = $row['email'];
		if ($email == $previously_registered_email) {
			echo "0";
			exit;
		}
	}
}

$email_exists = $mysql_connection->count("user", "user_ID", "WHERE email = '$email'");

echo $email_exists;

?>