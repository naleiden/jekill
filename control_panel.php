<?php

include("base/ensure_secure.php");
include("authentication.php");

require_once("base/define.php");
require_once("base/HTML.php");
require_once("base/mysql_connection.php");
require_once("base/schema_manager.php");
require_once("base/settings.php");

$table = $_REQUEST['table'];
$entity_ID = $_REQUEST['id'];
$function = isset($_REQUEST['func']) ? $_REQUEST['func'] : "browse";

$style = array(/* "schema/css/control_panel_style.php", */ "schema/js/jquery-ui/css/ui-lightness/jquery-ui-1.8.13.custom.css", "schema/css/color_selector.css", "schema/css/control_panel.php", "schema/css/calendar.css", "schema/css/form.css.php", "schema/css/util.css", "kernel/css/kernel_style.css");
$html = new HTML("{$SETTINGS['COMPANY_NAME']}: Control Panel", $style);

// $html->import("swf/SWFUpload/swfupload.js");
$html->import("node_modules/jquery/dist/jquery.min.js");
$html->import("schema/js/color.js");
$html->import("schema/js/form.php");
$html->import("schema/js/schema.php");
$html->import("schema/js/utils.js");
$html->import("schema/js/md5/md5.js");


/* For Kernel Use. */
$html->import("schema/js/draggable.js");
$html->import("schema/js/MouseAdapter.js");
$html->import("schema/js/HttpRequest.js");
$html->import("schema/js/windowing.js");

$html->import("kernel/js/kernel.js");

if ($entity_ID == "")
	$forward_page = "control_panel.php?func=browse&table={$table}";

$control_panel_div = SchemaManager::control_panel($table, $function, $entity_ID, $forward_page);

$frame_div = $html->div()->add($control_panel_div);

$html->add($frame_div);

echo $html->html();

?>
