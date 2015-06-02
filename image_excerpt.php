<?php

$URL = $_GET['url'];
$width = $_GET['w'];
$height = $_GET['h'];
$zoom = $_GET['z'];
$color = $_GET['c'];
$x = $_GET['x'];	// Currently Unused
$y = $_GET['y'];	// Currently Unused
$output = $_GET['o'];	// Output filename.
$quality = $_GET['q'];
$align = $_GET['a'];

require_once("base/util.php");

if (isset($_GET['url']) && (isset($_GET['w']) || isset($_GET['h']) || isset($_GET['z'])))
	image_excerpt($URL, $width, $height, $zoom, $color, $output, $quality, $x, $y, $align);

?>