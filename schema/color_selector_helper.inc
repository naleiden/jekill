<?php

function dec_hex ($dec) {
	$dec = round($dec);

	if ($dec > 255)
		return "FF";
	else if ($dec+0 <= 0)
		return "00";

	$hex = dechex($dec);
	if (strlen($hex) < 2)
		$hex = "0" . $hex;

	return $hex;
}

function get_color_palette ($color, $action="", $act_on="", $GRANULARITY=17) {
	global $html;

	$palette_div = $html->div()->id("color_palette");

	$dec_color = hexdec($color);
	$red = hexdec(substr($color, 0, 2));
	$green = hexdec(substr($color, 2, 2));
	$blue = hexdec(substr($color, 4));

	for ($y=0; $y<256; $y+=$GRANULARITY) {
		$dark_percentage = $y/256;

		for ($x=0; $x<256; $x+=$GRANULARITY) {
			$x_percentage = $x/256;
			$light_percentage = max($x_percentage - $dark_percentage, 0);
			$light_percentage = round($light_percentage, 2);
			$color_percentage = 1 - ($dark_percentage + $light_percentage);
			$color_percentage = round($color_percentage, 2);
			$light_correction = 256*$light_percentage;

			$r_corrected = ($red * $color_percentage) + $light_correction;
			$g_corrected = ($green * $color_percentage) + $light_correction;
			$b_corrected = ($blue * $color_percentage) + $light_correction;

			$corrected_color = dec_hex($r_corrected) . dec_hex($g_corrected) . dec_hex($b_corrected);

			$pixel = $html->div()->class("")->style("background-color: #{$corrected_color}"); 
			$pixel->onMouseOver("\$(this).addClass('selected')")->onMouseOut("\$(this).removeClass('selected')");
			$pixel->onClick("{$action}('{$act_on}', '{$corrected_color}')");

			$palette_div->add($pixel);
		}
	}

	return $palette_div;
}

?>