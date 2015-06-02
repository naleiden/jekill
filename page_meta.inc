<?php

/* MySQL Connection & Settings must be included prior to including this script. */

$script_name = substr($_SERVER['PHP_SELF'], 1);
$page_meta_data = $mysql_connection->get("page", "WHERE page = '{$script_name}'");
if ($page_meta_data['page_ID'] != "") {
	if (!isset($page_title) && $page_meta_data['title'] != "")
		$page_title = $page_meta_data['title'];

	if (!isset($meta_description) && $page_meta_data['meta_description'] != "")
		$meta_description = $page_meta_data['meta_description'];

	if (!isset($meta_keywords) && $page_meta_data['meta_keywords'] != "")
		$meta_keywords = $page_meta_data['meta_keywords'];
}

if ($page_title == "")
	$page_title = $SETTINGS['DEFAULT_PAGE_TITLE'] . $SETTINGS['PAGE_TITLE_SUFFIX'];

if ($meta_description == "")
	$meta_description = $SETTINGS['DETAULT_META_DESCRIPTION'];

if ($meta_keywords == "")
	$meta_keywords = $SETTINGS['DEFAULT_META_KEYWORDS'];


?>
