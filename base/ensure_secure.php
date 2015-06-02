<?php

// Redirect to https
if($_SERVER['REMOTE_ADDR'] != "127.0.0.1" && $_SERVER['SERVER_PORT'] != '443') { 
	header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
 	exit();
}

?>