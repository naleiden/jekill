<?php

session_start();

$_SESSION[$LOGIN_ID] = $_COOKIE["{$LOGIN_ID}_COOKIE"];

?>