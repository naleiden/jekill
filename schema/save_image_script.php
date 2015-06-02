<?php

/*
foreach ($_REQUEST as $key => $value)
echo "$key => $value<BR>";
exit;
*/

$filename = $_REQUEST['filename'];
$x = $_REQUEST['crop_x'];
$y = $_REQUEST['crop_y'];
$source_width = $_REQUEST['source_width'];
$source_height = $_REQUEST['source_height'];
$width = $_REQUEST['crop_width'];
$height = $_REQUEST['crop_height'];
$zoom = isset($_REQUEST['zoom']) ? ($_REQUEST['zoom'] / 100) : 1.0;
$background_color = $_REQUEST['image_background_color'];
$return_URL = $_REQUEST['return_URL'];
$callback = stripslashes($_REQUEST['callback']);
$quality = isset($_REQUEST['quality']) ? $_REQUEST['quality'] : 100;
$x_start = 0;
$y_start = 0;

$dot_index = strrpos($filename, ".");
$extension = strtolower(substr($filename, $dot_index));
$last_slash = strrpos($filename, "/");
$write_out = substr($filename, 0, $last_slash+1);

// $write_out .= time() . $extension;
$write_out = isset($_REQUEST['output_image']) ? $_REQUEST['output_image'] : $_REQUEST['filename'];
$write_out = $_SERVER['DOCUMENT_ROOT'] . $write_out;

$output_dot_index = strrpos($write_out, ".");
$output_extension = strtolower(substr($write_out, $output_dot_index));

if ($output_extension == ".jpg" || $output_extension == "jpeg") {
	$output_function = "imagejpeg";
}
else if ($output_extension == ".gif") {
	$output_function = "imagegif";
}
else if ($output_extension == ".png") {
	$output_function = "imagepng";
	$quality = round($quality/100*9);
}

$absolute_URL = $_SERVER['DOCUMENT_ROOT'] . $filename;
if ($extension == ".jpg" || $extension == "jpeg") {
	$image_in = imagecreatefromjpeg($absolute_URL);
}
else if ($extension == ".gif") {
	$image_in = imagecreatefromgif($absolute_URL);
}
else if ($extension == ".png") {
	$image_in = imagecreatefrompng($absolute_URL);
}

$image_out = imagecreatetruecolor($width, $height);

/* The bounding box exceeds the bound of the image. Only copy the portions of the bounding box that are inside of the image. */
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

// echo "{$filename}: {$x}, {$y} - {$width} x {$height} => ({$output_function}) {$write_out}";
// echo "$output_function($image_out, $write_out, $quality)";
$output_function($image_out, $write_out, $quality);
imagedestroy($image_in);
imagedestroy($image_out);

if ($callback != "") {
	echo "<html><head></head><body><script>{$callback}</script></body></html>";
}
else if ($return_URL != "") {
	header("Location: {$return_URL}");
	exit;
}

?>
