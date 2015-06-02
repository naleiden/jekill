<?php

$path = $_GET['p'];
$width = $_GET['w'];

if (strncmp($path, "images", 6))
	exit;

$image_in = imagecreatefromjpeg($path);

$imagesize = getimagesize($path);
// Compute height w/ aspect ratio
$height = $width * ($imagesize[1]/$imagesize[0]);

$image_in = imagecreatefromjpeg($path);
$image_out = imagecreatetruecolor($width, $height);

imagecopyresized($image_out, $image_in, 0, 0, 0, 0, $width, $height, $imagesize[0], $imagesize[1]);

header("Content-Type: image/jpeg");
imagejpeg($image_out);

imagedestroy($image_in);
imagedestroy($image_out);

?>