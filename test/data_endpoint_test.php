<?php

require_once("../base/mysql_connection.php");
require_once("../base/schema_manager.php");

$schema = "subject_group,subjects,lessons{visible:1}";
// $data = SchemaManager::data($schema);

//echo "<pre>";
//echo json_encode($data, JSON_PRETTY_PRINT);
//echo "</pre>";

function test ($actual_value, $expected_value, $error_message) {
	if ($actual_value != $expected_value) {
		echo "<div style='color: #CC0000;'>{$error_message} Expected: '{$expected_value}', found '{$actual_value}'</div>";
	} else {
		echo "<span style='color: #00CC00;'>.</span>";
	}
}


$subject_ID = 73;
$unit_ID = 249;
$tutorial_ID = 918;

/************/
/*  N-to-N  */
/************/

// Create a N-to-N relationship
SchemaManager::create_relationship("subject", $unit_ID, "lessons", $tutorial_ID);
// Check for the 1-to-N relationship
$row = $mysql->prepare("SELECT COUNT(*) AS map_exists FROM subject_lesson WHERE subject = {$unit_ID} AND lesson = {$tutorial_ID}")
			->fetch_row();
test($row['map_exists'], 1, "Expected junction row to be created, but it was not.");

// Destroy the N-to-N relationship
SchemaManager::destroy_relationship("subject", $unit_ID, "lessons", $tutorial_ID);
// Ensure the N-to-N relationship is destroyed
$row = $mysql->prepare("SELECT COUNT(*) AS map_exists FROM subject_lesson WHERE subject = {$unit_ID} AND lesson = {$tutorial_ID}")
			->fetch_row();
test($row['map_exists'], 0, "Expected junction row to be deleted, but it was not.");


/************/
/*  1-to-N  */
/************/

// Create an 1-to-N relationship
SchemaManager::create_relationship("subject_group", $subject_ID, "subjects", $unit_ID);
// Check for the 1-to-N relationship
$subject_group = $mysql->get_field("subject", "subject_group", "WHERE subject_ID = {$unit_ID}");
test($subject_group, $subject_ID, "Subject group was not set as expected.");

// Destroy the 1-to-N relationship
SchemaManager::destroy_relationship("subject_group", $subject_ID, "subjects", $unit_ID);
// Ensure the 1-to-N relationship is destroyed
$subject_group = $mysql->get_field("subject", "subject_group", "WHERE subject_ID = {$unit_ID}");
test($subject_group, 0, "Subject group was not un-set as expected.");

/*************/
/*  Cleanup  */
/*************/

// Explicitly clean up for next time...
$mysql->query("UPDATE subject SET subject_group = NULL WHERE subject_ID = {$unit_ID}");
$mysql->query("DELETE FROM subject_lesson WHERE subject = {$unit_ID} AND lesson = {$tutorial_ID}");

?>