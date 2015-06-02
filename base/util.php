<?php

function camelCase ($name) {
	$name_parts = preg_split("/[^a-z0-9]+/i", $name);
	$camel_case = strtolower($name_parts[0]);
	for ($i=1; $i<count($name_parts); $i++) {
		$camel_case .= ucwords($name_parts[$i]);
	}
	return $camel_case;
}

// -> 1 Week, 2 Weeks, etc.
function subscription_duration_to_text ($duration, $pluralize=false) {
	$time_units = array(DAY_SECONDS => "day", WEEK_SECONDS => "week", THIRTY_DAY_SECONDS => "month", YEAR_SECONDS => "year");

	$quantity = round($duration/DAY_SECONDS);
	$remainder = $duration % DAY_SECONDS;
	$divisor = DAY_SECONDS;
	foreach ($time_units AS $time_unit => $text) {
		$unit_remainder = $duration % $time_unit;
		if ($unit_remainder < $remainder
			// Is the divisor greater than the previous divisor, and the remainder less than the previous divisor?
			|| ($unit_remainder <= $divisor && $time_unit > $divisor)) {
			$quantity = round($duration/$time_unit);
			$remainder = $unit_remainder;
			$divisor = $time_unit;
		}

		$plural = ($pluralize && $quantity != 1) ? "s" : "";

		return "{$quantity} {$time_units[$divisor]}{$plural}";
	}

}

function curl_get ($url, $curl_opts=array()) {
	$curl_handle = curl_init();

	// set URL and other appropriate options
	curl_setopt($curl_handle, CURLOPT_URL, $url);
	curl_setopt($curl_handle, CURLOPT_HEADER, false);
	curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);

	// TODO: Test for https:
	// if ()
	curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);

	$response = curl_exec($curl_handle);

	curl_close($curl_handle);

	return $response;
}

function date_range_from_epoch ($from_epoch, $to_epoch) {
  $start_month = date("F", $from_epoch);
  $end_month = date("F", $to_epoch);
  $start_day = date("j", $from_epoch);
  $end_day = date("j", $to_epoch);
  $start_year = date("Y", $from_epoch);
  $end_year = date("Y", $to_epoch);

  if ($start_year != $end_year) {
    $date_range = "{$start_month} {$start_day}, {$start_year} - {$end_month} {$end_day}, {$end_year}";
  }
  else {
    if ($start_month == $end_month) {
      $date_range = "{$start_month}";
      if ($start_day == $end_day)
        $date_range .= " {$start_day}, {$start_year}";
      else $date_range .= " {$start_day} - {$end_day}, {$start_year}";
    }
    else $date_range = "{$start_month} {$start_day} - {$end_month} {$end_day}, {$end_year}";
  }
  return $date_range;
}

function format_epoch_duration ($epoch) {
  $seconds = $epoch%60;
  $epoch /= 60;
  $minutes = $epoch%60;
  $epoch /= 60;
  $hours = round($epoch, 0); // (round($epoch) > 0) ? round($epoch, 0) . ":" : "";

  if (strlen($hours) == 1)
    $hours = "0" . $hours;

  if ($minutes < 0)
    $minutes = "00";
  else if (strlen($minutes) == 1)
    $minutes = "0" . $minutes;

  if ($seconds < 0)
    $seconds = "00";
  else if (strlen($seconds) == 1)
    $seconds = "0" . $seconds;

  return "{$hours}:{$minutes}:{$seconds}";
}

// TODO: Replace this with str_pad
/* Pack 0's into IP bytes so that ranges can be numerically tested:
	$ip_range_floor <= $ip <= $ip_range_ceil
*/
function zero_flush ($num) {
	$length = strlen($num);
	if ($length == 0)
		$num = "000";
	else if ($length == 1)
		$num = "00" . $num;
	else if ($length == 2) {
		$num = "0" . $num;
	}

	return $num;
}

function url_implode (array $values, $key_value_glue="=", $variable_glue="&") {
  $imploded = "";

  foreach ($values AS $key => $value) {
    if ($i++) $imploded .= $variable_glue;

    $imploded .= $key . $key_value_glue . $value;
  }

  return $imploded;
}

