<?php

require_once("base/define.php");

if (isset($_POST['session_ID']))
	session_id($_POST['session_ID']);

include("404_authentication.php");

require_once("base/database_schema.php");
require_once("base/mysql_connection.php");
require_once("base/schema_manager.php");

$table_name = $_REQUEST['table_name'];
$field_name = $_REQUEST['field_name'];
$entity_ID = $_REQUEST['entity_ID'];

// TODO: Use SchemaManager::save_uploaded_file() here. (Needs to handle multiple files with the same filename - maybe use original filename)
$original_filename = $_FILES['swf_upload']['name'];
$error = $_FILES['swf_upload']['error'];
$directory = $SCHEMA[$table_name][$field_name][ROOT_DIRECTORY];
if ($directory == "") {
	$directory = "{$_SERVER['DOCUMENT_ROOT']}/images/schema/{$table_name}/";
	if (!is_dir($directory))
		mkdir($directory);
}
else {
	$table_unique_identifier = SchemaManager::get_table_unique_identifier($table_name);
	$record = $mysql->get($table_name, "WHERE {$table_unique_identifier} = '{$entity_ID}'");
	$directory = SchemaManager::replace_field_value($SCHEMA[$table_name][$field_name][ROOT_DIRECTORY], $record);
	if ($directory[0] != "/")
		$directory = "/" . $directory;
	$directory = "{$_SERVER['DOCUMENT_ROOT']}{$directory}";
}

$filename = "{$directory}{$original_filename}";
move_uploaded_file($_FILES['swf_upload']['tmp_name'], $filename);

SchemaManager::create_thumbnails($table_name, $field_name, $filename);

?>