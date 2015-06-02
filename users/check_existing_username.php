<?php

session_start();

include_once("base/mysql_connection.php");

$user_ID = $_SESSION['tdc_user_id'];

$mysql_connection = new TDC_MySQLConnection();

$username = $_POST['username'];

/* If the email is the same as the previously registered email, and we are
   editing (i.e., not registering), allow a match with email associated with
   the current designer_ID. */

if ($user_ID != "" && isset($_POST[edit])) {
  $query = "SELECT email FROM users WHERE user_ID = '$user_ID'";
  $results = $mysql_connection->sql($query);
  if ($results->has_next()) {
    $row = $results->next();
    $previously_registered_username = $row['username'];
    if ($email == $previously_registered_username) {
      echo "0";
      exit;
    }
  }
}

$username_exists = $mysql_connection->count("users", "user_ID", "WHERE username = '$username'");

echo $username_exists;

?>