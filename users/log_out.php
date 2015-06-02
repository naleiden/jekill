<?php

session_start();

include("../base/define.php");

unset($_SESSION['$LOGIN_ID']);

header("Location: index.php");
exit;

?>