if (!function_exists("hex2bin")) {
  function hex2bin ($h) {
  	if (!is_string($h)) return null;
  	$r='';
  	for ($a=0; $a<strlen($h); $a+=2) { $r.=chr(hexdec($h{$a}.$h{($a+1)})); }
  	return $r;
  }
}

function bound ($number, $min, $max) {
	if ($number < $min) {
		$number = $min;
	} else if ($number > $max) {
		$number = $max;
	}
	return $number;
}

function strip ($regex, $str) {
	$str = preg_replace($regex, "", $str);
	return $str;
}

function filter ($regex, $str, $default_value="", $success_func="", $failure_func="") {
	$num_matches = preg_match($regex, $str, $matches);
	if (!$num_matches) {
		if ($failure_func != "")
			$failure_func($matches);
		return $default_value;
	}
	else {
		if ($success_func != "")
			$success_func($matches);
		return $matches[0];
	}
}

function restrict ($value, $valid_values, $default="") {
	if (!isset($valid_values[$value]) && !in_array($value, $valid_values))
		return $default;
	else return $value;
}

function posssessive ($name) {
	// TODO: Check for 'y' ending
	if ($name[strlen($name)-1] == "s")
		return $name . "'";
	else return $name . "'s";
}

function pluralize ($name) {
	// TODO: Check for 'y' ending
	if ($name[strlen($name)-1] == "s")
		return $name . "es";
	else return $name . "s";
}

function get_file ($url, $local_filename) {
	$file_contents = $file = file_get_contents($url);
	$file_handle = fopen($local_filename, "w+");
	fwrite($file_handle, $file_contents);
	fclose($file_handle);
}

function generate_random_password ($length, $charset="") {
	$charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	$password = "";
	for ($i=0; $i<$length; $i++) {
		$index = rand(0, strlen($charset)-1);
		$password .= $charset[$index];
	}
	return $password;
}

function get_spamless_link ($address, $text="", $subject="", $body="", $cc="", $class="") {
	$name_parts = explode("@", $address);
	$name = $name_parts[0];
	$domain = $name_parts[1];

	if ($text == "")
		$text = "{$name}' + '@' + '{$domain}";	// Omit leading and trailing 's
	else $text = str_replace("'", "\\'", $text);

	$spamless_link = "<script language=\"javascript\">document.write('<a href=\"mailto:' + '{$name}' + '@{$domain}?subject={$subject}&body={$body}&cc={$cc}' + '\" class=\"{$class}\">' + '{$text}' + '</a>')</script>";
	return $spamless_link;
}

function js_image_preload ($array) {
	if (count($array) == 0)
		return "";
	else {
		$script = "if (document.images) {\n\tvar image = new Image();";
		foreach ($array AS $URL) {
			$script .= "\timage.src = \"{$URL}\";";
		}
		$script .= "}";
		return $script;
	}
}

function url_namify ($str) {
	$str = trim($str);
	$patterns = array( '/[\'|"]+/', '/[^a-z|^A-Z|^0-9]/');
	$replacements = array('', '-');
	$str = preg_replace($patterns, $replacements, $str);	// Remove non-alphanumeric
	$str = preg_replace('/-[-]+/', '-', $str);	// Replace two or more consecutive -'s with a single -
	if ($str[strlen($str)-1] == '-')
		$str = substr($str, 0, strlen($str)-1);	// Remove trailing -'s

	return $str;
}

