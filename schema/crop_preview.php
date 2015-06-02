<?php

require_once("../base/settings.php");

/* This file can go away later, and 'save_image_script.php' can just be called with the correct variables. */

$_REQUEST['crop_x'] = $_REQUEST['x'];
$_REQUEST['crop_y'] = $_REQUEST['y'];
$_REQUEST['crop_height'] = $_REQUEST['height'];
$_REQUEST['crop_width'] = $_REQUEST['width'];
$_REQUEST['image_background_color'] = $_REQUEST['background_color'];
$_REQUEST['zoom'] = $_REQUEST['zoom'] * 100;
$_REQUEST['output_image'] = $output_URL = "{$SETTINGS['JEKILL_ROOT']}/schema/crop_preview.jpg";

include("save_image_script.php");

/*
$filename = $_POST['filename'];
$x = $_POST['x'];
$y = $_POST['y'];
$source_width = $_POST['source_width'];
$source_height = $_POST['source_height'];
$width = $_POST['width'];
$height = $_POST['height'];
$background_color = $_POST['background_color'];
$zoom = $_POST['zoom'];
$x_start = 0;
$y_start = 0;

$extension = ".jpg";
$epoch_time = time();
$write_out = "crop_preview" . $extension;

$image_in = imagecreatefromjpeg($_SERVER['DOCUMENT_ROOT'] .$filename);
$image_out = imagecreatetruecolor($width, $height);
// echo "($width x $height), ($source_width x $source_height)";

// The bounding box exceeds the bound of the image. Only copy the portions of the bounding box that are inside of the image.
if ($height + $y > $source_height*$zoom) {
	$height = $source_height*$zoom - $y;
}
else if ($y < 0) {
	$height = $height + $y;
	$y_start = -1 * $y;
	$y = 0;
}

if ($width + $x > $source_width*$zoom) {
	$width = $source_width*$zoom - $x;
}
else if ($x < 0) {
	$width = $width + $x;
	$x_start = -1*$x;
	$x = 0;
}

if ($background_color != "") {
	$r = hexdec(substr($background_color, 0, 2));
	$g = hexdec(substr($background_color, 2, 2));
	$b = hexdec(substr($background_color, 4, 2));
	$background_color_handle = imagecolorallocate($image_out, $r, $g, $b);
	imagefill($image_out, 0, 0, $background_color_handle);
}

imagecopyresampled($image_out, $image_in, $x_start, $y_start, $x/$zoom, $y/$zoom, $width, $height, $width/$zoom, $height/$zoom);

imagejpeg($image_out, $write_out, 100);
imagedestroy($image_in);
imagedestroy($image_out);
*/

$time = time();
echo "<!-- ($x, $y) - {$_REQUEST['crop_width']} x {$_REQUEST['crop_height']} --><br /><img src='{$output_URL}?time=$time' />";	//  style='border: solid #000000 1px;' />";

?>