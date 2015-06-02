<?php

require_once("base/define.php");

session_start();

if (/* $_SERVER['HTTPS'] != 'on' || */ $_SESSION["{$LOGIN_ID}_user_ID"] == "" || $_SESSION["{$LOGIN_ID}_permissions"] <= 0) {
	// echo "{$LOGIN_ID}_user_ID" . " " . $_SESSION["{$LOGIN_ID}_user_ID"] . " vs " . $_SESSION["{$LOGIN_ID}_permissions"];
	exit;
}

?>