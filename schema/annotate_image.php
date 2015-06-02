<?php

require_once("../base/HTML.php");
require_once("../base/mysql_connection.php");
require_once("../base/schema_manager.php");

$parent_table = $_REQUEST['parent_table'];
$image_field = $_REQUEST['image_field'];
$parent_ID = $_REQUEST['parent_ID'];
$annotation = $_REQUEST['annotation'];
$annotation_table = $_REQUEST['annotation_table'];
$annotation_suffix = $_REQUEST['annotation_suffix'];

$width = $_REQUEST['width'];
$height = $_REQUEST['height'];
$x = $_REQUEST['x'];
$y = $_REQUEST['y'];
$color = $_REQUEST['color'];

$MAX_IMAGE_WIDTH = 800;
$MAX_IMAGE_HEIGHT = 600;

if ($x == "") $x = 0;
if ($y == "") $y = 0;
if (!$width) $width = (isset($SETTINGS['DEFAULT_ANNOTATION_WIDTH'])) ? $SETTINGS['DEFAULT_ANNOTATION_WIDTH'] : 100;
if (!$height) $height = (isset($SETTINGS['DEFAULT_ANNOTATION_HEIGHT'])) ? $SETTINGS['DEFAULT_ANNOTATION_HEIGHT'] : 100;
if ($color == "") $color = (isset($SETTINGS['DEFAULT_ANNOTATION_COLOR'])) ? $SETTINGS['DEFAULT_ANNOTATION_COLOR'] : "FFFFFF";
$zoom = 1.0;

$parent_table_identifier = SchemaManager::get_table_unique_identifier($parent_table);
$image_URL = $mysql->get_field($parent_table, $image_field, "WHERE {$parent_table_identifier} = '{$parent_ID}'");

$imagesize = getimagesize("{$_SERVER['DOCUMENT_ROOT']}{$image_URL}");

if ($imagesize[0] > $MAX_IMAGE_WIDTH || $imagesize[1] > $MAX_IMAGE_HEIGHT) {
	if ($imagesize[0] > $MAX_IMAGE_WIDTH && $imagesize[1] > $MAX_IMAGE_HEIGHT) {
		if ($imagesize[1] > $imagesize[0])
			$resize_height = 1;
	}

	if (!$resize_height && $imagesize[0] > $MAX_IMAGE_WIDTH) {
		$zoom = $MAX_IMAGE_WIDTH/$imagesize[0];
		$imagesize[0] = $MAX_IMAGE_WIDTH;
		$imagesize[1] = round($imagesize[1] * $zoom);
	}
	else if ($imagesize[1] > $MAX_IMAGE_HEIGHT) {
		$zoom = $MAX_IMAGE_HEIGHT/$imagesize[1];
		$imagesize[1] = $MAX_IMAGE_HEIGHT;
		$imagesize[0] = round($imagesize[0] * $zoom);
	}

	// $zoom = round($zoom * 100);
	$image_URL = "/preview_image.php?url={$image_URL}&w={$imagesize[0]}&h={$imagesize[1]}";
}

$html = new HTML();

$color = "FFFFFF";

$resize_handle = $html->div()->id("annotation_resize_handle")->style("background-color: #{$color}; border: 1px solid #{$color}; bottom: -5px; height: 3px; position: absolute; right: -5px; width: 3px;");
$drag_handle = $html->div()->id("annotation_drag_handle")->style("background-color: #{$color}; cursor: move; height: 100%; width: 100%; opacity: 0.5;");
$annotation_label = $html->div()->style("color: #{$color}; position: absolute; /* top: -15px*/")->content($annotation);
$annotation = $html->div()->id("annotation")->class("draggable")->style("/* background-color: #{$color}; */ border: 1px solid #{$color}; height: {$height}px; left: {$x}px; /* opacity: 0.5; */ position: absolute; top: {$y}px; width: {$width}px; z-index: 999;")->add($annotation_label)->add($drag_handle)->add($resize_handle);
$annotation_zoom = $html->hidden()->id("annotation_zoom")->value($zoom);
$annotate_button = $html->button()->value("Annotate")->onClick("annotate('{$annotation_table}', '{$annotation_suffix}')");
$annotation_control = $html->div()->add($annotate_button)->add($zoom_message)->add($annotation_zoom);
$image_div = $html->div()->style("background-image: url('{$image_URL}'); height: {$imagesize[1]}px; position: relative; width: {$imagesize[0]}px;")->add($annotation);

$annotate_div = $html->div()->add($image_div)->add($annotation_control);

echo $annotate_div->html();

?>