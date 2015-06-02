<?php

$CODE_LENGTH = 7;

function verify ($verification, $session_ID) {
  global $CODE_LENGTH;

  if ($verification == substr($session_ID, 0, $CODE_LENGTH))
    return 1;
  else return 0;
}

function get_verification_filename ($session_code) {
  $encrypted = crypt($session_code, 1);
  $filename = str_replace("\$", "", $encrypted);
  $filename = str_replace("/", "", $filename);
  $filename = str_replace(".", "", $filename);
  $filename = substr($filename, 0, 10);
  return "verification/" . $filename . ".jpg";
}

function get_verification_image ($session_code) {
  global $CODE_LENGTH;

  $IMAGE_WIDTH = 150;
  $IMAGE_HEIGHT = 50;

  $colors = array(0x0000FF, 0x00CC00, 0x990000, 0xFF6600, 0xCCCC00, 0xFF9999, 0xFF00FF, 0x66CCFF, 0x339999);
  $output_image = get_verification_filename($session_code);
  $image = imagecreatetruecolor($IMAGE_WIDTH, $IMAGE_HEIGHT);
  $background_color = $colors[rand(0, count($colors))];
  do {
    $foreground_color = $colors[rand(0, count($colors))];
    $foreground_color = 0x000000;
  } 
  while ($foreground_color == $background_color);

  imagefilledrectangle($image, 0, 0, $IMAGE_WIDTH, $IMAGE_HEIGHT, $background_color);

  $RECT_SPACING = 7;
  for ($i=0; $i<$IMAGE_WIDTH; $i+=$RECT_SPACING) {
    if (rand(0, 10) > 5) {
      $radius = rand(5, $IMAGE_HEIGHT);
      $x = rand(0, $IMAGE_WIDTH);
      $y = rand(0, $IMAGE_HEIGHT);
      imagearc($image, $x, $y, $radius, $radius, 0, 360, $foreground_color);
    }
    else imageline($image, $i, 0, $i, $IMAGE_HEIGHT, $foreground_color);
  }
  for ($i=0; $i<$IMAGE_HEIGHT; $i+=$RECT_SPACING) {
    imageline($image, 0, $i, $IMAGE_WIDTH, $i, $foreground_color);
  }

/*
  $arc_center_x = rand(0, $IMAGE_WIDTH);
  $arc_stop = (($IMAGE_WIDTH-$arc_center_x) > $arc_center_x) ? $IMAGE_WIDTH-$arc_center_x : $arc_center_x;
  $ARC_SPACING = 5;
  for ($i=0; $i<$arc_stop; $i+=$ARC_SPACING)
    imagearc($image, $arc_center_x, $IMAGE_HEIGHT, $arc_stop +$i, $IMAGE_HEIGHT +$i, 0, 360, $foreground_color);
*/

  $font_size = 25;
  if (rand(0, 10) > 5) {
    $X_OFFSET = 15;
    $Y_OFFSET = 30;
  }
  else {
    $X_OFFSET = 10;
    $Y_OFFSET = 35;
  }
  $session_substr = substr($session_code, 0, $CODE_LENGTH);
  // $font = "times.ttf";
  $font = "arial.ttf";
  imagettftext($image, $font_size, 0, $X_OFFSET, $Y_OFFSET, $foreground_color, $font, $session_substr);
  imageJPEG($image, $output_image, 100);
  imagedestroy($image);
  return $output_image;
}

function destroy_verification_image ($session_code) {
  $filename = get_verification_filename($session_code);
  unlink($filename);
}

?>