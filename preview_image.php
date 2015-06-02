<?php

$width = $_REQUEST['w'];
$format = $_REQUEST['f'];
$height = $_REQUEST['h'];
$mode = $_REQUEST['m'];		// F: Force to width/height, disregard proportions, S: Shrink - do not resize if smaller than specified width / height
$URL = $_SERVER['DOCUMENT_ROOT'] . $_REQUEST['url'];

$imagesize = getimagesize($URL);

$format = "image/jpeg";
/* TODO
switch ($format) {
	case "png":
}
*/

if ($mode == "S") {
	/* If the image is already smaller than the size requested, do nothing. */
	if (($width == "" || $width >= $imagesize[0]) && ($height == "" || $height >= $imagesize[1])) {
		header("Content-Type: {$format}");
		readfile($URL);
		exit;
	}
}

// Compute width or height w/ aspect ratio. Height takes precedence.
if ($width != "" && $height != "") {
	$corrected_width = $height * ($imagesize[0]/$imagesize[1]);
	$corrected_height = $width * ($imagesize[1]/$imagesize[0]);

	if ($corrected_height <= $height)
		$height = $corrected_height;
	else if ($corrected_width <= $width)
		$width = $corrected_width;
	else {
		$width_error = $width/$corrected_width;
		$height_error = $height/$corrected_heigth;

		/* Find the one that is the closest to the desired dimensions. */
		if ($width_error < $height_error)
			$width = $corrected_width;
		else $height = $corrected_height;
	}
}
else if ($height != "") {
	$width = $height * ($imagesize[0]/$imagesize[1]);
	// echo "{$width} x {$height}"; exit;
}
else $height = $width * ($imagesize[1]/$imagesize[0]);

$extension = strtolower(substr($URL, -4));
if ($extension == ".jpg" || $extension == "jpeg")
	$image_in = imagecreatefromjpeg($URL);
else {
	switch ($extension) {
		case ".bmp":	
			$image_in = imagecreatefromwbmp($URL);	break;
			$format = "image/bmp";
			break;
		case ".gif":	
			$image_in = imagecreatefromgif($URL);	break;
			$format = "image/gif";
			break;
		case ".png":	
			$image_in = imagecreatefrompng($URL);	break;
			$format = "image/png";
			break;
	}
}
	
$image_out = imagecreatetruecolor($width, $height);

// imagecopyresized($image_out, $image_in, 0, 0, 0, 0, $width, $height, $imagesize[0], $imagesize[1]);
imagecopyresampled($image_out, $image_in, 0, 0, 0, 0, $width, $height, $imagesize[0], $imagesize[1]);

header("Content-Type: {$format}");
imagejpeg($image_out);

imagedestroy($image_in);
imagedestroy($image_out);

?>