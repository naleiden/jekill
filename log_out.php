<?php

session_start();

require_once("base/define.php");

$permissions = $_SESSION["{$LOGIN_ID}_permissions"];

// unset($_SESSION[$LOGIN_ID]);
// unset($_SESSION["{$LOGIN_ID}_permissions"]);
session_unset();
setcookie("{$LOGIN_ID}_auth", "", time()-3600);

$location = "index.php";

header("Location: {$location}");
exit;

?>