/* Gets an exceprt from a string with embedded HTML. Splits on a space nearest
   to the requested index as possible. 

   $split - Split the string, and return both halves in an array
   $terminator - Add this to the end of the excerpt.
*/
function html_excerpt ($html_copy, $length, $split=0, $terminator="...") {
	$excerpt = $html_copy;
	$str_length = strlen($excerpt);
	if ($str_length > $length) {
		$space_index = strpos($excerpt, " ", $length);
		$tag_start = strrpos($excerpt, "<", $space_index-$str_length);	// Look backwards from length for '<'
		$tag_end = strrpos($excerpt, ">", $space_index-$str_length);	// Look backwards from length for '>'

		if ($space_index !== false) {	// The space is not beyond the end of the string.
			/* We are in the middle of an HTML tag. */
			if ($tag_end === false || $tag_end < $tag_start) {
				/* Find next ending tag. */
				$next_tag_end = strpos($excerpt, ">", $space_index);
				/* Set the cutoff to be immediately afterwards. */
				if ($next_tag_end !== false)
					$space_index = $next_tag_end+1;
			}
			$excerpt = substr($excerpt, 0, $space_index);
			$excerpt .= $terminator;
		}
		if ($split) {
			$second_part = substr($html_copy, $space_index);
			$excerpt_parts = array($excerpt, $second_part);
			return $excerpt_parts;
		}
	}
	return $excerpt;	
}

/* Capture the output of a file and return it as a value. */
function include_capture ($filename, $globals="", array $variables=null) {
	if (is_array($globals)) {
		foreach ($globals AS $global_var)
			global $$global_var;
	}

  if ($variables)
    extract($variables);

	//$old_error_level = error_reporting(0);	// Turn off error reporting & save old value
	ob_start();
  $DOCUMENT_ROOT = rtrim($_SERVER['DOCUMENT_ROOT'], "/");
  $filename = ltrim($filename, "/");
	include("{$DOCUMENT_ROOT}/{$filename}");
	$buffer = ob_get_contents();
	ob_end_clean();
	//error_reporting($old_error_level);
	return $buffer;
}

function is_valid_name ($name) {
	return (preg_match("/[a-z][a-z'\-\s]+/i", $name) > 0);
}

function is_valid_credit_card ($number) {
	$number = strip("/[^0-9]/", $number);

	$length = strlen($number);
	if ($length < 12)
		return false;

	$offset = 0;		// Even # digits: MasterCard, Visa
	if ($length%2) {	// Odd: American Express, etc.
		$offset = 1;
	}

	$sum = 0;
	$digit = 0;
	for ($i=0; $i<$lenth; $i++) {
		$digit = intval($number[$i]);
		if ($i%2 == $offset) {
			$digit *= 2;
		}
		if ($digit > 9) {
			$digit = intval($digit[0]) + intval($digit[1]);
		}
		$sum += $digit;
	}
	$remainder = $sum%10;

	return ($remainder == 0);
}

function is_valid_email ($email) {
	$email = strtolower($email);
	// return (preg_match("/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/i", $email) > 0);
  return (preg_match("/.+@.+\..+/i", $email) > 0);
}

function unescape ($value) {
	return str_replace("\\", "", $value);
}

function a_an ($str) {
	if (begins_with_vowel($str))
		return "an {$str}";
	return "a {$str}";
}

function begins_with_vowel ($str) {
	$vowels = "aeiouAEIOU";
	$match = preg_match("/^[{$vowels}]+[a-zA-Z]*/", $str);
	return ($match > 0);
}

function default_value ($value, $default_value) {
  if ($value == "")
    return $default_value;
  else return $value;
}

function get_xml ($url) {
	$ch = curl_init();
	
	// set URL and other appropriate options
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	/* Proper USER-AGENT for Feedburner. */
	curl_setopt($ch, CURL_HTTP_VERSION_1_1, true);
	curl_setopt($ch, CURLOPT_ENCODING, "gzip, deflate");
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.3) Gecko/2008092417 Firefox/3.0.3");
	
	$response = curl_exec($ch);
	
	curl_close($ch);
	
	return $response;
}

