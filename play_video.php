<?php

include_once("base/HTML.php");

$html = new HTML();

/* For flowplayer. */
$html->import("js/jquery.js");
$html->import("flowplayer/3.0/example/flowplayer-3.0.0.min.js");

$video_URL = $_REQUEST['video'];

if ($video_URL == "") {
	echo "You must specify a video URL.";
	exit;
}

$width = 475;
$height = 350;
if ($_REQUEST['width'] != "") {
	$width = $_REQUEST['width'];
	$height = 3/4 * $width;
}
$video_element = $html->videoNew($video_URL, "current_episode", $width, $height, "false");

$html->add($video_element);

echo $html->html();

?>