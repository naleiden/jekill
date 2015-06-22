<?php

require_once("../base/curl.php");
require_once("../base/mysql_connection.php");
require_once("../base/schema_manager.php");

define("HOST", "http://127.0.0.1");

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

// Direct tests:

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

/*************************/
/*  CURL test endpoints  */
/*************************/

// TODO: Make host more configurable
define("HOST", "http://127.0.0.1");

// GET request should be rejected.
$get = new Curl(HOST . "/subject/249/lessons/918/create");
$response = $get->execute();
$response_code = $get->get_response_code();
test($response_code, 400, "Created HTTP status with invalid GET request not as expected");

// echo $response;

// N-to-N
$post = new CurlPost(HOST . "/subject/249/lessons/918/create");
$response = $post->execute();
$response = json_decode($response, true);
$response_code = $post->get_response_code();
test($response_code, 201, "Created HTTP status for N-to-N not as expected");
test($response['status'], "created", "JSON status not as expected after creation");

$post = new CurlPost(HOST . "/subject/249/lessons/918/destroy");
$response = $post->execute();
$response = json_decode($response, true);
$response_code = $post->get_response_code();
test($response_code, 200, "Destroy HTTP status for N-to-N not as expected");
test($response['status'], "removed", "JSON status not as expected after removal");

// Repeat destroy (it should fail this time)
$post = new CurlPost(HOST . "/subject/249/lessons/918/destroy");
$response = $post->execute();
$response = json_decode($response, true);
$response_code = $post->get_response_code();
test($response_code, 304, "Destroy HTTP status for non-existing N-to-N not as expected");
// No response returned with 304

//$response = json_decode($response, true);//
//$post = new CurlPost(HOST . "/subject/249/lessons/918/reorder", array("order" => 2));
//$response = $post->execute();
//test($response['status'], "created", "");

// 1-to-N
$post = new CurlPost(HOST . "/subject_group/73/subjects/249/create");
$response = $post->execute();
$response = json_decode($response, true);
$response_code = $post->get_response_code();
test($response_code, 201, "Created HTTP status for 1-to-N not as expected");
test($response['status'], "created", "JSON status not as expected after creation");

$post = new CurlPost(HOST . "/subject_group/73/subjects/249/destroy");
$response = $post->execute();
$response = json_decode($response, true);
$response_code = $post->get_response_code();
test($response_code, 200, "Destroy HTTP status for 1-to-N not as expected");
test($response['status'], "removed", "JSON status not as expected after removal");

// Repeat destroy (it should fail this time)
$post = new CurlPost(HOST . "/subject_group/73/subjects/249/destroy");
$response = $post->execute();
echo $response;
$response = json_decode($response, true);
$response_code = $post->get_response_code();
test($response_code, 304, "Destroy HTTP status for non-existing 1-to-N not as expected");
// No response returned with 304

//$post = new CurlPost(HOST . "/subject_group/73/subjects/249/reorder", array("order" => 2));
//$response = $post->execute();
//echo $response;

/* // Endpoint test for data.
$get = new Curl(HOST . "/subject/249/lessons/918/data?schema=" . urlencode($schema));
$response = $get->execute();
echo $response;
exit;
*/

?>