function formatDimension ($dimension) {
  $decimal_place = strpos($dimension, ".");
  if ($decimal_place < 0)
    return $decimal;

  $whole_fraction = explode(".", $dimension);
  $whole = $whole_fraction[0];
  $fraction = $whole_fraction[1];

  switch ($fraction) {
    case 000:   $fraction = ""; break;
    case 125:   $fraction = "<FONT style=\"font-size: 0.9em;\"><SUP>1</SUP>/<SUB>8</SUB></FONT>"; break;
    case 250:   $fraction = "<FONT style=\"font-size: 0.9em;\"><SUP>1</SUP>/<SUB>4</SUB></FONT>"; break;
    case 375:   $fraction = "<FONT style=\"font-size: 0.9em;\"><SUP>3</SUP>/<SUB>8</SUB></FONT>"; break;
    case 438:   $fraction = "<FONT style=\"font-size: 0.9em;\"><SUP>7</SUP>/<SUB>16</SUB></FONT>"; break;
    case 500:   $fraction = "<FONT style=\"font-size: 0.9em;\"><SUP>1</SUP>/<SUB>2</SUB></FONT>"; break;
    case 625:   $fraction = "<FONT style=\"font-size: 0.9em;\"><SUP>5</SUP>/<SUB>8</SUB></FONT>"; break;
    case 750:   $fraction = "<FONT style=\"font-size: 0.9em;\"><SUP>3</SUP>/<SUB>4</SUB></FONT>"; break;
    case 875:	$fraction = "<FONT style=\"font-size: 0.9em;\"><SUP>7</SUP>/<SUB>8</SUB></FONT>"; break;
  }
  return "$whole $fraction";
}


function get_value ($variable_name) {
  if (isset($_POST[$variable_name]))
    return $_POST[$variable_name];
  else return $_GET[$variable_name];
}

function dewordify ($text) {
  $text = str_replace("�", "'", $text);
  $text = str_replace("�", "&rdquo;", $text);
  $text = str_replace("�", "&ldquo;", $text);
  $text = str_replace("\\�", "&mdash;", $text);
  return $text;
}

function mysql_safe_html ($text) {
  $text = dewordify($text);
  $text = str_replace("\"", "&quot;", $text);
  $text = str_replace("'", "&#39;", $text);
  $text = str_replace("\\'", "&#39;", $text);
  $text = str_replace("\n\n", "<P>", $text);
  $text = str_replace("\n\n", "<P>", $text);
  $text = str_replace("\n", "<BR>", $text);
  return $text;
}

function checkboxValue ($value) {
  return ($value == "on") ? 1 : 0;
}

function resizeAndWatermark ($original_image, $watermark_image, $width, $height, $watermark_opacity, $watermark_x, $watermark_y, $output_image, $quality=100) {
  resizeJPEG($original_image, $width, $height, $output_image, $quality);
  watermarkJPEG($output_image, $watermark_image, $output_image, $watermark_opacity, $watermark_x, $watermark_y);
}

function resize_image ($original_image, $width, $height, $output_image, $quality=100) {
  $extension = get_extension($original_image);

  switch ($extension) {
    case "jpeg":
    case "jpg":
      resizeJPEG($original_image, $width, $height, $output_image, $quality);
      break;
    case ".gif":
      resizeGIF($original_image, $width, $height, $output_image, $quality);
  }
}

function compress ($original_image, $output_image, $quality=100) {

}

function format_filesize ($filesize) {
  if ($filesize > 1000000) {
    $filesize /= 1000000;
    $filesize = round($filesize, 1);
    $filesize .= " M";
  }
  else if ($filesize > 1000) {
    $filesize /= 1000;
    $filesize = round($filesize, 1);
    $filesize .= " Kb";
  }
  return $filesize;
}

function strip_nonnumeric ($str) {
  $stripped = "";
  for ($i=0; $i<strlen($str); $i++) {
    switch ($str[$i]) {
      case "1":
      case "2":
      case "3":
      case "4":
      case "5":
      case "6":
      case "7":
      case "8":
      case "9":
      case "0":
        $stripped .= $str[$i];
    }
  }
  return $stripped;
}

function format_telephone ($telephone_num) {
  $length = strlen($telephone_num);
  if ($length < 7)
    return "";
  else if ($length == 7)
    $area_code = "(314) ";
  else {
    $area_code = substr($telephone_num, 0, 3);
    $area_code = "($area_code) ";
    $telephone_num = substr($telephone_num, 3, $length);
    $length = strlen($telephone_num);
  }
  $first = substr($telephone_num, 0, 3);
  $last = substr($telephone_num, 3, $length);
  return $area_code . "$first-$last";
}

