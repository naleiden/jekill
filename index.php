<?php

require_once("base/schema_manager.php");

$request_type = "ajax";
// include("authentication.php");

$parent_table = filter("/[a-z0-9\-_]+/", $_GET['parent']);
$field_name = filter("/[a-z0-9\-_]+/", $_GET['field']);
$parent_ID = intval($_GET['parent_ID']);
$child_ID = intval($_GET['child_ID']);
$action = filter("/[a-z\-_]+/i", $_GET['action']);
$order = intval($_POST['order']);

header("Content-Type: application/json");

$response = array();
if ($action == "data") {
	$schema = urldecode($_GET['schema']);
	$response = SchemaManager::data($schema);
} else {
	// Only accept POSTs
	if (strcasecmp($_SERVER['REQUEST_METHOD'], "POST") !== 0) {
		header('HTTP/1.1 400 Bad Request', true, 400);
		exit;
	}

	if (!$parent_table || !$field_name || !$parent_ID || !$child_ID || !$action) {
		header('HTTP/1.1 400 Bad Request', true, 400);
	} else {
		switch ($action) {
			case "create":
				$created = SchemaManager::create_relationship($parent_table, $parent_ID, $field_name, $child_ID);
				if ($created) {
					header('HTTP/1.1 201 Created', true, 201);
					$response['status'] = "created";
				} else {
					$response['status'] = "failed";
				}
				break;
			case "destroy":
				$destroyed = SchemaManager::destroy_relationship($parent_table, $parent_ID, $field_name, $child_ID);
				if (!$destroyed) {
					header('HTTP/1.1 304 Not Modified', true, 304);
				} else {
					$response['status'] = "removed";
				}
				break;
			case "reorder":
				if (!$order) {
					header('HTTP/1.1 400 Bad Request', true, 400);
				} else {
					SchemaManager::reorder($parent_table, $parent_ID, $field_name, $child_ID, $order);
				}
				break;
			default:
		}
	}
}
echo json_encode($response);

?>
