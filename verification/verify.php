<?php

session_start();

include_once("verifier.php");

$verification = $_POST[verification];

$session_ID = session_ID();

$verified = verify($verification, $session_ID);

destroy_verification_image($session_ID);
if (!$verified)
  session_regenerate_id();

echo $verified;

?>