function format_mileage ($mileage) {
  $mileage_len = strlen($mileage);
  if ($mileage > 1000)
    $mileage = substr($mileage, 0, $milage_len-3) . "," . substr($mileage, $mileage_len-3, $mileage_len);
  else if ($mileage == 0)
    $mileage = " -- ";

  return $mileage;
}

function format_number ($num) {
  if (strstr($num, ".")) {
    $whole_decimal = explode(".", $num);
    $num = $whole_decimal[0];
    $decimal = "." . $whole_decimal[1];
  }
  $num_len = strlen($num);

  if ($num_len < 4)
    return $num;
  else {
    while ($num_len > 3) {
      $last_three = substr($num, $num_len-3, $num_len);
      $num = substr($num, 0, $num_len-3);
      $num_len = strlen($num);
      $formatted = $formatted . "," . $last_three;
    }
    $formatted = $num . $formatted;
  }
  return $formatted . $decimal;
}

function price_format ($price, $decimal=true) {
  if ($price == "0" || $price == 0 || $price == "")
    return "0.00";
  $price = round($price, 2);
  $decimalPointPos = stripos($price, ".");
  if (!$decimalPointPos || ($decimalPointPos == "")) {
    $decimalPointPos = strlen($price);
    $price .= ".00";
  }
  else if ($decimalPointPos == strlen($price)-2) {
    $price .= "0";
  }
  if (strlen($price) > $decimalPointPos +3)
    $price = substr($price, 0, $decimalPointPos+3);
  if ($price > 1000) {
    $price_len = strlen($price);
    $price = substr($price, 0, $price_len-6) . "," . substr($price, $price_len-6, $price_len);
  }
  if (!$decimal)
    $price = substr($price, 0, strlen($price)-3);
  return $price;
}

function endsWith ($str, $suffix ) {
  return (substr($str, strlen($str) - strlen($suffix)) == $suffix);
}

function startsWith ($str, $prefix) {
  return (substr($str, 0, strlen($prefix)) == $prefix);
}

function trimNonAlpha ($string) {
  for ($i=0; $i<strlen($string); $i++) {
    $char = $string[$i];
    switch ($char) {
      case " ":
      case ".":
      case ",":
      case "-":
      case "'":
      case "=":
      case "\"":
      case "\\":
      case "/":
      case "<":
      case ">":
      case "&":
      case "%":
      case "$":
      case "#":
      case "@":
      case "!":
      case "^":
      case "*":
      case "(":
      case ")": {
        $pre = substr($string, 0, $i);
        $post = substr($string, $i+1, strlen($string));
        $string = $pre . $post;
        $i--;
      }
      default:
    }
  }
  return $string;
}

function isAlphaChar ($char) {
    switch ($char) {
      case " ":
      case "'":
      case ".":
      case ",":
      case "-":
      case "'":
      case "=":
      case "\"":
      case "\\":
      case "/":
      case "<":
      case ">":
      case "!":
      case "@":
      case "#":
      case "$":
      case "%":
      case "&":
      case "*":
      case "(":
      case ")":
        return false;
      default:
        return true;
   }
}

function strip_HTML ($text) {
  $text = str_replace(">", "&gt;", $text);
  $text = str_replace("<", "&lt;", $text);
  return $text;
}

function get_resized_dimensions ($original_width, $original_height, $width, $height) {
  // scale evenly
  $ratio = $original_width / $original_height;
  if ($ratio >= 1 && $width != ""){
    $scale = $width / $original_width;
  }
  else {
    $scale = $height / $original_height;
  }
  // make sure its not smaller to begin with!
  if ($width >= $original_width && $height >= $original_height){
    $scale = 1;
  }
  $uniform_width = floor($scale*$original_width);
  $uniform_height = floor($scale*$original_height);
  return array($uniform_width, $uniform_height);
}

