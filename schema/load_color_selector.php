<?php

include("color_selector_helper.inc");
require_once("../base/HTML.php");

$color = $_REQUEST['color'];
$action = $_REQUEST['action'];
$act_on = $_REQUEST['act_on'];

$html = new HTML();

$palette = get_color_palette($color, $action, $act_on);

echo $palette->html();

?>