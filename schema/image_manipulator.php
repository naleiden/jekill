<?php

require_once("../base/HTML.php");

$html = new HTML("", "css/image_manipulator.css");

$html->import("js/image_manipulator.js");
$html->import("js/jquery.js");
$html->import("js/MouseAdapter.js");
$html->import("js/utils.js");
$html->import("js/HttpRequest.js");
$html->import("js/color.js");
$html->import("js/schema.js");
$html->import("js/windowing.js");

$html->import_style("css/util.css");

if (!isset($_REQUEST['output_image'])) 
	$output_image = "working_image.jpg";
else $output_image = $_REQUEST['output_image'];

$return_URL = $_REQUEST['return_URL'];

if (!isset($_REQUEST['image_URL'])) {
	echo "Please specify a filename via \$_REQUEST[image_URL].";
	exit;
}
$image_URL = $_REQUEST['image_URL'];

$image_size = getimagesize($_SERVER['DOCUMENT_ROOT'] . $image_URL);
$source_width = $image_size[0];
$source_height = $image_size[1];

$width = $_REQUEST['width'];
$height = $_REQUEST['height'];
$callback = stripslashes($_REQUEST['callback']);
$zoom = isset($_REQUEST['zoom']) ? $_REQUEST['zoom'] : 1.0;
$resize_width = isset($_REQUEST['max_width']) ? $_REQUEST['max_width'] : 800;
$automatic_resize = isset($_REQUEST['automatic_resize']) ? $_REQUEST['automatic_resize'] : 1;

$working_image_name = /* time() . */ "working_image.jpg";
copy($_SERVER['DOCUMENT_ROOT'] . $image_URL, $working_image_name);

$indicator_width = round($source_width/2);
$indicator_height = round($source_height/2);

if (($source_width > $resize_width) && ($zoom != "") && $automatic_resize) {
	$link = $html->a()->href("image_manipulator.php?image_URL={$image_URL}&auto_resize=0")->content("here");
	// echo "This picture has been automatically resized due to excessive size. To prevent this resize, click " . $link->html() . ".<BR>";
	$zoom = $resize_width/$source_width;
	$resize_height = $source_height * $zoom;
	$resize_script = $html->script()->content("\$(\"document\").ready(function () { \$(\"#crop_source\").css(\"width\", $resize_width).css(\"height\", $resize_height); })");
	$html->script->add($resize_script);

	$indicator_width = round($resize_width/2);
	$indicator_height = round($resize_height/2);

}

$source_image = $html->img()->id("crop_source")->src($image_URL . "?t=" . time());	// time: Force browser refresh.

if ($width != "")
	$indicator_width = $width;
if ($height != "")
	$indicator_height = $height;

$source_width_input = $html->hidden()->id("source_width")->value($source_width);
$source_height_input = $html->hidden()->id("source_height")->value($source_height);
$width_input = $html->text()->id("crop_width")->size(1)->value($indicator_width)->onKeyUp("alterManually()");
$height_input = $html->text()->id("crop_height")->size(1)->value($indicator_height)->onKeyUp("alterManually()");
$hidden_width = $html->hidden()->id("crop_width")->value($width);
$hidden_height = $html->hidden()->id("crop_height")->value($height);
$hidden_output_image = $html->hidden()->id("output_image")->value($output_image);
$x_input = $html->text()->id("crop_x")->size(1)->value(0)->onKeyUp("alterManually()");
$y_input = $html->text()->id("crop_y")->size(1)->value(0)->onKeyUp("alterManually()");
$zoom_input = $html->text()->id("zoom")->size(1)->value(100 * $zoom)->onKeyUp("recalculateZoom()");
$return_URL_input = $html->hidden()->id("return_URL")->value($return_URL);
$callback_input = $html->hidden()->id("callback")->value($callback);

$filename = $html->hidden()->id("filename")->value($image_URL);
$preview_filename = $html->hidden()->id("preview_filename");

$frame_div = $html->div()->id("image_frame");
$source_div = $html->div()->add($source_image);
$indicator_div = $html->div()->id("indicator")->class("dragable");	// ->onMouseDown("initializeDrag(event)");
$xy_indicator_div = $html->div()->content("(0, 0)")->id("xy_indicator");
$width_height_indicator_div = $html->div()->content("$indicator_width x $indicator_height")->id("width_height_indicator");

$indicator_div->add($width_height_indicator_div)->add($xy_indicator_div);

if ($zoom != "") {
	$source_width *= $zoom;
	$source_height *= $zoom;
}

$indicator_width_style = $indicator_width; // -2;
$indicator_height_style = $indicator_height; // -2;	// Account for borders.
$indicator_init = $html->script()->content("\$(\"document\").ready(function () { initializeIndicator($indicator_width_style, $indicator_height_style); })");

