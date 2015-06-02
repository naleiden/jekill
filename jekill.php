<?php

include("authentication.php");

$page_URL = $_REQUEST['page_URL'];
$return_URL = "{$_SERVER['DOCUMENT_ROOT']}/{$page_URL}";

if ($page_URL == "") {
	require_once("base/mysql_connection.php");
	require_once("base/schema_manager.php");

	$table = mysql_real_escape_string($_REQUEST['jtable']);
	$url_field = mysql_real_escape_string($_REQUEST['jfield']);
	$query_field = mysql_real_escape_string($_REQUEST['jquery']);
	$include_field = mysql_real_escape_string($_REQUEST['jinclude']);
	$id = mysql_real_escape_string($_REQUEST['jid']);

	$unique_identifier = SchemaManager::get_table_unique_identifier($table);

	$page_query = "SELECT page.page, page.name, page.content_include, parent.page AS parent_page, parent.content_include AS parent_content, language.abbreviation FROM page
				LEFT JOIN language ON (page.language = language.language_ID)
				LEFT JOIN page parent ON (parent.page_ID = page.parent)
				WHERE page.page_ID = '{$id}'";
	// echo $page_query;
	$page_results = $mysql->sql($page_query);

	if ($page_results->has_next()) {
		$page = $page_results->next();
		$page_URL = $page['page'];

		$content_page = $page['content_include'];

		if ($page['parent_page'] != "") {	// This is just alternate content for the parent page.
			$page_URL = $page['parent_page'];
			$content_page = "{$page['abbreviation']}/{$page['parent_content']}";
			$language_abbr = "{$page['abbreviation']}/";
		}
	}
	else {
		header("Location: control_panel.php?func=form&table={$table}&id={$id}");
		exit;
	}
	$return_URL = "control_panel.php?func=form&table={$table}&id={$id}";
}


$JEKILL_STYLE = "<link rel=\"stylesheet\" href=\"{$SETTINGS['JEKILL_ROOT']}/schema/css/jekill.css\" />";
$JEKILL_SCRIPT = "<script type=\"text/javascript\" src=\"{$SETTINGS['JEKILL_ROOT']}/schema/js/jquery.js\"></script>
<script type=\"text/javascript\" src=\"{$SETTINGS['JEKILL_ROOT']}/schema/js/tiny_mce/tiny_mce.js\"></script>
<script type=\"text/javascript\" src=\"{$SETTINGS['JEKILL_ROOT']}/schema/js/HttpRequest.js\"></script>
<script type=\"text/javascript\" src=\"{$SETTINGS['JEKILL_ROOT']}/schema/js/MouseAdapter.js\"></script>
<script type=\"text/javascript\" src=\"{$SETTINGS['JEKILL_ROOT']}/schema/js/utils.js\"></script>
<script type=\"text/javascript\" src=\"{$SETTINGS['JEKILL_ROOT']}/schema/js/windowing.js\"></script>
<script type=\"text/javascript\" src=\"{$SETTINGS['JEKILL_ROOT']}/schema/js/jekill.js\"></script>
<script type=\"text/javascript\" src=\"{$SETTINGS['JEKILL_ROOT']}/kernel/js/kernel.js\"></script>

<!-- 
<script type=\"text/javascript\" src=\"{$SETTINGS['JEKILL_ROOT']}/schema/js/spec.js\"></script>
<script type=\"text/javascript\" src=\"{$SETTINGS['JEKILL_ROOT']}/schema/js/SpecElement.js\"></script>
-->
<script type=\"text/javascript\">
initJekill(\"{$page['name']}\", \"{$page['abbreviation']}\", \"{$return_URL}\");
</script>";

include("{$_SERVER['DOCUMENT_ROOT']}/{$page_URL}");

?>