<?php

// TODO: Rewrite relative paths with $_SERVER['DOCUMENT_ROOT']

session_start();

require_once("../base/define.php");
require_once("../base/schema_manager.php");
require_once("../base/settings.php");

$table = $_POST['table'];

if (isset($_SESSION["{$table}_serialization"])) {	// isset($_POST["{$table}_serialization"])) {
	$table_schema = SchemaManager::unserialize_table($table, $_SESSION["{$table}_serialization"]);
	$SCHEMA[$table] = $table_schema;
}

/* If controling page specifies a pre-processor, include it. */
if (isset($_POST['process_page']))
	include("../" . $_POST['process_page']);

if ($table != "") {
	if ($SCHEMA[$table][TABLE_PREPROCESSOR] != "")
		include($_SERVER['DOCUMENT_ROOT'] . "/" . $SCHEMA[$table][TABLE_PREPROCESSOR]);

	try {
		$entity_ID = SchemaManager::persist($_POST);
	}
	catch (Exception $e) {
		if ($_POST['_submission_method'] == "ajax") {
			header("Content-Type: text/xml");
			echo "<" . "?xml version=\"1.0\" ?><reponse><Error>The data submitted could not be authenticated.</Error></reponse>";
			exit;
		}
		else {

		}
	}

	if ($SCHEMA[$table][TABLE_POSTPROCESSOR] != "")
		include($_SERVER['DOCUMENT_ROOT'] . "/" . $SCHEMA[$table][TABLE_POSTPROCESSOR]);

	if ($_POST['table_postprocessor'] != "")
		include($_SERVER['DOCUMENT_ROOT'] . "/" . $_POST['table_postprocessor']);

}

if ($_POST['_submission_method'] == "ajax") {
	header("Content-Type: text/xml");
	echo "<" . "?xml version=\"1.0\" ?>
<response>
<EntityID>{$entity_ID}</EntityID>";
	if ($SETTINGS['ENVIRONMENT'] == TEST_DEBUG)
		echo "<Error>" . $mysql->get_error() . "</Error>";
	echo "</response>";
}
else {
	$location = $_POST['source_page'];
	if ($_POST['source_page'] == "")
		$location = "../control_panel.php?func=form&table={$table}&id={$entity_ID}";

	if (isset($_POST['forward_page'])) {
		$location = $_POST['forward_page'];
		if ($location[0] != "/")
			$location = "../" . $location;
	}

	// echo "'{$location}'";

	header("Location: {$location}");
	exit;
}


?>