/*
	$align: L,R,T,B - if $zoom is FIT, $align allows you to move the image within the defined space . Centered by default.
*/
function image_excerpt ($URL, $width, $height, $zoom, $color, $output_path, $quality=100, $x="", $y="", $align="") {
	if ($zoom == "")
		$zoom = 100;

	if ($quality == "")
		$quality = 100;

	if (strpos($URL, $_SERVER['DOCUMENT_ROOT']) !== false)
		$absolute_URL = $URL;
	else $absolute_URL = $_SERVER['DOCUMENT_ROOT'] . $URL;

	$imagesize = getimagesize($absolute_URL);
	if ($height == "") {
		$height = $width/$imagesize[0] * $imagesize[1];
	}

	if ($zoom == "MAX") {	// Zoom in as much as possible within the confines of the image
		$height_ratio = $height/$imagesize[1];
		$width_ratio = $width/$imagesize[0];
		$zoom_pct = max($width_ratio, $height_ratio);
	}
	else if ($zoom == "FIT") {	// Fit the image inside of the specified dimensions with background color occupying unfilled space
		$width_zoom = $width/$imagesize[0];
		$height_zoom = $height/$imagesize[1];
		$zoom_pct = min($width_zoom, $height_zoom);
	}
	else $zoom_pct = $zoom/100;

	$extension = strtolower(substr($URL, -4));

	if ($output_path)
		$output_extension = strtolower(substr($output_path, -4));
	else $output_extension = $extension;

	if ($extension == ".jpg" || $extension == "jpeg") {
		$image_in = imagecreatefromjpeg($absolute_URL);
	}
	else if ($extension == ".gif") {
		$image_in = imagecreatefromgif($absolute_URL);
	}
	else if ($extension == ".png") {
		$image_in = imagecreatefrompng($absolute_URL);
	}

	if ($output_extension == ".jpg" || $output_extension == "jpeg") {
		$output_function = "imagejpeg";
		$output_type = "image/jpeg";
	}
	else if ($output_extension == ".gif") {
		$output_function = "imagegif";
		$output_type = "image/gif";
	}
	else if ($output_extension == ".png") {
		$output_function = "imagepng";
		$output_type = "image/png";
		$quality = 9-round($quality/100 * 9);	// PNG compression level
	}

	/* The dimensions of the resized image. */
	$zoom_width = ceil($imagesize[0] * $zoom_pct);
	$zoom_height = ceil($imagesize[1] * $zoom_pct);

	$zoomed = imagecreatetruecolor($zoom_width, $zoom_height);

	imagecopyresampled($zoomed, $image_in, 0, 0, 0, 0, $zoom_width, $zoom_height, $imagesize[0], $imagesize[1]);
	imagedestroy($image_in);

	$excerpt = imagecreatetruecolor($width, $height);

	if ($zoom == "FIT" && $color != "") {
		if ($color[0] == "-") {
			$color = substr($color, 1);
			$transparent_background = true;
		}
		$r = hexdec(substr($color, 0, 2));
		$g = hexdec(substr($color, 2, 2));
		$b = hexdec(substr($color, 4, 2));
		$background_color = imagecolorallocate($excerpt, $r, $g, $b);

		if ($transparent_background) {
			$output_type = "image/png";
			$output_function = "imagepng";
			imagecolortransparent($excerpt, $background_color);
		}
		imagefill($excerpt, 0, 0, $background_color);
		// imagefilledrectangle($excerpt, 0, 0, $width, $height);
	}

	
	$copy_start_x = ($x) ? $x : abs(round(($zoom_width/2)-($width/2)));
	$copy_start_y = ($y) ? $y : abs(round(($zoom_height/2)-($height/2)));

	// TODO: $align (CENTER, LEFT, RIGHT, TOP, BOTTOM) to specify position of a FIT image.
	if ($zoom == "FIT") {
		if ($align) {
			if (strpos($align, "L") !== false)
				$copy_start_x = 0;
			else if (strpos($align, "R") !== false)
				$copy_start_x = $width-$zoom_width;
			if (strpos($align, "T") !== false)
				$copy_start_y = 0;
			else if (strpos($align, "B") !== false)
				$copy_start_y = $height-$zoom_height;
		}
		imagecopy($excerpt, $zoomed, $copy_start_x, $copy_start_y, 0, 0, $zoom_width, $zoom_height);
	}
	else imagecopy($excerpt, $zoomed, 0, 0, $copy_start_x, $copy_start_y, $width, $height);

	//$color = imagecolorallocate($excerpt, 255, 0, 0);	// For debugging.
	//imagestring($excerpt, 1, 5, 5, $zoom_pct, $color);

	if ($output_path != "") {
		$output_function($excerpt, $output_path, $quality);
	}
	else {
		header("Content-Type: {$output_type}");
		$output_function($excerpt);
	}

	imagedestroy($zoomed);
	imagedestroy($excerpt);
}