$width_down = $html->img()->src("images/down.jpg")->onMouseDown("startAlteration(thinner)")->onMouseOut("stopAlteration()")->onMouseUp("stopAlteration()");
$width_up = $html->img()->src("images/up.jpg")->onMouseDown("startAlteration(thicker)")->onMouseOut("stopAlteration()")->onMouseUp("stopAlteration()");
$height_down = $html->img()->src("images/down.jpg")->onMouseDown("startAlteration(shorter)")->onMouseOut("stopAlteration()")->onMouseUp("stopAlteration()");
$height_up = $html->img()->src("images/up.jpg")->onMouseDown("startAlteration(taller)")->onMouseOut("stopAlteration()")->onMouseUp("stopAlteration()");
$left = $html->img()->src("images/left.jpg")->onMouseDown("startAlteration(moveLeft)")->onMouseOut("stopAlteration()")->onMouseUp("stopAlteration()");
$right = $html->img()->src("images/right.jpg")->onMouseDown("startAlteration(moveRight)")->onMouseOut("stopAlteration()")->onMouseUp("stopAlteration()");
$down = $html->img()->src("images/down.jpg")->onMouseDown("startAlteration(moveDown)")->onMouseOut("stopAlteration()")->onMouseUp("stopAlteration()");
$up = $html->img()->src("images/up.jpg")->onMouseDown("startAlteration(moveUp)")->onMouseOut("stopAlteration()")->onMouseUp("stopAlteration()");
$fixed_width = $html->hidden()->id("fixed_width")->value($width > 0);
$fixed_height = $html->hidden()->id("fixed_height")->value($height > 0);
$zoom_out = $html->img()->src("images/minus.jpg")->onMouseDown("startAlteration(zoomIn)")->onMouseOut("stopAlteration()")->onMouseUp("stopAlteration()");
$zoom_in = $html->img()->src("images/plus.jpg")->onMouseOut("stopAlteration()")->onMouseOut("stopAlteration()")->onMouseOut("stopAlteration()");

$preview_button = $html->button()->value("Preview")->onClick("previewCrop()");
$save_button = $html->submit()->value("Save")->onClick("saveImage()");

$black_div = $html->div()->class("color_indicator black")->content("&nbsp;")->onClick("setIndicatorColor('#000000')");
$white_div = $html->div()->class("color_indicator white")->content("&nbsp;")->onClick("setIndicatorColor('#FFFFFF')");
$red_div = $html->div()->class("color_indicator red")->content("&nbsp;")->onClick("setIndicatorColor('#CC0000')");
$green_div = $html->div()->class("color_indicator green")->content("&nbsp;")->onClick("setIndicatorColor('#00CC00')");

$control_table = $html->table(4)->class("control")->style("position: absolute; right: 15px;");

$hidden_values = $html->div()->add($return_URL_input)->add($callback_input)->add($hidden_output_image)->add($filename)->add($preview_filename)->add($source_width_input)->add($source_height_input)->add($fixed_width)->add($fixed_height);
$control_table->add_datum($hidden_values, 4);

/* CROPPING */
if ($width == "") {
	$control_table->add_datum("Width:")->add_datum($width_down)->add_datum($width_input)->add_datum($width_up);
}
else {
	$control_table->add_datum($hidden_width, 4);
}

if ($height == "") {
	$control_table->add_datum("Height:")->add_datum($height_down)->add_datum($height_input)->add_datum($height_up);
}
else {
	$control_table->add_datum($hidden_height, 4);
}

/* Background Color */
$background_color = $html->text()->size(10)->id("image_background_color")->value("FFFFFF")->onkeyup("imageBackgroundColorChanged()")->onchange("imageBackgroundColorChanged()");
$background_color_div = $html->div()->content("#")->add($background_color);
$background_color_preview = $html->div()->id("background_color_preview")->style("border: solid #000000 1px; background-color: #FFFFFF; cursor: pointer; width 80px; height: 20px;")->onclick("loadColorChooser('image_background_color', '', 'imageBackgroundColorChanged')");

$validate_indicator = $html->checkbox()->id("validate_indicator_position")->checked("true");
$validate_indicator_label = $html->label()->for("validate_indicator_position")->style("font-size: 12px;")->content("Attempt to keep indicator within confines of image.");
$validate_indicator_label_div = $html->div()->style("width: 200px;")->add($validate_indicator_label);

$control_table->add_datum("X:")->add_datum($left)->add_datum($x_input)->add_datum($right);
$control_table->add_datum("Y:")->add_datum($down)->add_datum($y_input)->add_datum($up);
$control_table->add_datum("Zoom:")->add_datum("%" /* $zoom_out */)->add_datum($zoom_input->html())->add_datum("&nbsp;" /* $zoom_in */);
$control_table->add_datum("Indicator Color:", 4)->add_datum($black_div)->add_datum($white_div)->add_datum($red_div)->add_datum($green_div);
$control_table->add_datum($validate_indicator)->add_datum($validate_indicator_label_div, 3);
$control_table->add_datum("Background Color:", 4)->add_datum($background_color_div, 2)->add_datum($background_color_preview, 2);
$control_table->add_datum($preview_button, 2)->add_datum($save_button, 2);

$frame_div->add($source_div);
// $frame_div->style("width: {$source_width}px; height: {$source_height}px; overflow: hidden;");

if (($width != "" && $width > $source_width) || ($height != "" && $height > $source_height)) {
	$error = $html->div()->class("error")->style("font-size: 12px; width: 250px;")->content("This image ({$source_width} x {$source_height}) is not large enough to be cropped to the specified size ({$width} x {$height}).");
	$control_table->add_datum($error, 4);
}
else if ($width == $source_width && $height == $source_height) {
	$warning = $html->div()->class("warning")->style("font-size: 12px; width: 225px;")->content("This image has already been cropped to the specified size.");
	$control_table->add_datum($warning, 4);
}

$control_form = $html->form()->id("manipulator_form")->method("POST")->action("save_image_script.php")->add($control_table);
$control_div = $html->div()->id("control_panel")->add($control_form);
$preview_div = $html->div()->id("preview_frame");

$html->add($frame_div);
$html->add($control_div);
$html->add($preview_div);

$html->script->add($indicator_init);

echo $html->html();

?>