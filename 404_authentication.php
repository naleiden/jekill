<?php

require_once("base/define.php");

session_start();

if (/* $_SERVER['HTTPS'] != 'on' || */  $_SESSION[$LOGIN_ID] == "" || $_SESSION["{$LOGIN_ID}_permissions"] == "" || $_SESSION["{$LOGIN_ID}_permissions"] == 0) {
	// header("HTTP/1.0 404 Not Found");
	header("Status: 404 Not Found");
	include("404.php");
	exit;
}

?>