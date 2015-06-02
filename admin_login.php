<?php

include("base/ensure_secure.php");

require_once("base/define.php");
require_once("base/settings.php");
require_once("base/HTML.php");
require_once("base/schema_manager.php");

$html = new HTML("{$SETTINGS['COMPANY_NAME']}: Administrative Login", "schema/css/control_panel.php");

$html->body->class("login");

$login_form = SchemaManager::login_form("username", "control_panel.php");

$html->add($login_form);

$html->import("schema/js/jquery.js");
$html->import("schema/js/jquery.corner.js");
$html->import("schema/js/md5/md5-min.js");
$html->import("schema/js/schema.js");
$html->import("schema/js/user.js");
$html->import("schema/js/utils.js");
$html->import("schema/js/windowing.js");

$round_script = $html->script()->content("$(\".login_frame\").corner(\"30px\");");

$html->script->add($round_script);

echo $html->html();

?>