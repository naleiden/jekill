<?php

include_once("../base/HTML.php");

include("color_selector_helper.inc");

$html = new HTML("", "css/color_selector.css");

$action = $_POST['action'];
$act_on = $_POST['act_on'];
$params = $_POST['params'];
$directory = $_POST['directory'];

$hue_selector = $html->div()->id("hue_selector");

$SIZE_X = 1;
$SIZE_Y = 15;
$GRANULARITY = 17;
$HEX = "0123456789ABCDEF";

$color_count = 0;
$COLORS = array();

// F00 -> F0F
for ($b=0; $b<255; $b+=$GRANULARITY) {
	$color = "ff00" . dec_hex($b);
	$COLORS['color'][$color] = $color;
//echo "$color<br/>";
}

// F0F -> 00F
for ($r=255; $r>0; $r-=$GRANULARITY) {
	$color = dec_hex($r) . "00ff";
	$COLORS['color'][$color] = $color;
//echo "$color<br/>";
}

// 00F -> 0FF
for ($g=0; $g<255; $g+=$GRANULARITY) {
	$color = "00" . dec_hex($g) . "ff";
	$COLORS['color'][$color] = $color;
//echo "$color<br/>";
}

// 0FF -> 0F0
for ($b=255; $b>0; $b-=$GRANULARITY) {
	$color = "00ff" . dec_hex($b);
	$COLORS['color'][$color] = $color;
//echo "$color<br/>";
}

// 0F0 -> FF0
for ($r=0; $r<255; $r+=$GRANULARITY) {
	$color = dec_hex($r) . "ff00";
	$COLORS['color'][$color] = $color;
//echo "$color<br/>";
}

// FF0 -> F00
for ($g=255; $g>0; $g-=$GRANULARITY) {
	$color = "ff" . dec_hex($g) . "00";
	$COLORS['color'][$color] = $color;
//echo "$color<br/>";
}

foreach ($COLORS as $HUE_NAME => $HUE_ARRAY) {
	foreach ($HUE_ARRAY as $COLOR_CLASS => $COLOR) {
		$color_div = $html->div()->id($COLOR)->class($HUE_NAME);
		$color_div->style("background-color: #{$COLOR};");
		$color_div->onMouseOver("viewColor('{$COLOR}')")->onClick("loadColorPalette('{$COLOR}', '{$action}', '{$act_on}', '{$directory}')");
		$hue_selector->add($color_div);
	}
}
// $hue_selector->onClick("{$action}('{$act_on}', '{$params}', \$('#hex_preview').val())");

$hex_preview = $html->div()->id("hex_preview")->content("&nbsp;");
$color_viewer = $html->div()->id("color_viewer");

$color_controls = $html->div()->id("color_controls")->add($color_viewer)->add($hex_preview);

$palette = get_color_palette("FF0000", $action, $act_on);
$palette_container = $html->div()->id("palette_container")->add($palette);

$html->add($palette_container);
$html->add($hue_selector);
$html->add($color_controls);

$html->import("js/jquery.js");
$html->import("js/utils.js");
$html->import("js/color.js");

echo $html->html();

?>