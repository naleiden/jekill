<?php

session_start();

require_once("base/ad_manager.php");
require_once("base/mysql_connection.php");

$ad_ID = mysql_real_escape_string($_REQUEST['id']);

$time = time();
$ad_ID = substr($ad_ID, strlen($time), strlen($ad_ID));

/* LOGICAL ERROR. 'Click time' is page load time.

$click_time = substr($ad_ID, 0, strlen($time));
$difference = $time - $click_time;

$THRESHOLD = 20;	// Do not increment again after 20 seconds.

$increment = true;
if ($difference > $THRESHOLD)
	$increment = false;
*/

AdManager::redirect($ad_ID);	// , $increment);

?>