<?php

require_once("../base/HTML.php");

$image_URL = $_REQUEST['image_URL'];
$output_image = $_REQUEST['output_image'];
$max_width = $_REQUEST['max_width'];
$width = $_REQUEST['width'];
$height = $_REQUEST['height'];
$callback = stripslashes($_REQUEST['callback']);

$html = new HTML();

$iframe = $html->iframe()->frameborder(0)->style("border-width: 0px; height: 520px; padding: 0px; margin: 0px; width: 970px;");
$iframe->src("schema/image_manipulator.php?max_width=$max_width&image_URL={$image_URL}&output_image={$output_image}&width={$width}&height={$height}&callback={$callback}")->class("embedded_image_manipulator");

// &return_URL=view_image.php?image_URL={$image_URL}

$html->add($iframe);

echo $iframe->html();

?>