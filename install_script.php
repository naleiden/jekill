<?php

$username = $_REQUEST['username'];
$password = $_REQUEST['password'];
$mysql_host = $_REQUEST['mysql_host'];
$mysql_database = $_REQUEST['mysql_database'];
$mysql_username = $_REQUEST['mysql_username'];
$mysql_password = $_REQUEST['mysql_password'];
$dev_database = $_REQUEST['dev_database'];
$dev_username = $_REQUEST['dev_username'];
$dev_password = $_REQUEST['dev_password'];

$define_handle = fopen("base/define.php", "w");
fwrite($define_handle, "<?php

if (\$_SERVER['REMOTE_ADDR'] == \"127.0.0.1\") {
	\$DATABASE_NAME = \"{$dev_database}\";
	\$DATABASE_HOST = \"{$dev_host}\";
	\$DATABASE_USER = \"{$dev_username}\";
	\$DATABASE_PASSWORD = \"{$dev_password}\";
}
else {
	\$DATABASE_NAME = \"{$mysql_database}\";
	\$DATABASE_HOST = \"{$mysql_host}\";
	\$DATABASE_USER = \"{$mysql_username}\";
	\$DATABASE_PASSWORD = \"{$mysql_password}\";
}

\$LOGO_URL = \"images/logo.jpg\";
\$DEFAULT_MAX_PAGE_DISPLAY = 10;
\$DEFAULT_MAX_RESULTS = 15;
\$CONTROL_PANEL_WIDTH = 1000;

?>");
fclose($define_handle);

$USERNAME = $username;
$PASSWORD = $password;

include("database_init.php");

?>