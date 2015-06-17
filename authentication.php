<?php

require_once("base/define.php");

session_start();

// echo $_SESSION["{$LOGIN_ID}_user_ID"] . " vs " . $_SESSION["{$LOGIN_ID}_permissions"];

if (/* $_SERVER['HTTPS'] != 'on' || */  $_SESSION["{$LOGIN_ID}_user_ID"] == "" || $_SESSION["{$LOGIN_ID}_permissions"] == "" || $_SESSION["{$LOGIN_ID}_permissions"] == 0) {
	if (isset($request_type) && $request_type == "ajax") {
		header("HTTP/1.1 401 Unauthorized");
	} else {
		header("Location: admin_login.php");
	}
	exit;
}

?>