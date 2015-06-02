<?php

session_start();

include_once("kernel.php");

$name = $_POST['name'];
$directory = $_POST['directory'];

/*
if (!startsWith($directory, "..")) {
	$directory = "../" . $directory;	// ..: we are currently in the kernel/ directory.
}
*/

$kernel = Kernel::get_kernel();

$html = new HTML();

$kernel->set_directory($directory);

$kernel->set_kernel($kernel);

$browser = $kernel->get_kernel_divide();

echo $browser->html();

?>