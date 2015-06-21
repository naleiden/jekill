<?php

require_once("base/schema_manager.php");

$request_type = "ajax";
include("authentication.php");

$parent_table = filter("/[a-z0-9\-]+/", $_POST['parent']);
$field_name = filter("/[a-z0-9\-]+/", $_POST['field']);
$parent_ID = intval($_POST['parent_ID']);
$child_ID = intval($_POST['child_ID']);
$order = intval($_POST['order']);

header("Content-Type: application/json");

switch ($action) {
	case "data":
		$schema = urldecode($_GET['schema']);
		echo SchemaManager::data($schema);
		break;
	case "create":
		SchemaManager::create_relationship($parent_table, $parent_ID, $field_name, $child_ID);
		break;
	case "destroy":
		SchemaManager::destroy_relationship($parent_table, $parent_ID, $field_name, $child_ID);
		break;
	case "reorder":
		SchemaManager::reorder($parent_table, $parent_ID, $field_name, $child_ID, $order);
		break;
}

?>
