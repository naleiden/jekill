<?php

require_once("account-helper.inc");

if (!logged_in()) {
	header("Location: /login.php?dest={$_SERVER['SCRIPT_NAME']}");
	exit;
}

?>