function read_image ($source) {
  $extension = get_extension($source);

  switch ($extension) {
    case ".jpg":
    case ".jpeg":
      $image_resource = imagecreatefromjpeg($source);
      break;
    case ".gif":
      $image_resource = imagecreatefromgif($source);
      break;
    case ".png":
      $image_resource = imagecreatefrompng($source);
  }
  return $image_resource;
}

/* If no height is specified, height is calculated using current aspect ratio. If no output_filename
   is specified, the image is saved to the original filename. If output_filename is OUTPUT_IMAGE, the
   appropriate headers are set, and the image is output directly. */
function resize_image_uniform ($input_filename, $width, $height="", $output_filename="", $quality=100) {
  $size = getimagesize($input_filename);
  if ($width == "" && $height == "")
	return;

  if ($width == "")
	$width = $height * ($size[0]/$size[1]);
  if ($height == "")
	$height = width * ($size[1]/$size[0]);
  if ($output_filename == "")
	$output_filename = $input_filename;
  $resized_dimensions = get_resized_dimensions($size[0], $size[1], $width, $height);
  $im_in = read_image($input_filename);
  $im_out = imagecreatetruecolor($resized_dimensions[0], $resized_dimensions[1]);

  imagecopyresampled($im_out, $im_in, 0, 0, 0, 0, $resized_dimensions[0], $resized_dimensions[1], $size[0], $size[1]);
  imagedestroy($im_in);
  save_image($im_out, $output_filename, $quality);
  imagedestroy($im_out);
}

function resizeJPEG ($source, $width, $height, $target, $quality=100) {
  $size = getimagesize($source);
  $resized_dimensions = get_resized_dimensions($size[0], $size[1], $width, $height);
  $im_in = imagecreatefromjpeg($source);
  $im_out = imagecreatetruecolor($resized_dimensions[0], $resized_dimensions[1]);
  imagecopyresampled($im_out, $im_in, 0, 0, 0, 0, $resized_dimensions[0], $resized_dimensions[1], $size[0], $size[1]);
  imagedestroy($im_in);
  save_image($im_out, $target, $quality);
  imagedestroy($im_out);
}

function resizeGIF ($source, $width, $height, $target, $quality=100) {
  $size = getimagesize($source);
  $resized_dimensions = get_resized_dimensions($size[0], $size[1], $width, $height);
  $im_in = imagecreatefromgif($source);
  $im_out = imagecreatetruecolor($resized_dimensions[0], $resized_dimensions[1]);
  imagecopyresampled($im_out, $im_in, 0, 0, 0, 0, $resized_dimensions[0], $resized_dimensions[1], $size[0], $size[1]);
  imagedestroy($im_in);
  save_image($im_out, $target, $quality);
  imagedestroy($im_out);
}

function save_image ($image_resource, $target_filename, $quality) {
  $extension = get_extension($target_filename);
  switch ($extension) {
    case ".jpg":
    case ".jpeg":
      imagejpeg($image_resource, $target_filename, $quality);
      break;
    case ".gif":
      imagegif($image_resource, $target_filename, $quality);
      break;
    case ".png":
      imagepng($image_resource, $target_filename, $quality);
  }
}

function get_extension ($filename) {
  $extension = strrchr($filename, ".");
  $extension = strtolower($extension);
  return $extension;
}

?>