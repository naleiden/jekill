<?php

require_once("schema_manager.inc");

include("database_schema.php");
include("define.php");
include("settings.php");

require_once("HTML.php");
require_once("mysql_connection.php");
require_once("util.php");

class SchemaManager {

	function browse_files () {
		$kernel = new Kernel();
		$kernel_div = $kernel->get_kernel_divide(true);
		session_start();
		$_SESSION['kernel'] = serialize($kernel);

		return $kernel_div;
	}

	protected function get_map_table_details ($parent_table_name, $field_name) {
		global $SCHEMA;

		$child_table_name = $SCHEMA[$parent_table_name][$field_name][LINK_TABLE];
		$map_table = self::get_map_table_name($parent_table_name, $child_table_name, $field_name);
		$field = $SCHEMA[$parent_table_name][$field_name];
        $parent_ID_field = (isset($field[LINK_LOCAL_KEY])) ? $field[LINK_LOCAL_KEY] : self::get_table_unique_identifier($parent_table_name);
        $child_ID_field = (isset($field[LINK_FOREIGN_KEY])) ? $field[LINK_FOREIGN_KEY] : self::get_table_unique_identifier($child_table_name);
		return array($map_table, $parent_ID_field, $child_ID_field);
	}

	static function create_relationship ($parent_table_name, $parent_ID, $field_name, $child_ID) {
		global $SCHEMA, $mysql;

		$TABLE = $SCHEMA[$parent_table_name];
		$field = $TABLE[$field_name];
		$child_table_name = $field[LINK_TABLE];

		switch ($field[FIELD_TYPE]) {
			// TODO: Factor this with similar code in persist()
			case LINK_N_TO_N:
				list($map_table, $parent_ID_field, $child_ID_field) = self::get_map_table_details($parent_table_name, $child_table_name);
				// TODO: Maybe make this not have to be unique later, or use keys to ensure uniqueness.
				$mysql->write_lock($map_table);
				$existing = $mysql->count($map_table, "*", sprintf("WHERE {$parent_ID_field} = %d AND {$child_ID_field} = %d", $parent_ID, $child_ID));
				$query = sprintf("INSERT INTO {$map_table} ({$parent_ID_field}, {$child_ID_field}) VALUES (%d, %d)", $parent_ID, $child_ID);
				$mysql->query($query);
				$mysql->unlock();
				break;
			case LINK_ONE_TO_N:
				// The field in the child table that references the parent
				$reference_field = $field[LINK_FIELD];
				$child_table_ID_field = self::get_table_unique_identifier($child_table);
				$query = sprintf("UPDATE {$child_table_name} SET {$reference_field} = %d
							WHERE {$child_table_ID_field} = %d",
							$parent_ID,
							$child_ID
						);
				break;
		}
	}

	static function destroy_relationship ($parent_table, $parent_ID, $field_name, $child_ID) {
		global $SCHEMA;

		$TABLE = $SCHEMA[$parent_table];
		$field = $TABLE[$field_name];

		switch ($field[FIELD_TYPE]) {
                        // TODO: Factor this with similar code in persist()
                        case LINK_N_TO_N:
                                list($map_table, $parent_ID_field, $child_ID_field) = self::get_map_table_details($parent_table_name, $child_table_name);
                                // TODO: Maybe make this not have to be unique later, or use keys to ensure uniqueness.
                                $mysql->write_lock($map_table);
                                $query = sprintf("DELETE FROM {$map_table} WHERE {$parent_ID_field} = %d AND {$child_ID_field} = %d", $parent_ID, $child_ID);
                                $mysql->query($query);
                                $mysql->unlock();
                                break;
                        case LINK_ONE_TO_N:
                                // The field in the child table that references the parent
                                $reference_field = $field[LINK_FIELD];
                                $child_table_ID_field = self::get_table_unique_identifier($child_table);
                                $query = sprintf("UPDATE {$child_table_name} SET {$reference_field} TO NULL
                                                        WHERE {$child_table_ID_field} = %d AND {$reference_field} = %d",
                                                        $child_ID,
							$parent_ID
                                                );
                                break;
                }

	}

	static function parse_data_schema ($schema) {
		$schema_parts = explode(",", $schema);
		$schema = array();
		foreach ($schema_parts AS $part) {
			if (preg_match("/:/", $part)) {
				preg_match("/(?P<field>[a-z_]+){(?P<where_field>[a-z_]+):(?P<where_value>[a-z0-9_]+)}/i", $part, $matches);
				$field = array(
						"field" => $matches['field'],
						"where_field" => $matches['where_field'],
						"where_value" => $matches['where_value']
					);
			} else {
				$field = array("field" => $part);
			}
			$schema[] = $field;
		}

		return $schema;
	}

	// Follow a heirarchy of a schema via references
	// [ table_name, field_name, field_name, ... ]
	static function data ($schema) {
		global $SCHEMA, $mysql;
		
		$schema = self::parse_data_schema($schema);
		$root_table = $schema[0]['field'];
		$heirarchy_IDs = array();
		$fields = array();
		$joins = array();
		$where = array();
		$orders = array();
		$current_table = $root_table;
		$current_table_alias = $current_table;

		// Add the fields of the root table
		$root_table_ID_field = self::get_table_unique_identifier($root_table);
		$root_table_label = $SCHEMA[$root_table][RECORD_LABEL];
		$fields[] = "{$root_table}.{$root_table_ID_field}";
		$fields[] = "{$root_table}.{$root_table_label}";
		if ($SCHEMA[$root_table][TABLE_SORT]) {
			$orders[] = "{$root_table}.{$SCHEMA[$root_table][TABLE_SORT]}";
		}
		if ($schema[0]['where_field']) {
			$where[] = "{$schema[0]['where_field']} = '{$schema[0]['where_value']}'";
		}
		$heirarchy_IDs[] = array("table" => $root_table, "id" => $root_table_ID_field, "id_alias" => $root_table_ID_field, "label" => $root_table_label, "label_alias" => $root_table_label);
		for ($i=1; $i<count($schema); $i++) {
			$current_table_ID_field = self::get_table_unique_identifier($current_table);
			$field_name = $schema[$i]['field'];
			$field = $SCHEMA[$current_table][$field_name];
			$child_table = $field[LINK_TABLE];

			// Create an alias for the child table, in case the table is referenced more than once
			$child_table_alias = $child_table . "_" . $field_name;
			$child_table_ID_field = self::get_table_unique_identifier($child_table);
			$child_label_field = $field[LINK_LABEL];

			if ($schema[$i]['where_field']) {
				$where[] = "{$child_table_alias}.{$schema[$i]['where_field']} = '{$schema[$i]['where_value']}'";
			}

			switch ($field[FIELD_TYPE]) {
				case LINK_N_TO_N:
					list($map_table, $parent_ID_field, $child_ID_field) = self::get_map_table_details($current_table, $field_name);
					$joins[] = "LEFT JOIN {$map_table} ON ({$map_table}.{$parent_ID_field} = {$current_table_alias}.{$current_table_ID_field})";
					$joins[] = "LEFT JOIN {$child_table} AS {$child_table_alias} ON ({$child_table_alias}.{$child_table_ID_field} = {$map_table}.{$child_ID_field})";
					if (!$field[LINK_MAP_TABLE]) {
						$orders[] = "{$map_table}.record_num";
					} else if ($field[LINK_MAP_TABLE] && $field[LINK_MAP_SORT]) {
						$orders[] = "{$map_table}." . $field[LINK_MAP_SORT];
					}
					break;
				case LINK_ONE_TO_N:
					$reference_field = $field[LINK_FIELD];
					$order_field = $field[LINK_SORT];
					$joins[] = "LEFT JOIN {$child_table} AS {$child_table_alias} ON ({$child_table_alias}.{$reference_field} = {$current_table}.{$current_table_ID_field})";
					if ($order_field) {
						$orders[] = "{$child_table_alias}.{$order_field}";
					}
					break;
			}
			$child_ID_alias = $child_table_ID_field . "_" . $field_name;
			$child_label_alias = $child_label_field . "_" . $field_name;
			$heirarchy_IDs[] = array("table" => $child_table, "field" => $field_name, "id" => $child_table_ID_field, "id_alias" => $child_ID_alias, "label" => $child_label_field, "label_alias" => $child_label_alias);
			$fields[] = "{$child_table_alias}.{$child_table_ID_field} AS {$child_ID_alias}"; 
			$fields[] = "{$child_table_alias}.{$child_label_field} AS {$child_label_alias}";
			$current_table = $child_table;
			$current_table_alias = $child_table_alias;
		}

		$fields = implode(", ", $fields);
		$joins = implode("\n", $joins);
		$where = (count($where)) ? "WHERE " . implode(" AND ", $where) : "";
		$orders = (count($orders)) ? "ORDER BY " . implode(", ", $orders) : "";

		$query = "SELECT {$fields}
					FROM {$root_table}
					{$joins}
					{$where}
					{$orders}";
		echo str_replace("\n", "<br/>", "<p>" . $query . "</p>");
		$results = $mysql->sql($query);

		$compiled_data = self::compile_data($heirarchy_IDs, $results);
		return $compiled_data;
	}

	static function compile_data (array $schema, SQLResult $results) {
		$results = $results->flatten();
		
		$data = self::group_results($results, $schema);

		return $data;
	}

	static function group_results (array $results, array $schema) {
		global $SCHEMA;

		// Pop the heirarchy table off of the schema 'stack'
		$table = array_shift($schema);
		$table['data'] = array();
		$id_field = $table['id_alias'];
		$label_field = $table['label_alias'];
		unset($table['id_alias']);
		unset($table['label_alias']);

		$group = null;
		$previous_value = null;
		$data_index = -1;
		foreach ($results AS $result) {
			if (!$group || $result[$id_field] != $previous_value) {

				if ($group && count($schema)) {
					// NOTE: If to support multiple children, just append results from reflecsive calls to array two lines down
					$children = self::group_results($group, $schema);
					$table['data'][$data_index]['children'] = array($children);
				}
				$data_index++;

				$group = array();
				$previous_value = $result[$id_field];
				$table['data'][$data_index] = array("id" => $result[$id_field], "label" => $result[$label_field]);
			}

			$group[] = $result;
		}

		if ($group && count($schema)) {
			// NOTE: If to support multiple children, just append results from reflecsive calls to array two lines down
			$children = self::group_results($group, $schema);
			$table['data'][$data_index]['children'] = array($children);
		}

		return $table;
	}

	function get_complex_link_label_comparator ($link_table, $link_label, $prefix="") {
		$link_label_options = array();
		foreach ($link_label AS $link_attachment_value => $link_label_attached) {
			$link_label_options[] = SchemaManager::get_link_label_comparator($link_table[$link_attachment_value], $link_label_attached, $prefix);
		}
		return "COALESCE(" . implode(", ", $link_label_options) . ")";
	}

	/* NOTE: $link_table may be a table alias, so it cannot be used as an index into $SCHEMA. */
	function get_link_label_comparator ($link_table, $link_label, $prefix="") {
		if (is_array($link_label)) {
			return SchemaManager::get_complex_link_label_comparator($link_table, $link_label, $prefix);
		}

		/* Split the label. */
		$delimiter = "/([^a-zA-Z0-9_]+)/";
		if (preg_match($delimiter, $link_label)) {
			$label_parts = preg_split($delimiter, $link_label, 0, PREG_SPLIT_DELIM_CAPTURE);
			$search_in = "";
			foreach ($label_parts AS $label_part) {
				if ($search_in != "")
					$search_in .= ", ";

				if (preg_match($delimiter, $label_part))
					$search_in .= "'{$label_part}'";
				else $search_in .= "{$prefix}{$link_table}.{$label_part}";
			}
			$search_in = "CONCAT(" . $search_in . ")";
		}
		else $search_in = "{$prefix}{$link_table}.{$link_label}";

		return $search_in;
	}

	function get_link_subquery ($page_table, $field, &$joined) {
		switch ($field[FIELD_TYPE]) {
			case LINK:
				$field_name = $field[FIELD_NAME];
				$link_attachment = $field[LINK_ATTACHMENT];
				$link_table = $field[LINK_TABLE];
				$link_label = $field[LINK_LABEL];
				if (!$link_attachment) {
					/* True: Multiple fields could join to the same table, but be different references. */
					if (true || !array_key_exists($link_table, $joined)) {
						$joined[] = $link_table;
						$link_table_alias = "{$field_name}_{$link_table}";
						$link_table_identifier = SchemaManager::get_table_unique_identifier($link_table);
						$link_label = SchemaManager::get_link_label_comparator($link_table_alias, $link_label);
						$query_fields .= ", {$link_label} AS {$field_name}_label";	// _{$link_label} // why was this (appending label) necessary? Will not work with complex labels, e.g., "first_name, last_name"
						$query_join .= "\nLEFT JOIN {$link_table} AS {$link_table_alias} ON ({$link_table_alias}.{$link_table_identifier} = {$page_table}.{$field_name}) ";
					}
				}
				else {
					$query_fields_attached = array();
					foreach ($link_table AS $link_table_attachment => $link_table_attached) {
						$joined[] = $link_table_attached;
						$link_table_identifier = SchemaManager::get_table_unique_identifier($link_table_attached);
						$link_table_alias = "{$field_name}_{$link_table_attached}";
						$link_label_attached = $link_label[$link_table_attached];
						$link_label_attached = SchemaManager::get_link_label_comparator($link_table_alias, $link_label_attached);
						$query_join .= "\nLEFT JOIN {$link_table_attached} AS {$link_table_alias} ON ({$link_table_alias}.{$link_table_identifier} = {$page_table}.{$field_name} AND {$page_table}.{$link_attachment} = '{$link_table_attachment}') ";
						$query_fields_attached[] = $link_label_attached;
					}
					$query_fields .= ", COALESCE(" . implode(", ", $query_fields_attached) . ") AS {$field_name}_label";
				}
				break;
			default: continue;
		}

		return array("fields" => $query_fields, "joins" => $query_join);
	}

	/**
	 * @param $deep_query: Join all associated fields.
	 */
	function construct_result_query ($page_table, $search_for="", $operator="", $search_in="", $num_results="", $sort_by="", $page="", $deep_query=false) {
		global $SCHEMA, $mysql_connection;

		$user_access = $_SESSION["{$LOGIN_ID}_permissions"];

		if ($page == "")
			$page = 1;

		$limit_start = (($page-1)*$num_results);
		$query_fields = "{$page_table}.*";
		if (isset($SCHEMA[$page_table][RECORD_BROWSE_FIELDS]))
			$query_fields = $SCHEMA[$page_table][RECORD_BROWSE_FIELDS];

		$joined = array();	// Keep track of what has been joined already.

		$query_where = $SCHEMA[$page_table][RECORD_WHERE];

		/* Join all associated fields as defined in $SCHEMA. */
		if ($deep_query) {
			foreach ($SCHEMA[$page_table] AS $field) {
				$link_subquery = SchemaManager::get_link_subquery($page_table, $field, $joined);

				// $joined = array_merge($link_subquery['joined_tables'], $joined);
				$query_fields .= $link_subquery['fields'];
				$query_join .= $link_subquery['joins'];
			}
		}

		if ($search_for && $search_in) {
			/* The field being searched in is in an associated table. */
			if ($SCHEMA[$page_table][$search_in][FIELD_TYPE] == LINK) {
				$search_field = $SCHEMA[$page_table][$search_in];
				$link_attachment = $search_field[LINK_ATTACHMENT];
				$link_table = $search_field[LINK_TABLE];
				$link_label = $search_field[LINK_LABEL];

				// if (!in_array($link_table, $joined)) {
					$link_subquery = SchemaManager::get_link_subquery($page_table, $SCHEMA[$page_table][$search_in], $joined);
					$query_join .= $link_subquery['joins'];
/*
					$joined[] = $link_table;
					$link_table_identifier = SchemaManager::get_table_unique_identifier($link_table);
					$query_join .= " LEFT JOIN {$link_table} ON ({$page_table}.{$search_in} = {$link_table}.{$link_table_identifier}) ";
*/
				// }

				$search_in = SchemaManager::get_link_label_comparator($link_table, $link_label, "{$search_in}_");
			}

			if ($operator == "LIKE")
				$search_for = "%{$search_for}%";

			if ($query_where != "")
				$query_where .= " AND ";
			else $query_where = " WHERE ";
			$query_where .= "{$search_in} {$operator} '{$search_for}'";

		}

		if ($user_access < $SCHEMA[$page_table][RECORD_ACCESS]) {
			if ($query_where != "")
				$query_where .= " AND ";
			else $query_where = "WHERE ";

			$user = $mysql_connection->get("user", "WHERE user_ID = '{$_SESSION[$LOGIN_ID]}'");
			$test_field = $SCHEMA[$page_table][RECORD_ACCESS_FIELD];	// Get the field specified on this table
			$user_field = $SCHEMA[$page_table][RECORD_ACCESS_USER_FIELD];	// Get the field specified on the 'user' table
			$user_value = $user[$user_field];				// Get the value of the user-field on the current user
			$query_where .= "{$test_field} = '{$user_value}'";
		}

		if ($sort_by == "" && isset($SCHEMA[$page_table][TABLE_SORT]))
			$sort_by = $SCHEMA[$page_table][TABLE_SORT];

		if ($sort_by != "") {
			$sort = $sort_by;
			if ($sort[0] == "-") {
				$sort = substr($sort_by, 1);
				$sort_desc = "DESC";
			}

			/* If the defined sort is by an adjacent table, sort according to the defined label for that table. */
			if ($SCHEMA[$page_table][$sort][FIELD_TYPE] == LINK) {
				$sort_field = $SCHEMA[$page_table][$sort];
				$link_table = $sort_field[LINK_TABLE];
				$link_label = $sort_field[LINK_LABEL];
				if (!in_array($link_table, $joined)) {
					$joined[] = $link_table;
					$link_table_identifier = SchemaManager::get_table_unique_identifier($link_table);
					$query_join .= " LEFT JOIN {$link_table} AS {$link_table}_sort ON ({$page_table}.{$sort} = {$link_table}_sort.{$link_table_identifier}) ";
				}
				$sort = SchemaManager::get_link_label_comparator("{$link_table}_sort", $link_label);
			}
		}

		if ($sort != "")
			$query_sort = " ORDER BY {$sort} {$sort_desc}";
		if ($num_results != "")
			$query_limit = " LIMIT {$limit_start}, {$num_results}";

		if (isset($SCHEMA[$page_table][RECORD_GROUP_BY]))
			$query_group_by = "GROUP BY " . $SCHEMA[$page_table][RECORD_GROUP_BY];

		$query = "SELECT SQL_CALC_FOUND_ROWS {$query_fields} FROM {$page_table} {$query_join} {$query_group_by} {$query_where} {$query_sort} {$query_limit}";

// echo $query; exit;

		return $query;
	}

	function browse ($page_table) {
		global $SCHEMA, $mysql_connection, $html, $DEFAULT_MAX_RESULTS;

		if (isset($SCHEMA[$page_table][TABLE_NAME]))
			$page_table = $SCHEMA[$page_table][TABLE_NAME];

		$max_results = ($SCHEMA[$page_table][TABLE_BROWSE_LIMIT]) ? $SCHEMA[$page_table][TABLE_BROWSE_LIMIT] : $DEFAULT_MAX_RESULTS;
		if (isset($SCHEMA[$page_table][TABLE_BROWSE]))
			return SchemaManager::custom_table_browse($page_table);

		$page = (isset($_REQUEST['page'])) ? mysql_real_escape_string($_REQUEST['page']) : 1;
		$query = SchemaManager::construct_result_query($page_table, mysql_real_escape_string($_REQUEST['search_for']),
									mysql_real_escape_string($_REQUEST['op']),
									mysql_real_escape_string($_REQUEST['search_in']),
									$max_results,
									mysql_real_escape_string($_REQUEST['sort']),
									$page);

		$browse_div = $html->div();
		$results = $mysql_connection->sql($query);
		if ($results->has_next()) {
			$pagination_parameters = "table={$page_table}&sort={$_REQUEST['sort']}&search_in={$_REQUEST['search_in']}&search_for={$_REQUEST['search_for']}&op={$_REQUEST['op']}&layout={$_REQUEST['layout']}";
			$result_linkbars = SchemaManager::result_linkbars($page, "", $pagination_parameters, $max_results);
			$browse_method = isset($_REQUEST['layout']) ? $_REQUEST['layout'] : $SCHEMA[$page_table][DEFAULT_BROWSE_LAYOUT];
			if ($browse_method == "")
				$browse_method = "row";

			$browse_header = "{$browse_method}_header";
			$browse_header = SchemaManager::$browse_header($page_table);
			$browse_div->add($result_linkbars[0])->add($result_linkbars[1])->add( $html->div()->class("clear") )->add($browse_header);
			$rows_div = $html->div()->class("rows");

			while ($results->has_next()) {
				$row = $results->next();
				$row_class = ($i++%2) ? "row_odd" : "row_even";

				$row_div = SchemaManager::$browse_method($page_table, $row, $row_class);
				$rows_div->add($row_div);
			}
			$browse_div->add($rows_div)->add( $html->div()->class("clear") )->add($result_linkbars[1])->add($result_linkbars[0]);
		}
		else {
			$no_records_div = $html->div()->class("center")->content("- There are currently no records in the '" . $SCHEMA[$page_table][TABLE_LABEL] . "' table -");
			$browse_div->add($no_records_div);
		}
		$rows_icon = $html->img()->src("schema/images/rows.gif")->title("Display Results in Rows");
		$gallery_icon = $html->img()->src("schema/images/gallery.gif")->title("Display Results in Gallery Form");
		$query_string = "table={$_REQUEST['table']}&sort={$_REQUEST['sort']}&search_in={$_REQUEST['search_in']}&search_for={$_REQUEST['search_for']}&page={$_REQUEST['page']}";
		$rows_link = $html->a()->href("?{$query_string}&layout=row")->add($rows_icon);
		$gallery_link = $html->a()->href("?{$query_string}&layout=gallery")->add($gallery_icon);
		$layout_div = $html->div()->id("layout_control")->add($rows_link)->add($gallery_link);
		$browse_div->add($layout_div);

		return $browse_div;
	}

	function gallery_header ($page_table) {
		global $html, $SCHEMA;

		$header_div = $html->div();
		return $header_div;
	}

	/* Gallery browse function for browsing w/images and titles. */
	function gallery ($page_table, $data, $result_class="") {
		global $html, $SCHEMA, $SETTINGS;

		static $image_field;
		static $title_field;
		static $unique_identifier;

		$unique_identifier = SchemaManager::get_table_unique_identifier($page_table);

		$color = (isset($SETTINGS['CONTROL_PANEL_COLOR'])) ? $SETTINGS['CONTROL_PANEL_COLOR'] : "9999AA";
		// Look for an IMAGE, or a 'title' or 'name' field
		if ($image_field == "") {
			foreach ($SCHEMA[$page_table] AS $field_name => $field) {
				if (strtolower($field_name) == "title")
					$title_field = $field_name;
				else if (strpos(strtolower($field_name), "title") > 0 && $title_field == "")
					$title_field = $field_name;
				else if (strpos(strtolower($field_name), "name") > 0 && $title_field == "")
					$title_field = $field_name;

				if ($field[FIELD_TYPE] == IMAGE && $image_field == "")
					$image_field = $field_name;

				if ($image_field != "" && $title_field != "")
					break;
			}
			if ($image_field == "")
				$image_field = "NO_IMAGE_FOUND";
			if ($title_field == "")
				$title_field = "NO_TITLE_FOUND";
		}

		$image_URL = $data[$image_field];
		if ($image_URL == "")
			$image_URL = "{$SETTINGS['JEKILL_ROOT']}/schema/images/no_image_available.jpg";
		$image_URL = "{$SETTINGS['JEKILL_ROOT']}/image_excerpt.php?url={$image_URL}&w=150&h=150&z=FIT&c={$color}";

		$record_URL = "?func=form&table={$page_table}&id={$data[$unique_identifier]}";
		$image = $html->img()->src($image_URL);

		$image_link = $html->a()->href($record_URL)->add($image);
		$title_link = $html->a()->href($record_URL)->content($data[$title_field]);

		$image_div = $html->div()->add($image_link); // ->content($image_field);
		$title_div = $html->div()->add($title_link); // ->content($title_field);
		$gallery_div = $html->div()->class("gallery")->add($image_div)->add($title_div);

		return $gallery_div;
	}

	function copy ($page_name="", $data="", $forward_page="") {
		global $COPY, $html;

		$copy_div = $html->div();
		$i = 0;
		$MAX_COPY_LENGTH = 150;
		foreach ($COPY AS $copy_name => $copy) {
			$row_class = ($i++%2) ? "row_odd" : "row_even";
			$copy_row = $html->div()->class("row {$row_class}");
			$copy_text = include_capture($copy[COPY_INCLUDE_PAGE]);
			$copy_text = strip_tags($copy_text);
			$copy_link = $html->a()->href("control_panel.php?func=copy_form&table={$copy_name}")->content($copy[COPY_DESCRIPTION]);
			if (strlen($copy_text) > $MAX_COPY_LENGTH) {
				$space_index = strpos($copy_text, " ", $MAX_COPY_LENGTH);
				$copy_text = substr($copy_text, 0, $space_index);
			}
			$copy_field_div = $html->div()->class("row_field")->add($copy_link)->content(" - " . $copy_text);
			$copy_row->add($copy_field_div)->add( $html->div()->class("clear") );

			$copy_div->add($copy_row);
		}
		/* Remove until fully functional... */
		/*  $add_copy = $html->button()->value("Add Copy")->onClick("addCopy()"); */
		$control_div = $html->div()->add($add_copy);

		$copy_div->add($control_div);
		return $copy_div;
	}

	function settings ($page_name, $data="", $forward_page="") {
		global $_SETTINGS, $SETTINGS, $html;

/*
		$settings_form = $html->form()->id("settings")->method("POST")->action("schema/save_settings.php");	// ->onsubmit("return settingsValidation('$suffix')");

		foreach ($_SETTINGS AS $setting_name => $setting) {
			$setting_label = $setting[FIELD_LABEL];
			$setting_input = $html->text()->id($setting_name)->value($SETTINGS[$setting_name]);
			$label_div = $html->div()->class("field_label")->content($setting_label);
			$input_div = $html->div()->class("field_input")->add($setting_input);
			$extras_div = $html->div()->class("field_extras")->add($setting[FIELD_NOTES]);
			$setting_div = $html->div()->class("field")->add($label_div)->add($input_div)->add($extras_div);
			$settings_form->add($setting_div);
		}
		// $add_setting = $html->button()->value("Add a Setting")->onClick("addSetting()");
		$submit = $html->submit()->value("Save Settings");
		$submit_label = $html->div()->class("field_label")->content("&nbsp;");
		$submit_div = $html->div()->class("field_input")->add($submit); // ->add($add_setting)->content("&nbsp;")

		$control_div = $html->div()->class("field")->add($submit_label)->add($submit_div);
		$settings_form->add($control_div)->add( $html->div()->class("clear") );
*/
		$settings_form = SchemaManager::custom_form($_SETTINGS, "", $SETTINGS);	// , "", "/schema/save_settings.php");

		return $settings_form;
	}

	function copy_form ($page_name, $data="", $forward_page="") {
		global $COPY, $html;

		$copy_form = $html->form()->method("POST")->action("schema/save_copy.php");

		$copy_include_page = $COPY[$page_name][COPY_INCLUDE_PAGE];
		$copy_text = include_capture($copy_include_page);
		$copy_include_input = $html->hidden()->id("copy_include")->value($copy_include_page);
		$copy = $html->textarea()->id("copy")->content($copy_text)->cols(55)->rows(8);

		$copy_label_div = $html->div()->class("field_label")->content("&nbsp;");
		$copy_input_div = $html->div()->class("field_input")->add($copy);
		SchemaManager::init_tinyMCE();

		$submit = $html->submit()->value("Submit");
		$cancel = $html->button()->value("Cancel")->onClick("window.location = 'control_panel.php?func=copy';");

		$copy_div = $html->div()->add($copy_include_input)->add($copy_label_div)->add($copy_input_div);
		$control_label_div = $html->div()->class("field_label")->content("&nbsp;");
		$control_input_div = $html->div()->class("field_input")->add($submit)->add($cancel);
		$control_div = $html->div()->class("field")->add($control_label_div)->add($control_input_div);
		$copy_form->add($copy_div)->add($control_div);

		$copy_form->add( $html->div()->class("clear") );

		return $copy_form;
	}

	function get_schema_field_row ($page_table, $field_num, $field_name, $field) {
		global $html, $FIELD_TYPES, $REQUIRED_OPTIONS;

		/* $row_class = ($field_name%2) ? "row_odd" : "row_even"; */
		$spacer = $html->div()->class("field_label")->content("&nbsp;");
		$field_div = $html->div()->class("field {$row_class}")->id($field_name);

		if ($field_num == 1) {
			$field_name_header = "Name<BR>";
			$field_type_header = "Type<BR>";
			$field_label_header = "Label (formal name)<BR>";
			$field_required_header = "<BR>";
			$field_extras_header = "<BR>";
		}

		$field_name_input = $html->text()->id("name_{$field_num}")->value($field_name);
		$datatype = $html->select($FIELD_TYPES, $field[FIELD_TYPE])->id("type_{$field_num}");
		$required = $html->select($REQUIRED_OPTIONS, $field[FIELD_REQUIRED])->id("required_{$field_num}");
		$label = $html->text()->value($field[FIELD_LABEL])->id("label_{$field_num}");
		$add_feature_button = $html->img()->src("schema/images/plus.gif")->class("clickable")->onClick("addFieldModifier('{$field_name}')")->title("Add a Feature to this Field");
		$delete_field_button = $html->img()->src("schema/images/delete.gif")->class("clickable")->onClick("dropDatabaseField('{$page_table}', '{$field_name}')")->title("Delete this Field");

		$field_name_div = $html->div()->class("field_input")->content($field_name_header)->add($field_name_input);
		$datatype_div = $html->div()->class("field_input")->content($field_type_header)->add($datatype);
		$label_div = $html->div()->class("field_input")->content($field_label_header)->add($label);
		$required_div = $html->div()->class("field_input")->content($field_required_header)->add($required);
		$extras_div = $html->div()->id("control_{$field_num}")->class("field_extras transparent")->content($field_extras_header)->add($add_feature_button)->add($delete_field_button);

		$field_div->add($spacer)->add($field_name_div)->add($field_type_div)->add($field_label_div)->add($datatype_div)->add($label_div)->add($required_div)->add($extras_div);
		$field_div->onMouseOver("$('#control_{$field_num}').stop().fadeTo('fast', 1.0)")->onMouseOut("$('#control_{$field_num}').stop().fadeTo('fast', 0.0)");

		return $field_div;
	}

	function schema ($page_table) {
		global $html, $SCHEMA, $FIELD_TYPES, $FIELD_MODIFIERS, $REQUIRED_OPTIONS, $TABLE_MODIFIERS;

		$tables = array("" => "&lt; New Table &gt;");
		foreach ($SCHEMA AS $table_name => $table) {
			/* if ($page_table == "") $page_table = $table_name; */

			$tables[$table_name] = $table[TABLE_LABEL];
		}
		$TABLE = $SCHEMA[$page_table];

		$schema_form = $html->form()->id("schema_form")->method("POST")->onSubmit("return validateTableSchema()");	/* ->action("schema/save_database_table.php") */
		$error_div = $html->div()->class("error")->id("schema_error");
		$schema_form->add($error_div);

		$fields = array();
		$fields_div = $html->div()->id("fields");
		if (isset($SCHEMA[$page_table])) {

			$spacer = $html->div()->class("field_label")->content("&nbsp;");
			$i = 0;
			foreach ($TABLE AS $field_name => $field) {
				if (!is_array($field))
					continue;
				else {
					$i++;
					if ($field[FIELD_TYPE] != LINK_N_TO_N && $field[FIELD_TYPE] != LINK_ONE_TO_N)
						$fields[$field_name] = $field_name;
					$field_div = SchemaManager::get_schema_field_row($page_table, $i, $field_name, $field);

					$j = 0;
					foreach ($field AS $modifier => $value) {
						if ($modifier == FIELD_NAME || $modifier == FIELD_TYPE || $modifier == FIELD_LABEL || $modifier == FIELD_REQUIRED)
							continue;
						else {
							$j++;
							$modifier_input = $html->select($FIELD_MODIFIERS, $modifier)->id("modifier_{$i}_{$j}");
							switch ($modifier) {
								case LINK_LABEL:
									$link_table_fields = array();
									foreach ($SCHEMA[$field[LINK_TABLE]] AS $link_table_field_name => $link_table_field) {
										if (!is_array($link_table_field))
											continue;
										$link_table_fields[$link_table_field_name] = $link_table_field[FIELD_LABEL];
									}

									$modifier_value = $html->select($link_table_fields, $value);
									break;
								case LINK_TABLE:
									$modifier_value = $html->select($tables, $value)->onChange("");
									break;
								default:
									$modifier_value = $html->text()->value($value);
							}
							$modifier_value->id("modifier_{$i}_{$j}_value");
							$delete_modifier = $html->img()->class("clickable")->src("schema/images/delete.gif")->onClick("deleteFieldModifier('{$field_name}', $i, $j)");
							$modifier_input_div = $html->div()->class("field_input")->add($modifier_input);
							$modifier_value_div = $html->div()->class("field_input")->add($modifier_value);
							$modifier_extras_div = $html->div()->id("modifier_{$i}_{$j}_extras")->class("field_extras transparent")->add($delete_modifier);
							$modifier_div = $html->div()->id("modifier")->class("field")->add($spacer)->add($spacer)->add($modifier_input_div)->add($modifier_value_div)->add($modifier_extras_div);
							$modifier_div->onMouseOver("$('#modifier_{$i}_{$j}_extras').stop().fadeTo('fast', 1.0)")->onMouseOut("$('#modifier_{$i}_{$j}_extras').stop().fadeTo('fast', 0.0)");
							$field_div->add($modifier_div);
						}
					}
					$num_modifiers = $html->hidden()->id("num_modifiers_{$i}")->value($j);
					$field_div->add($num_modifiers);
					$fields_div->add($field_div);
				}
			}
		}
		$num_fields = $html->hidden()->id("num_fields")->value($i);
		$schema_form->add($num_fields);

		$table_select = $html->select($tables, $page_table)->id("table")->onChange("editSchemaTableChanged()");
		/* $table_name = $html->text()->id("table_name")->value($page_table)->onChange("adjustTableName()"); */
		$table_label = $html->text()->id("table_label")->value($TABLE[TABLE_LABEL]);
		$table_sort = $html->select($fields, $TABLE[TABLE_SORT])->id("table_sort");
		$add_field = $html->button()->value("Add Field")->onClick("addDatabaseField('{$page_table}')");
		$save_table = $html->button()->value("Save")->onClick("validateTableSchema()");		/* submit()->value("Save"); */
		$drop_table = $html->button()->value("Drop Table")->onClick("dropDatabaseTable('{$page_table}')");

		$table_select_label_div = $html->div()->class("field_label")->content("Table");
		$table_select_input_div = $html->div()->class("field_input")->add($table_select);
		$table_label_label_div = $html->div()->class("field_label")->content("Table Label");
		$table_label_input_div = $html->div()->class("field_input")->add($table_label);
		$table_sort_label_div = $html->div()->class("field_label")->content("Table Default Sort");
		$table_sort_input_div = $html->div()->class("field_input")->add($table_sort);

		$table_select_div = $html->div()->class("field")->add($table_select_label_div)->add($table_select_input_div);
		$table_label_div = $html->div()->class("field")->add($table_label_label_div)->add($table_label_input_div);
		$table_sort_div = $html->div()->class("field")->add($table_sort_label_div)->add($table_sort_input_div);
		$control_label_div = $html->div()->class("field_label")->content("&nbsp;");
		$control_input_div = $html->div()->class("field_input")->add($add_field)->add($save_table);
		$control_div = $html->div()->class("field")->add($control_label_div)->add($control_input_div);

		if ($page_table != "") {
			$control_div->add($drop_table);
		}

		$schema_form->add($table_select_div);
		$schema_form->add($table_label_div);
		$schema_form->add($table_sort_div);
		$schema_form->add($fields_div);
		$schema_form->add($control_div);
		$schema_form->add( $html->div()->class("clear") );

		return $schema_form;
	}

	function has_permissions ($required_permissions) {
		global $LOGIN_ID;

		$access = $_SESSION["{$LOGIN_ID}_permissions"];
// echo "$access vs $required_permissions:";

		if ($access == DIETY)	// A DIETY can see anything.
			$allowed = true;

		if ($access == ADMINISTRATOR) {	// An ADMINISTRATOR can see everything except things designated to DIETIES
			$allowed = ($required_permissions != DIETY);
		}
		else if ($required_permissions[0] == "=")	// Only allow if User has exactly the same permissions
			$allowed = ($access == substr($required_permissions, 1));
		else $allowed = ($access >= $required_permissions);
//echo "{$allowed}<br>";
		return $allowed;
	}

	function is_field_attached ($TABLE, $table_name, $field, $data) {
		// If the thing that this field is attached to is not in the table, the field is attached.
		if (!isset($field[FIELD_ATTACHMENT]))
			return true;

		// TODO: FIELD_DEFAULT && SESSION_DEFAULT here?
		$table_identifier = SchemaManager::get_table_unique_identifier($table_name);
		if (!$data[$table_identifier]) {
			if ($field[SESSION_DEFAULT])
				$data[$field[FIELD_NAME]] = $field[SESSION_DEFAULT];
			else if ($field[FIELD_DEFAULT])
				$data[$field[FIELD_NAME]] = $field[FIELD_DEFAULT];
		}

		// The presence of this field depends on multiple fields
		if (is_array($field[FIELD_ATTACHMENT])) {
			$operator = ($field[FIELD_ATTACHMENT_OPERATOR]) ? $field[FIELD_ATTACHMENT_OPERATOR] : "&&";
			$result = ($operator == "&&");
			foreach ($field[FIELD_ATTACHMENT] AS $parent_field => $target_value) {
				$parent_value = $data[$parent_field];
				$match = SchemaManager::get_attachment_match($target_value, $parent_value);
				if ($operator == "&&")
					$result = ($result && $match);
				else $result = ($result || $match);
			}
			return $result;
		}
		else {
			$target_value = $field[FIELD_ATTACHMENT_VALUE];
			$parent_value = $data[$field[FIELD_ATTACHMENT]];
			return SchemaManager::get_attachment_match($target_value, $parent_value);
		}
	}

	function get_attachment_match ($target_value, $parent_value) {
		if ($target_value[0] == "!") {
			return (substr($target_value, 1) != $parent_value);
		}
		else if ($target_value[0] == "<") {
			if ($target_value[2] == "=")
				return (substr($target_value, 1) <= $parent_value);
			else return (substr($target_value, 1) < $parent_value);
		}
		else if ($target_value[0] == ">") {
			if ($target_value[2] == "=")
				return (substr($target_value, 1) >= $parent_value);
			else return (substr($target_value, 1) > $parent_value);
		}
		else return ($parent_value == $target_value);
	}

	/**
	* $forward_page: The page that the user should be returned to after processing, if $function is "form"
	*
	*/
	function control_panel ($page_table="", $function="browse", $entity_ID="", $forward_page="") {
		global $SCHEMA, $SETTINGS, $COPY, $_SETTINGS, $LOGIN_ID, $LOGO_URL, $COMPANY_NAME, $SEARCH_OPERATORS, $html, $mysql_connection;

		$tables = array_keys($SCHEMA);
		$access = $_SESSION["{$LOGIN_ID}_permissions"];

		$data = array();
		if (($SCHEMA[$page_table][TABLE_TYPE] == TABLE_GROUP || $page_table == "") && ($function == "form" || $function == "browse")) {
			foreach ($tables AS $table) {
				if (SchemaManager::has_permissions($SCHEMA[$table][TABLE_ACCESS])) {
					if ($SCHEMA[$page_table][TABLE_TYPE] == TABLE_GROUP) {
						if ($SCHEMA[$table][TABLE_PARENT] != $page_table)
							continue;
					}
					$page_table = $table;
					break;
				}
			}
			if ($page_table == "") exit;
		}
		else if ($entity_ID != "") {
			$table_identifier = SchemaManager::get_table_unique_identifier($page_table);
			$query = "SELECT * FROM {$page_table} WHERE {$table_identifier} = '{$entity_ID}'";
			$results = $mysql_connection->sql($query);
			if ($results->has_next())
				$data = $results->next();
		}

		$title = $SCHEMA[$page_table][TABLE_LABEL];
		if ($function == "browse_files")
			$title = "Browse Files";
		if ($function == "copy" || $function == "copy_form")
			$title = "Edit Copy";
		else if ($function == "settings")
			$title = "Edit Settings";
		else if ($function == "schema" && $page_table == "")
			$title = "Add New Table";

		$control_panel_div = $html->div()->class("control_panel_frame");
		$header_div = $html->div()->class("page_header");
		$menu_div = $html->div()->class("menubar_carrier")->id("menubar_carrier");
/* $html->script->add( $html->script()->content("makeDraggable(getElement('menubar_carrier'))") ); */
		$menubar_div = $html->div()->class("menubar")->add($menu_div);
		$menubar_container_div = $html->div()->class("menubar_container")->add($menubar_div);
/*
		$scroll_menu_left = $html->img()->src("schema/images/scroll_left.jpg")->class("clickable")->onClick("scrollMenuLeft()");
		$scroll_menu_right = $html->img()->src("schema/images/scroll_right.jpg")->class("clickable")->onClick("scrollMenuRight()");
		$menubar_control_div = $html->div()->class("menubar_control")->add($scroll_menu_left)->add($scroll_menu_right);
*/
		$title_div = $html->div()->class("header")->content($title)->content(" &gt; ");
		$content_div = $html->div()->id("control_panel_content");
		$body_div = $html->div()->id("control_panel_body")->class("page_body")->add($content_div);
		$footer_div = $html->div()->class("page_footer")->content("All content herein is confidential and property of {$SETTINGS['COMPANY_NAME']}, Copyright &copy; " . date("Y"));

		if ($function == "form") {
			$browse_link = $html->a()->href("?func=browse&table={$page_table}&browse=1")->content("Browse");
			$title_div->add($browse_link);
		}
		else if ($function == "browse") {
			/*  Search Form */
			$search_fields = array("");
			foreach ($SCHEMA[$page_table] AS $field) {
				if (!is_array($field))	/* Not a field, but a table setting. */
					continue;

				$field_name = $field[FIELD_NAME];
				$field_type = $field[FIELD_TYPE];
				if ($field_type == BOOL || $field_type == HTML || /* $field_type == LINK || */ $field_type == LINK_ONE_TO_N || $field_type == LINK_N_TO_N || $field_type == LINK_MUTUAL)
					continue;

				$search_fields[$field_name] = $field[FIELD_LABEL];
			}
			$search_in = $html->select($search_fields, $_REQUEST['search_in'])->id("search_in");
			$search_for = $html->text()->id("keywords")->value($_REQUEST['search_for']);
			$operator = $html->select($SEARCH_OPERATORS, $_REQUEST['op'])->id("operator");
			$search_button = $html->button()->value("Search")->onClick("search('{$page_table}')");
			$clear_search = $html->button()->value("Clear")->onClick("clearSearch('{$page_table}')");
			$search_div = $html->div()->id("search")->add($search_in)->add($operator)->add($search_for)->add($search_button);

			if ($SCHEMA[$page_table][TABLE_TYPE] != CUSTOM_TABLE)
				$title_div->add($search_div);

			if (false && $access >= ADMINISTRATOR) {
				$edit_schema_link = $html->a()->href("?func=schema&table={$page_table}")->content("Edit Schema");
				$title_div->add($edit_schema_link)->content(" &gt; ");
			}
			if ($access >= $SCHEMA[$page_table][RECORD_CREATION] && $SCHEMA[$page_table][TABLE_TYPE] != CUSTOM_TABLE) {
				$add_new_link = $html->a()->href("?table={$page_table}&func=form")->content("Add New");
				$title_div->add($add_new_link);
			}
		}
		/* else if ($function == "copy" || $function == "copy_form" || $function == "settings" || $function == ) {} */

		if ($LOGO_URL != "") {
			$logo = $html->img()->src($LOGO_URL);
			$logo_div = $html->div()->id("logo_header")->add($logo);
			$header_div->add($logo_div);
		}

		if (false && SchemaManager::has_permissions(ADMINISTRATOR)) {
			$file_tab_class = "menu_tab";
			if ($function == "browse_files")
				$file_tab_class = "menu_tab menu_tab_selected";
			$file_link = $html->a()->href("?func=browse_files")->content("Files");
			$file_tab = $html->div()->class($file_tab_class)->add($file_link);
			$menu_div->add($file_tab);
		}
		if ((!isset($SETTINGS['COPY_ACCESS']) && count($COPY) > 0) && SchemaManager::has_permissions($SETTINGS['COPY_ACCESS'])) {
			$copy_tab_class = "menu_tab";
			if ($function == "copy" || $function == "copy_form")
				$copy_tab_class = "menu_tab menu_tab_selected";
			$copy_link = $html->a()->href("?func=copy")->content("Copy");
			$copy_tab = $html->div()->class($copy_tab_class)->add($copy_link);
			$menu_div->add($copy_tab);
		}
		if ((!isset($SETTINGS['SETTINGS_ACCESS']) && count($_SETTINGS) > 0) || SchemaManager::has_permissions($SETTINGS['SETTINGS_ACCESS'])) {
			$settings_tab_class = "menu_tab";
			if ($function == "settings")
				$settings_tab_class = "menu_tab menu_tab_selected";
			$settings_link = $html->a()->href("?func=settings")->content("Settings");
			$settings_tab = $html->div()->class($settings_tab_class)->add($settings_link);
			$menu_div->add($settings_tab);
		}
		if (false && SchemaManager::has_permissions(ADMINISTRATOR)) {
			$schema_tab_class = "menu_tab";
			if ($function == "schema")
				$schema_tab_class = "menu_tab menu_tab_selected";
			$schema_link = $html->a()->href("?func=schema")->content("Schema");
			$schema_tab = $html->div()->class($schema_tab_class)->add($schema_link);
			$menu_div->add($schema_tab);
		}

		SchemaManager::create_table_menu($menu_div, $tables, $page_table);

		$log_out_link = $html->a()->href("log_out.php")->content("Log Out");
		$export_link = $html->a()->href("schema/export_table.php?table={$page_table}&search_in={$_REQUEST['search_in']}&op={$_REQUEST['op']}&search_for={$_REQUEST['search_for']}&sort={$_REQUEST['sort']}" /* "javascript: exportTable('$table_name')" */)->content("Export");

		$log_out_div = $html->div()->class("footer")->content("&lt; ")->add($log_out_link);
		if ($function != "copy" && $function != "copy_form" && $function != "settings" && $access >= $SCHEMA[$page_table][RECORD_ACCESS] && $SCHEMA[$page_table][TABLE_TYPE] != CUSTOM_TABLE) {
			$log_out_div->content(" &lt; ")->add($export_link);
		}
		/* $menu_div->add($log_out_div) */

		$form_div = SchemaManager::$function($page_table, $data, $forward_page);
		
		$header_div->add($menubar_container_div)->add($menubar_control_div);
		$content_div->add($title_div);
		$content_div->add($form_div);
		$content_div->add($log_out_div);

		$control_panel_div->add($header_div)->add($body_div)->add($footer_div);;

		return $control_panel_div;
	}

	/* Create the <DIV> that holds links to each table. */
	function create_table_menu (&$menu_div, $tables, $selected_table, $parent_table="") {
		global $SCHEMA, $html;

		$ancestor = $selected_table;

		/* Get the next table in the "tree" of this table family. */
		while ($SCHEMA[$ancestor] != "" && $SCHEMA[$ancestor][TABLE_PARENT] != $parent_table) {
			$ancestor = $SCHEMA[$ancestor][TABLE_PARENT];
		}

		$submenu_tables = 0;
		foreach ($tables AS $table) {
			if ($SCHEMA[$table][TABLE_PARENT] != $parent_table) {
				/* This table is a member of an immediate submenu. */
				if ($SCHEMA[$table][TABLE_PARENT] == $ancestor) {
					$submenu_tables++;
				}
				continue;
			}

			/* User does not have permissions to view this table. */
			if (!SchemaManager::has_permissions($SCHEMA[$table][TABLE_ACCESS]))
				continue;

			$menu_tab = $html->div()->class("menu_tab");
			if ($table == $selected_table || $table == $ancestor)
				$menu_tab->class .= " menu_tab_selected";

			$tab_link = $html->a()->href("?table={$table}")->content($SCHEMA[$table][TABLE_LABEL]);
			$menu_tab->add($tab_link);

			$menu_div->add($menu_tab);
		}
		$menu_div->add( $html->div()->class("clear") );
		if ($selected_table != $parent_table) {
			if ($submenu_tables > 0) {
				$submenu_div = $html->div()->class("submenu");
				SchemaManager::create_table_menu($submenu_div, $tables /* $submenu_tables */, $selected_table, $ancestor);
				$menu_div->add($submenu_div);
			}
		}
	}

	function export_table ($table, $search_for="", $operator="", $search_in="") {
		global $SCHEMA, $mysql_connection;

		$filename = "{$table}_" . date("m_d_Y") . ".csv";
		$file_handle = fopen($filename, "w+");

		$query = SchemaManager::construct_result_query($table, $search_for, $operator, $search_in, "", $sort, "", true);
// echo $query;
		$results = $mysql_connection->sql($query);

		$headers_written = false;
		while ($results->has_next()) {
			$row = $results->next();
			/* Map linked values & enumerations. */
			foreach ($SCHEMA[$table] AS $field) {
				$field_name = $field[FIELD_NAME];
				switch($field[FIELD_TYPE]) {
					case ENUMERATION:
						$row[$field_name] = $field[FIELD_OPTIONS][$row[$field_name]];
						break;
					case LINK:
						$row[$field_name] = $row["{$field_name}_label"];
						unset($row["{$field_name}_label"]);
						break;
				}
			}
			if (!$headers_written) {
				$headers = array();
				foreach ($row AS $header => $value) {
					$table_identifier = SchemaManager::get_table_unique_identifier($table);
					if (isset($SCHEMA[$table][$header]) || $header == $table_identifier)
						$headers[] = $header;
				}
				fputcsv($file_handle, $headers);
				$headers_written = true;
			}

			fputcsv($file_handle, $row);
		}
		fclose($file_handle);

		return $filename;
	}

	function init_all () {
		global $SCHEMA, $DATABASE_NAME, $mysql_connection;

/*
		if ($DATABASE_NAME != "") {
			$database_create_query = "CREATE DATABASE IFT NOT EXISTS {$DATABASE_NAME}";
			$mysql_connection->query($database_create_query);
		}
*/
		foreach ($SCHEMA as $table_name => $table_schema)
			SchemaManager::init_table($table_name);
	}

	function init_table ($table_name) {
		global $SCHEMA, $DATATYPES, $mysql_connection;

		if (!isset($SCHEMA[$table_name]))
			return;

		$table_identifier = SchemaManager::get_table_unique_identifier($table_name);
		$fields = array("{$table_identifier} SERIAL");
		foreach ($SCHEMA[$table_name] AS $field_name => $field) {
			if (!is_array($field))	/* Not a field, but a table setting e.g. TABLE_NAME, TABLE_LABEL */
				continue;

			$field_name = $field[FIELD_NAME];
			$field_type = $field[FIELD_TYPE];

			if ($field_type == USER_DEFAULT) {
				$link_table = $field[LINK_TABLE];
				$link_field = $field[LINK_FIELD];
				$field_type = $SCHEMA[$link_table][$link_field][FIELD_TYPE];
			}

			/* External reference to this table. Do not reference field in table definition. */
			if ($field_type == "" || $field_type == HTML || $field_type == LINK_ONE_TO_N || $field_type == JEKILL_CONTENT || $field_type == IMAGE_ANNOTATION)
				continue;

			/* Explicit external relationship. Create map table, but do not reference field in table definition */
			else if ($field_type == LINK_N_TO_N || $field_type == LINK_MUTUAL) {
				SchemaManager::create_map_table($table_name, $field_name);
				continue;
			}

			/* Can specify normal MySQL types, e.g. CHAR(32), etc. */
			$mapped_field_type = $field_type;
			if (isset($DATATYPES[$field_type]))
				$mapped_field_type = $DATATYPES[$field_type];

			if ($field[FIELD_TYPE] == SET) {
				$set_options = implode("','", array_keys($field[SET_OPTIONS]));
				$mapped_field_type .= "('{$set_options}')";
			}

			$fields[] = "{$field_name} {$mapped_field_type}";
		}
		$field_declaration = implode(", ", $fields);
		$create_query = "CREATE TABLE {$table_name} ({$field_declaration})";

		$mysql_connection->query("DROP TABLE $table_name");
		$result = $mysql_connection->query($create_query);
		$error = $mysql_connection->get_error();

		echo "{$create_query} {$error}<BR>";

		if (!$result)
			$result = $error;

		return $result;
	}

	function create_map_table ($table_name, $field_name) {
		global $SCHEMA, $mysql_connection;

		$link_table = $SCHEMA[$table_name][$field_name][LINK_TABLE];
		$map_table = SchemaManager::get_map_table_name($table_name, $link_table, $field_name);
		if ($table_name == $link_table) {	// A table refers to other records of its own type
			$table_one_ID = "one_ID";
			$table_two_ID = "two_ID";
		}
		else {
			$table_one_ID = "{$table_name}_ID";
			$table_two_ID = "{$link_table}_ID";
		}
		$map_query = "CREATE TABLE IF NOT EXISTS {$map_table} (map_ID SERIAL, record_num BIGINT UNSIGNED, {$table_one_ID} BIGINT UNSIGNED, {$table_two_ID} BIGINT";
		if ($SCHEMA[$table_name][TABLE_ENGINE] == INNODB) {
			$table_one_unique_identifier = SchemaManager::get_table_unique_identifier($table_name);
			$map_query .= ", FOREIGN KEY ({$table_one_ID}) REFERENCES {$table_name}($table_one_unique_identifier) ON DELETE CASCADE";
		}
		if ($SCHEMA[$link_table][TABLE_ENGINE] == INNODB) {
			$table_two_unique_identifier = SchemaManager::get_table_unique_identifier($link_table);
			$map_query .= ", FOREIGN KEY ({$table_two_ID}) REFERENCES {$link_table}({$table_two_identifier}) ON DELETE CASCADE ENGINE=INNODB";
		}
		$map_query .= ")";

//CREATE TABLE IF NOT EXISTS user_page_map (map_ID SERIAL, record_num BIGINT, page_ID BIGINT, user_ID BIGINT, FOREIGN KEY (page_ID) REFERENCES page(page_ID) ON DELETE CASCADE, FOREIGN KEY (user_ID) REFERENCES user(user_ID) ON DELETE CASCADE) ENGINE=INNODB
		$mysql_connection->query($map_query);
	}

	function disassociate_records ($table_name, $field_name, $entity_ID, $subrecord_ID) {
		global $SCHEMA;

		$field = $SCHEMA[$table_name][$field_name];
		SchemaManager::disassociate($table_name, $field[LINK_TABLE], $field_name, $field[FIELD_TYPE],  $entity_ID, $subrecord_ID);
	}

	function disassociate ($table_name, $link_table, $field_name, $field_type, $entity_ID, $subrecord_ID) {
		global $SCHEMA, $mysql_connection;

		$map_table_name = SchemaManager::get_map_table_name($table_name, $link_table, $field_name);
		$local_key = (isset($SCHEMA[$table_name][$field_name][LINK_LOCAL_KEY])) ? $SCHEMA[$table_name][$field_name][LINK_LOCAL_KEY] : "{$table_name}_ID";
		$foreign_key = (isset($SCHEMA[$table_name][$field_name][LINK_FOREIGN_KEY])) ? $SCHEMA[$table_name][$field_name][LINK_FOREIGN_KEY] : "{$link_table}_ID";
		if ($field_type == LINK_MUTUAL)
			$map_delete = "DELETE FROM {$map_table_name} WHERE (one_ID = '{$entity_ID}' AND two_ID = '{$subrecord_ID}') OR (one_ID = '{$subrecord_ID}' AND two_ID = '{$entity_ID}')";
		else if ($subrecord_ID == "") {
			/* One of the associated records was deleted, orphaning the map record. */
			$foreign_ID = SchemaManager::get_table_unique_identifier($link_table);
			$map_delete = "DELETE {$map_table_name} FROM {$map_table_name}
						LEFT JOIN {$link_table} ON ({$map_table_name}.{$foreign_key} = {$link_table}.{$foreign_ID})
						WHERE {$map_table_name}.{$local_key} = '{$entity_ID}' AND {$link_table}.{$foreign_ID} IS NULL";	// IS NULL: Join fails, because record has been deleted.
		}
		else {
			$map_delete = "DELETE FROM {$map_table_name} WHERE {$local_key} = '{$entity_ID}' AND {$foreign_key} = '{$subrecord_ID}'";
		}
// echo "{$map_delete}<p>"; exit;
		$mysql_connection->query($map_delete);
	}

	function delete ($table_name, $entity_ID) {
		global $SCHEMA, $mysql_connection;

		foreach ($SCHEMA AS $parent_table_name => $parent_table) {
			foreach ($parent_table AS $field) {
				if ($field[FIELD_TYPE] == LINK_N_TO_N || $field[FIELD_TYPE] == LINK_MUTUAL) {
					$link_table = $field[LINK_TABLE];
					$link_table_identifier = "{$table_name}_ID";	// SchemaManager::get_table_unique_identifier($link_table);
					$map_table = SchemaManager::get_map_table_name($parent_table_name, $link_table, $field[FIELD_NAME]);
					$query = "DELETE FROM {$map_table} WHERE {$link_table_identifier} = '{$entity_ID}'";
					if ($field[FIELD_TYPE] == LINK_MUTUAL)
						$query = "DELETE FROM {$map_table} WHERE one_ID = '{$entity_ID}' OR two_ID = '{$entity_ID}'";

					/* echo $query; */
					$mysql_connection->query($query);
				}
			}
		}
		$table_identifier = SchemaManager::get_table_unique_identifier($table_name);
		$query = "DELETE FROM {$table_name} WHERE {$table_identifier} = '{$entity_ID}'";
		$mysql_connection->query($query);
	}

	function check_schema_compatability ($table_name, $entity_ID) {
		global $SCHEMA, $html, $mysql_connection;

		if ($table_name == "")
			return;

		$query = "DESCRIBE {$table_name}";
		$results = $mysql_connection->sql($query);

		/* The table does not exist yet. */
		if (!$results->has_next()) {
			$warning_div = $html->div()->class("warning")->content("<B>Warning</B>: The table '{$table_name}' does not exist in the database.");
			$add_table_button = $html->button()->value("Create Table")->onClick("createDatabaseTable('{$table_name}')");
			$add_table_div = $html->div()->add($add_table_button);
			$warning_div->add($add_table_div);
		}
		else {
			$database_fields = array();
			while ($results->has_next()) {
				$field = $results->next();
				$database_fields[$field['Field']] = "1";
			}

			$non_existant_fields = array();
			foreach ($SCHEMA[$table_name] AS $field_name => $field) {
				if (!isset($database_fields[$field_name])) {
					/* If it is not a field, but rather a setting on the table. */
					if (!is_array($field) || $field[FIELD_TYPE] == HTML || ($field[FIELD_NAME] == "_save"))	// TODO: || '_delete', etc.
						continue;

					/* If the field is not an 'implicit' relationship with the table */
					if ($field[FIELD_TYPE] != IMAGE_ANNOTATION && $field[FIELD_TYPE] != JEKILL_CONTENT && $field[FIELD_TYPE] != LINK_ONE_TO_N && $field[FIELD_TYPE] != LINK_N_TO_N && $field[FIELD_TYPE] != LINK_MUTUAL)
						$non_existant_fields[] = $field_name;
					else if ($field[FIELD_TYPE] == LINK_N_TO_N || $field[FIELD_TYPE] == LINK_MUTUAL) {
						$link_table = $SCHEMA[$table_name][$field_name][LINK_TABLE];
						$map_table_name = SchemaManager::get_map_table_name($table_name, $link_table, $field_name);
						$map_table_presence = $mysql_connection->sql("DESCRIBE {$map_table_name}");
						if (!$map_table_presence->has_next())
							$non_existant_fields[] = $field_name;
					}
				}
			}

			if (count($non_existant_fields) > 0) {
				$warning_div = $html->div()->class("warning");

				$update_database_div = $html->div();
				$update_database_button = $html->button()->value("Update Database")->onClick("updateDatabase('{$table_name}', '{$entity_ID}')");

				$non_existant_field_list = "";
				$i = 1;
				$table_name_input = $html->hidden()->id("table_name")->value($table_name);
				$entity_ID_input = $html->hidden()->id("entity_ID")->value($entity_ID);
				$update_database_div->add($table_name_input);
				foreach ($non_existant_fields AS $field_name) {
					$field_name_input = $html->hidden()->id("incompatible_field_name_{$i}")->value($field_name);
					$non_existant_field_list .= "<LI>" . $SCHEMA[$table_name][$field_name][FIELD_LABEL];
					$update_database_div->add($field_name_input);
					$i++;
				}
				$update_database_div->add($update_database_button);

				$warning_div->content("<B>Warning</B>: The following fields are not present in your database:<P><UL>{$non_existant_field_list}</UL><P>This may prevent your changes from being saved successfully.");
				$warning_div->add($update_database_div);
			}
		}
		return $warning_div;
	}

	function get_table_unique_identifier ($table_name, $suffix="") {
		global $SCHEMA;

//if (is_array($table_name))
//	throw new Exception("array");
		if (isset($SCHEMA[$table_name][TABLE_UNIQUE_ID]))
			return $SCHEMA[$table_name][TABLE_UNIQUE_ID] . $suffix;
		else return "{$table_name}_ID{$suffix}";
	}

	/* Include problem causing '$SCHEMA' not to register in function variation above. */
	function get_unique_identifier ($table, $table_name) {
		if (isset($table[TABLE_UNIQUE_ID]))
			return $table[TABLE_UNIQUE_ID];

		else return "{$table_name}_ID";
	}

	/* $ored_values - If $value holds multiple values that are powers of 2 that were |'d together. */
	function binary_value_array ($value, $ored_values=false) {
		$binary_value = decbin($value);
		$bin_length = strlen($binary_value);
		$value_array = array();
		for ($i=0; $i<$bin_length; $i++) {
			if ($binary_value[$bin_length-($i+1)] == 1) {
				if ($ored_values)
					$value_array[pow(2, $i)] = 1;
				else $value_array[] = $i;
			}
		}
		return $value_array;
	}

	function form ($table_name, $data="", $forward_page="", $process_page="", $suffix="", $parent_table="", $parent_field="", $parent_record_ID="") {
		global $SCHEMA, $mysql;
		$SCHEMA[$table_name][TABLE_NAME] = $table_name;
		if (isset($SCHEMA[$table_name][TABLE_FORM]))
			return SchemaManager::custom_table_form($SCHEMA[$table_name], $table_name, $data, $forward_page, $process_page, $suffix, $parent_table, $parent_field, $parent_record_ID);
		return SchemaManager::custom_form($SCHEMA[$table_name], $table_name, $data, $forward_page, $process_page, $suffix, $parent_table, $parent_field, $parent_record_ID);
	}

	function custom_table_browse ($page_table) {
		global $SCHEMA, $html, $mysql;

		if (isset($SCHEMA[$page_table][TABLE_STYLE]))
			$html->import_style($SCHEMA[$page_table][TABLE_STYLE]);

		$table_browse_script = $SCHEMA[$page_table][TABLE_BROWSE];
		$table_browse = include_capture($table_browse_script);
		return $table_browse;
	}

	function get_image_extras ($table_name, $field_name, $base_field_name, $entity_ID, $image_URL, $optimum_width, $optimum_height) {
		global $html, $SETTINGS;

		$preview = $html->img()->class("clickable")->id("preview_{$field_name}")->src("{$SETTINGS['JEKILL_ROOT']}/schema/images/image_icon.jpg");
		$preview->onMouseOver("imagePreview(event, '{$image_URL}?t=" . time() . "')")->onMouseOut("closeImagePreview()");
		$preview_link = $html->a()->href($image_URL)->target("_blank")->add($preview);
		$delete = $html->img()->src("{$SETTINGS['JEKILL_ROOT']}/schema/images/close.gif")->id("delete_{$field_name}")->class("clickable")->alt("Delete this image")->title("Delete this image")->onClick("deleteUploadedFile('{$table_name}', '{$base_field_name}', " . $entity_ID . ", '{$image_URL}')");
		$crop_resize = $html->img()->src("{$SETTINGS['JEKILL_ROOT']}/schema/images/crop_resize.gif")->class("clickable")->title("Crop / Resize Image")->onClick("formatImage('{$image_URL}', '{$optimum_width}', '{$optimum_height}')");
		$extras_div = $html->div()->add($preview_link)->content("&nbsp;")->add($crop_resize)->content("&nbsp;")->add($delete);

		return $extras_div;
	}

	function custom_table_form ($TABLE, $table_name, $data, $forward_page, $process_page, $suffix) {
		global $html;

		if (isset($TABLE[TABLE_STYLE]))
			$html->import_style($TABLE[TABLE_STYLE]);

		$table_form_script = $TABLE[TABLE_FORM];
		$table_form = include_capture($table_form_script);
		return $table_form;
	}

	function custom_input ($TABLE, $table_name, $field, $data, $entity_ID, $suffix, $parent_table, $parent_field, $parent_record_ID, &$attachment_listeners, &$option_listeners, &$rich_editors, &$optional_fields) {
		global $html, $mysql_connection, $mysql, $SCHEMA, $SETTINGS, $LOGIN_ID, $MONTH_NAMES, $YEARS, $AM_PM;

		$field_name = $field[FIELD_NAME];
		$clear_div = $html->div()->class("clear");
		$REQUIRED_INDICATOR = (isset($field[FIELD_REQUIRED_INDICATOR])) ? $field[FIELD_REQUIRED_INDICATOR] : $SETTINGS['DEFAULT_REQUIRED_INDICATOR'];

		if (!is_array($field))	/* If this is not a field, but a table modifier, e.g. TABLE_LABEL */
			return "";

		if ($field[FIELD_TYPE] == "")
			return "";

		$table_base_identifier = SchemaManager::get_table_unique_identifier($table_name);
		$base_field_name = $field[FIELD_NAME];
		$value = $data[$base_field_name];
		$field_name = $base_field_name . $suffix;
		$field_type = $field[FIELD_TYPE];
		$field_label = $field[FIELD_LABEL];
		$field_required = $field[FIELD_REQUIRED];
		$field_class = (isset($field[FIELD_CLASS])) ? " " . $field[FIELD_CLASS] : "";
		$required_class = ($field_required) ? " required" : "";

		$value_changed = 0;
		if ($value == "" && ($value != $field[FIELD_DEFAULT] && $value != $field[SESSION_DEFAULT])) {
			if (isset($field[FIELD_DEFAULT]))
				$value = $field[FIELD_DEFAULT];
			else $value = $_SESSION[$field[SESSION_DEFAULT]];

			$data[$base_field_name] = $value;
			$value_changed = 1;
		}
		$value = str_replace("\"", "&quot;", $value);

		$field_div = $html->div()->class("field{$required_class}{$field_class}")->id("{$field_name}_field");
		$field_label_label = $html->label()->for($field_name)->content($field_label);
		$label_div = $html->div()->class("field_label")->id("{$field_name}_label")->add($field_label_label);
		$input_div = $html->div()->class("field_input")->id("{$field_name}_input");

		$extras_div = $html->div()->class("field_extras")->id("{$field_name}_extras")->content($field[FIELD_NOTES]);

		if ($field[FIELD_HELP]) {
			$help_text = $html->span()->class("field_help_text")->content($field[FIELD_HELP]);
			// $help_icon = $html->img()->src("{$SETTINGS['JEKILL_ROOT']}/schema/images/help_icon.png");
			$field_help = $html->div()->class("field_help")->add($help_text);	// ->add($help_icon)
			$extras_div->add($field_help);
		}

		$input = $html->text()->class("input")->value($value);

		if ($field[FIELD_ATTACHMENT]) {
			if (!SchemaManager::is_field_attached($TABLE, $table_name, $field, $data))
				$field_div->class .= " hidden";

			if (is_array($field[FIELD_ATTACHMENT])) {
				foreach ($field[FIELD_ATTACHMENT] AS $parent_field => $attachment_value) {
					$attachment_listeners[$parent_field][$base_field_name] = $attachment_value;
				}
			}
			else {
				if (!$attachment_listeners[$field[FIELD_ATTACHMENT]])
					$attachment_listeners[$field[FIELD_ATTACHMENT]] = array();
				$attachment_listeners[$field[FIELD_ATTACHMENT]][$base_field_name] = $field[FIELD_ATTACHMENT_VALUE];
			}
		}

		/* If we're a subtable, and the SUBTABLE_DEFAULT is set. */
		if ($suffix != "" && $field[SUBTABLE_DEFAULT] != "") {
			$default_hidden = $html->hidden()->id($field_name)->value($value);
			if ($value == "")
				$default_hidden->value($data[$field[SUBTABLE_DEFAULT]]);
			$field_div->class("hidden")->add($default_hidden);
			return $field_div;
		}

		switch ($field_type) {
			case BOOL:
				$input = $html->checkbox()->class("checkbox");
				if ($value == 1 || ($value === "" && isset($field[FIELD_DEFAULT_VALUE]) && $field[FIELD_DEFAULT_VALUE] == 1))
					$input->checked("true");
				break;
			case BUTTON:
				$label_div->remove_all()->content("&nbsp;");
				$input = $html->button()->class("button")->value($field_label);
				break;
			case COLOR:
				$color_preview = $html->div()->class("color_swatch clickable")->id("{$field_name}_swatch")->style("background-color: #{$value}; border: solid #000000 1px; height: 20px; width: 20px;")->onClick("loadColorChooser('{$field_name}')");
				$extras_div->add($color_preview);
				break;
			case COPY:
				$input = $html->textarea()->class("copy_input")->content($value);
				break;
			case CREDIT_CARD:
				if ($value)
					$input->disabled = true;
				break;
			case DATE:
				$options = $field[FIELD_OPTIONS];
				if (isset($options[SEPARATE_DATE_COMPONENTS])) {
					$date_parts = explode("-", $value);
					$month = $html->select($MONTH_NAMES, $date_parts[1])->id("{$field_name}_month{$suffix}");
					$day = $html->input()->size(1)->id("{$field_name}_day{$suffix}")->value($date_parts[2]);
					$year = $html->select($YEARS, $date_parts[0])->id("{$field_name}_year{$suffix}");
					$input = $html->span()->add($month)->content(" ")->add($day)->content(" ")->add($year);
				}
				else {
/*
					$calendar_button = $html->img()->src("{$SETTINGS['JEKILL_ROOT']}/schema/images/calendar_icon.gif")->class("clickable")->onClick("loadDateChooser('{$field_name}')")->alt("Choose Date")->title("Choose Date");
					$extras_div->add($calendar_button);
*/
					$input->class("date");
					if ($value != "" && $value != "0000-00-00")
						$input->value(date("m/d/Y", strtotime($value)));
					else $input->value("");
				}

				break;
			case DATETIME:
				$calendar_button = $html->img()->src("{$SETTINGS['JEKILL_ROOT']}/schema/images/calendar_icon.gif")->class("clickable")->onClick("loadDateChooser('{$field_name}', '', '', true)")->alt("Choose Date")->title("Choose Date");
				$extras_div->add($calendar_button);
				if ($value != "")
					$input->value(date("m/d/Y h:i:s", strtotime($value)));
				break;
			case EMAIL:
				$input->class("email");
				break;
			case ENUMERATION:
				if ($field[FIELD_VARIATION] == RADIO) {
					// TODO: Incomplete
					// TODO: Later affect this with option-attachment.
					$option_num = 1;
					foreach ($field[FIELD_OPTIONS] AS $value => $option_text) {
						$option_ID = "{$field_name}_option_{$option_num}";
						$option_radio = $html->input()->type("radio")->id($option_ID)->value($value);
						// Set the name.
						$option_radio->__set("name", $field_name);
						$radio_label = $html->label()->for($option_ID)->content($option_text);
						$radio_div = $html->div()->class("radio")->add($option_radio)->add($radio_label);
						$input_div->add($radio_div);
						$option_num++;
					}
					$input = $html->null();		// Will accept , but will not render itself
					// $label_div->remove_all()->content("&nbsp;");
				}
				else {
					if (isset($field[OPTION_ATTACHMENT])) {
						$option_attachment_field_value = $data[$field[OPTION_ATTACHMENT]];
						$option_sources = $field[OPTION_ATTACHMENT_SOURCES];
						if (isset($option_sources[$option_attachment_field_value]))
							$field_options = $option_sources[$option_attachment_field_value];
						else $field_options = current($option_sources);
					}
					else $field_options = $field[FIELD_OPTIONS];
					if ($field[SELECT_MULTIPLE] != "" && $value != "") {
						$value = SchemaManager::binary_value_array($value);
					}
					$input = $html->select($field_options, $value)->class("enum");
					if ($field[SELECT_MULTIPLE] != "") {
						$select_size = $field[SELECT_SIZE];
						if ($select_size == "")
							$select_size = 3;
						$input->multiple("")->size($select_size);
					}
					if ($field[OPTION_ATTACHMENT] != "") {
						if ($option_listeners[$field[OPTION_ATTACHMENT] . $suffix] == "")
							$option_listeners[$field[OPTION_ATTACHMENT] . $suffix] = array();
						$option_listeners[$field[OPTION_ATTACHMENT] . $suffix][$field_name] = $field[OPTION_ATTACHMENT_SOURCES];
					}
				}
				break;
			case FILE:
				$input = $html->file()->class("file");
				//$file_label = $html->label()->content("Browse...")->class("file_label")->for($field_name);
				//$input_div->add($file_label);
				if ($value != "") {
					$file_icon = $html->img()->src("/schema/images/file_icon.gif");
					$file_link = $html->a()->href($value)->add($file_icon);
					$extras_div->add($file_link);
				}
				break;
			case HIDDEN_VALUE:
				// TODO: Just make this return <div><input type="hidden" /></div> ?
				$field[FIELD_NO_LABEL] = 1;
				$input = $html->hidden()->value($value);
				break;
			case HTML:
				$field_label = SchemaManager::replace_field_value($field_label, $data);
				return $field_div->id("{$field_name}_container")->content($field_label);
			case HTML_COPY:
				if ($suffix == "")
					$rich_editors[] = "{$field_name}_editor";	// SchemaManager::init_tinyMCE("{$field_name}_editor");
				$editor = $html->textarea()->class("copy_input")->content($value)->id("{$field_name}_editor");
				/* $toggle_editor_link = $html->a()->href("javascript: toggleRichEditor('{$field_name}_editor')")->content("[ Toggle Editor ]"); */
				$input = $html->hidden()->value(str_replace("\"", "&quot;", $value));
				$input_div->add($editor);	// ->add($toggle_editor_link);
				break;
			case ID:
				$input = $html->hidden()->value($value);
				break;
			case IMAGE:
				/* $label_div->content("<BR>")->add($preview); */
				$input = $html->file()->class("file");
				//$file_label = $html->label()->content("Browse...")->class("file_label")->for($field_name);
				//$input_div->add($file_label);

				if ($field[IMAGE_PREVIEW] && ($value || $field[IMAGE_DEFAULT_PREVIEW])) {
					$image_URL = ($value) ? $value : $field[IMAGE_DEFAULT_PREVIEW];
					$image_preview = $html->img()->src("{$SETTINGS['JEKILL_ROOT']}/image_excerpt.php?w=250&amp;h=100&amp;z=FIT&amp;a=L&amp;c=-000000&url={$image_URL}")->alt($field_label)->class("clickable")->onclick("\$('#{$field_name}').toggle('slow')")->title("Click to Change Image");
					$image_preview_div = $html->div()->add($image_preview);
					$input_div->add($image_preview_div);
					$input->class .= " hidden";
				}

				if ($value) {
// echo "$table_identifier vs {$data[$table_identifier]}<br/>";
					if (!isset($field[IMAGE_EXTRAS_ACCESS]) || $access > $field[IMAGE_EXTRAS_ACCESS]) {
						$image_extras = SchemaManager::get_image_extras ($table_name, $field_name, $base_field_name, $data[$table_base_identifier], $value, $field[IMAGE_OPTIMUM_WIDTH], $field[IMAGE_OPTIMUM_HEIGHT]);
						$extras_div->add($image_extras);

						if (isset($field[IMAGE_MAX_HEIGHT]) || isset($field[IMAGE_MAX_WIDTH]) || isset($field[IMAGE_MIN_HEIGHT]) || isset($field[IMAGE_MIN_WIDTH]) || isset($field[IMAGE_ASPECT_RATIO])) {
							$RESOURCE_PATH = $_SERVER['DOCUMENT_ROOT'] . "/";

							$dimensions = getimagesize($RESOURCE_PATH . $value);
							$image_errors = "";
							$manipulatable = true;
							if (isset($field[IMAGE_MAX_WIDTH]) && $dimensions[0] > $field[IMAGE_MAX_WIDTH])
								$image_errors .= "<LI> The width of the image ({$dimensions[0]}px) is more than the suggested maximum of " . $field[IMAGE_MAX_WIDTH] . "px.";
							if (isset($field[IMAGE_MAX_HEIGHT]) && $dimensions[1] > $field[IMAGE_MAX_HEIGHT])
								$image_errors .= "<LI> The height of the image ({$dimensions[1]}px) is more than the suggested maximum of " . $field[IMAGE_MAX_HEIGHT] . "px.";
							if ($dimensions[0] < $field[IMAGE_MIN_WIDTH]) {
								$image_errors .= "<LI> The width of the image ({$dimensions[0]}px) is less than the suggested minimum of " . $field[IMAGE_MIN_WIDTH] . "px.";
								$manipulatable = false;
							}
							if ($dimensions[1] < $field[IMAGE_MIN_HEIGHT]) {
								$image_errors .= "<LI> The height of the image ({$dimensions[0]}px) is less than the suggested minimum of " . $field[IMAGE_MIN_HEIGHT] . "px.";
								$manipulatable = false;
							}				
							if (isset($field[IMAGE_ASPECT_RATIO])) {
								$aspect_ratio_delta = (isset($field[IMAGE_ASPECT_DELTA])) ? $field[IMAGE_ASPECT_DELTA] : 0.05;
								$aspect_ratio = $dimensions[0]/$dimensions[1];
								if (abs($field[IMAGE_ASPECT_RATIO] - $aspect_ratio) > $aspect_ratio_delta) {
									$image_errors .= "<LI>The aspect ratio of the image (" . round($aspect_ratio, 2) . ") is outside the suggested ratio of " . $field[IMAGE_ASPECT_RATIO] . ".";
									/* Determine if the image is big enough to accommodate manipulation. */
									if ($dimensions[0] < $field[IMAGE_MIN_WIDTH] || $dimensions[1] < $field[IMAGE_MIN_HEIGHT]) /* If the image is too small, period. */
										$manipulatable = false;
									/* If a minimum size is specified, determine whether it is possible to achieve the aspect ratio with the minimum size given the dimensions of the image. */
									else if ((isset($field[IMAGE_MIN_WIDTH]) && isset($field[IMAGE_MIN_HEIGHT]))
										&& ($dimensions[0] > $field[IMAGE_MIN_WIDTH] && $dimensions[1] > $field[IMAGE_MIN_HEIGHT])
										&& $dimensions[0]/$dimensions[1] < $field[IMAGE_ASPECT_RATIO])
										$manipulatable = true;
									else $manipulatable = true;
								}
							}
							if ($image_errors != "") {
								$image_warning_div = $html->div()->class("warning")->content("The following problems were found with the current uploaded image:<P><UL>{$image_errors}</UL><P>");
								if ($manipulatable) {
									/* '$value' is the filename. */
									if (isset($field[IMAGE_MAX_WIDTH]) && isset($field[IMAGE_MAX_HEIGHT])) 
										$manipulate_image_link = $html->a()->href("javascript: formatImage('{$value}', '" . $field[IMAGE_OPTIMUM_WIDTH] . "', '" . $field[IMAGE_OPTIMUM_HEIGHT] . "')")->content("Resize / Crop Image");
									else $manipulate_image_link = $html->a()->href("javascript: manipulateImage('{$value}')")->content("Resize / Crop Image");
									$image_warning_div->add($manipulate_image_link);
								}
								$field_div->add($image_warning_div);
							}
						}
					}
				}
				break;
			case IMAGES:
				$field_directory = isset($field[ROOT_DIRECTORY]) ? $field[ROOT_DIRECTORY] : "/";
				$select_images = $html->a()->class("button_link")->href("javascript: //")->onclick("browseImages('{$field_name}', '$field_directory')")->content("Add Images");
				$disassociate_all = $html->a()->class("button_link")->href("javascript: //")->onclick("disassociateAllFiles('{$field_name}')")->content("Disassociate All");
				$input = $html->hidden()->value($value);
				$images_div = $html->div()->id("{$field_name}_files");	// ->class("file_bank"); // Class added by addImages(...)

				if ($value != "") {
					$value = str_replace("&quot;", "\"", $value);
					$preview_div = SchemaManager::get_images_preview_divide($field_name, $value);
					$images_div->add($preview_div);
				}
				$input_div->add($select_images)->content("&nbsp;")->add($disassociate_all)->add($images_div);
				break;
			case IMAGE_EXCERPT:
				$source_field = $field[LINK_FIELD];
				$source_URL = $data["{$source_field}{$suffix}"];
				$input = $html->hidden()->id("excerpt")->onchange("valueChanged('{$field_name}')")->value($value);
				if ($source_URL != "") {
					$excerpt_URL = SchemaManager::get_uploaded_filename($table_name, $field_name, $entity_ID, $source_URL);
					$create_excerpt_link = $html->a()->href("javascript: formatImage('{$source_URL}', '" . $field[IMAGE_OPTIMUM_WIDTH] . "', '" . $field[IMAGE_OPTIMUM_HEIGHT] . "', '" . $excerpt_URL . "', 'window.parent.saveImageExcerpt(\'{$field_name}\', \'{$excerpt_URL}\')')")->content("Select Excerpt");
					$input_div = $html->div()->class("field_input")->add($create_excerpt_link)->add($excerpt_input);
					if ($value != "") {
						$image_extras = SchemaManager::get_image_extras ($table_name, $field_name, $base_field_name, $data[$table_base_identifier], $value, $field[IMAGE_OPTIMUM_WIDTH], $field[IMAGE_OPTIMUM_HEIGHT]);
						$extras_div->add($image_extras);
					}
				}
				else $input_div = $html->div()->content("(Upload '" . $SCHEMA[$table_name][$source_field][FIELD_LABEL] . "' to Manipulate)");
				break;
			case IMAGE_ANNOTATION:
				$image_field = $SCHEMA[$parent_table][$parent_field][LINK_FIELD];
				if ($image_field == "")
					$image_field = $field[LINK_FIELD];

				$label_div->remove_all()->content("&nbsp;");
				if ($parent_record_ID == "")
					$input = $html->div();
				else $input = $html->a()->href("javascript: annotateImage('{$parent_table}', '{$image_field}', {$parent_record_ID}, '{$table_name}', '{$suffix}')")->content("Annotate");	// $field[FIELD_LABEL]);
				break;
			case IMAGE_SELECT:
/*
				if (!$value) {
					$image = $html->div()->style("background-color: #FFFFFF; border: solid #000000 1px; cursor: pointer; float: left; height: 55px; padding-top: 45px; width: 100px; text-align: center")->content("Select an Image");
				}
*/
				if (!$value)
					$image_URL = "{$SETTINGS['JEKILL_ROOT']}/schema/images/select_image.jpg";
				else {
					$image_URL = $value;
					if (isset($field[FIELD_OPTIONS]))
						$image_URL = $field[FIELD_OPTIONS][$value];
					$image_URL = "{$SETTINGS['JEKILL_ROOT']}/image_excerpt.php?w=100&amp;h=100&amp;z=FIT&amp;c=-000000&amp;url={$image_URL}";
				}

				$image = $html->img()->id("{$field_name}_preview")->src($image_URL)->style("cursor: pointer")->onclick("loadImageDropdown('{$base_field_name}', '{$suffix}')");
				$down_arrow = $html->img()->src("{$SETTINGS['JEKILL_ROOT']}/schema/images/down.gif")->onclick("loadImageDropdown('{$base_field_name}', '{$suffix}')");
				$options_div = $html->div()->id("{$field_name}_select")->class("hidden")->style("background-color: #FFFFFF; border: solid #CCCCCC 1px;  position: absolute; height: 400px; overflow: auto; width: 500px;");
				$input_div->add($image)->add($down_arrow)->add($options_div);

				$no_image = $html->img()->src("{$SETTINGS['JEKILL_ROOT']}/schema/images/no_image.jpg")->style("cursor: pointer; margin: 10px;")->onclick("selectDropdownImage('{$field_name}', '{$suffix}', '{$SETTINGS['JEKILL_ROOT']}/schema/images/no_image.jpg', '')");
				$options_div->add($no_image);

				// TODO
				if (isset($field[LINK_TABLE])) {

				}
				else {
					if (isset($field[ROOT_DIRECTORY])) {	// Specify images with a directory. TODO: Currently only .jpegs
						if (is_dir($field[ROOT_DIRECTORY])) {
							$dir_handle = opendir($_SERVER['DOCUMENT_ROOT'] . $field[ROOT_DIRECTORY]);
							while (($file = readdir($dir_handle)) !== false) {
								$extension = substr(strtolower($file), -4);
								if ($extension == ".jpg" || $extension == "jpeg" || $extension == ".gif" || $extension == ".png") {
									$image = $html->img()->src("{$SETTINGS['JEKILL_ROOT']}/image_excerpt.php?w=100&amp;h=100&amp;z=FIT&amp;c=-000000&amp;url={$field[ROOT_DIRECTORY]}/{$file}")->style("cursor: pointer; margin: 10px;")->onclick("selectDropdownImage('{$field_name}', '{$suffix}', '{$field[ROOT_DIRECTORY]}/{$file}', '{$field[ROOT_DIRECTORY]}/{$file}')");
									$options_div->add($image);
								}
							}
							closedir($dir_handle);
						}
					}
					if (isset($field[FIELD_OPTIONS])) {	// Specify images with an array of URLs
						foreach ($field[FIELD_OPTIONS] AS $select_value => $image_URL) {
							if (is_int($select_value))
								$select_value = $image_URL;	// Store image_URL in database if value is just an index.
							$image = $html->img()->src("{$SETTINGS['JEKILL_ROOT']}/image_excerpt.php?w=100&amp;h=100&amp;z=FIT&amp;c=-000000&amp;url={$image_URL}")->onclick("selectDropdownImage('{$field_name}', '{$suffix}', '{$image_URL}', '{$select_value}')");;
							$options_div->add($image);
						}
					}

					if (isset($field[ROOT_DIRECTORY]) && true) {
						$file_upload = $html->file()->id("{$field_name}_upload")->onchange("");
						$file_upload_div = $html->div()->add($file_upload);
						$options_div->add($file_upload_div);
					}
				}

				$input = $html->hidden()->value($value);
				break;
			case JEKILL_CONTENT:
				$page_field = $field[LINK_FIELD];
				$affected_page = $data[$page_field];
				$jekill_URL = "jekill.php?jtable={$table_name}&jfield={$page_field}&jid={$entity_ID}&jinclude={$field_name}";
				$link = $html->a()->href($jekill_URL)->content("Edit Content");
				$extras_div->add($link);
				break;
			case KEYWORDS:
				$input = $html->textarea()->content($value);
				break;
			case LINK:
			case MULTI_LINK:
				$link_table = $field[LINK_TABLE];
				$link_label = $field[LINK_LABEL];
				$link_where = $field[LINK_WHERE];
				$link_limit = $field[LINK_LIMIT];
				$link_sort = $field[LINK_SORT];
				$link_where = SchemaManager::replace_field_value($link_where, $data);

				if (isset($field[LINK_ATTACHMENT])) {
					$link_attachment = $field[LINK_ATTACHMENT];
					if (is_array($link_table))
						$link_table = $link_table[$data[$link_attachment]];
					if (is_array($link_label))
						$link_label = $link_label[$data[$link_attachment]];

					// Grab the first element, if no default ("") key is supplied
					if (is_array($link_table) && is_array($link_label) && $data[$link_attachment] == "") {
						if (!isset($field[LINK_TABLE][""]))
							$link_table = current($field[LINK_TABLE]);
						if (!isset($field[LINK_LABEL][""]))
							$link_label = current($field[LINK_LABEL]);
					}
					if ($option_listeners[$field[LINK_ATTACHMENT] . $suffix] == "")
						$option_listeners[$field[LINK_ATTACHMENT] . $suffix] = array();
					$option_listeners[$field[LINK_ATTACHMENT] . $suffix][$field_name . $suffix] = LINK_TABLE;	// Look to LINK_TABLE / LINK_LABEL
				}

				// TODO: Use replace_field_value() for this. TODO: This will likely not work with subtable records.
				$field_value_delimiter = "/<%[a-zA-Z_]*%>/";
				if (preg_match($field_value_delimiter, $link_where)) {
					$where_parts = preg_split("/(<%[a-zA-Z_]*%>)/", $link_where, -1, PREG_SPLIT_DELIM_CAPTURE);
					$link_where = "";
					foreach ($where_parts AS $where_element) {
						if (preg_match($field_value_delimiter, $where_element)) {
							$where_field_name = substr($where_element, 2, -2);
							$where_element = $data[$where_field_name];
						}
						$link_where .= $where_element;
					}
				}

				// $add_link = $html->a()->href("?func=form&table={$link_table}")->content(" > Add " . a_an($field_label));
				$link_table_identifier = SchemaManager::get_table_unique_identifier($link_table);
				$select_default = ($field_type == LINK) ? "< Select {$field_label} >" : "";
				$link_options = $mysql_connection->get_associative($link_table, $link_table_identifier, $link_label, $link_where, $link_limit, $link_sort, $select_default);

				if ($field_type == MULTI_LINK && $value != "") {
					$value = explode(",", $value);
				}

				$input = $html->select($link_options, $value);

				if ($field_type == MULTI_LINK) {
					$select_size = $field[SELECT_SIZE];
					if ($select_size == "")
						$select_size = 3;
					$input->multiple("")->size($select_size);
				}
				$extras_div->add($add_link);
				break;
			case MILITIME:
				$hours = $minutes =$seconds = $milis = "";
				$militime = $value;

				if ($militime) {
					$milis = $militime%1000;
					$militime = floor($militime/1000);

					if ($militime) {
						$seconds = $militime%60;
						$militime = floor($militime/60);

						if ($militime) {
							$minutes = $militime%60;
							$militime = floor($militime/60);

							$hours = $militime;
						}
					}
				}

				$hours_input = $html->text()->id("{$field_name}_hours")->size(2)->placeholder("H")->value($hours);
				$minutes_input = $html->text()->id("{$field_name}_minutes")->size(2)->placeholder("M")->value($minutes);
				$seconds_input = $html->text()->id("{$field_name}_seconds")->size(2)->placeholder("S")->value($seconds);
				$milis_input = $html->text()->id("{$field_name}_milis")->size(2)->placeholder("Ms")->value(rtrim($milis, '0'));

				$input = $html->div()->class("militime")->add($hours_input)->content(":")->add($minutes_input)
														->content(":")->add($seconds_input)->content(".")->add($milis_input);
				break;
			case LINK_N_TO_N:
			case LINK_ONE_TO_N:
			case LINK_SUBTABLE:
				break;
			case NAME:
				$input->class("name");
				break;
			case MONEY:
			case KMONEY:
			case GMONEY:
				$input->class("money");
				break;
			case PASSWORD:
			case MD5_PASSWORD:
				if ($value != "")
					$value = "_PASSWORD_";

				$input = $html->input()->type("password")->value($value);
				if ($field_type == MD5_PASSWORD) {
					$input = $html->hidden()->value($value);
					$plaintext = $html->input()->type("password");
					$plaintext->onchange("hashPassword('{$base_field_name}', '{$suffix}', hex_md5)")->value($value);
					// Set 'id' using __set so 'name' is not also set.
					$plaintext->__set("id", "{$base_field_name}_plaintext{$suffix}");
					$input_div->add($plaintext);
				}
				break;
			case RANDOM_PIN:
				// If there is no value, create a random one.
				if ($value == "") {
					// $char_bank = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
					// Remove confusing characters
					// One-case only. // abcdefghijkmnopqrstuvwxyz
					$char_bank = "ABCDEFGHJKLMNOPQRSTUVWXYZ1234567890";
					$length = $field[FIELD_MAX_LENGTH];
					if (!$length)
						$length = 10;

					$random = "";
					for ($i=0; $i<$length; $i++) {
						$bank_index = rand(0, strlen($char_bank)-1);
						$random .= $char_bank[$bank_index];
					}
					$input->value($random);
					$value_changed = 1;
				}
				break;
			case SENTENCE:
				$input->class("sentence");
				break;
			case SERVER_FILE:
				$input = $html->text()->value($value);
				$upload_container = $html->span()->id("{$field_name}_swfupload");
				$progress_container = $html->div()->id("{$field_name}_swfupload_progress");
				$upload_button = $html->button()->id("{$field_name}_swfupload_button")->value("Upload")->onclick("startSWFUpload()");
				$cancel_button = $html->button()->id("{$field_name}_swfupload_cancel_button")->value("Cancel");
				$upload_status = $html->div()->id("divStatus");
				$html->import("swf/SWFUpload/plugins/swfupload.queue.js");
				$html->import("swf/SWFUpload/plugins/swfupload.swfobject.js");
				$html->import("swf/SWFUpload/plugins/fileprogress.js");
				$html->import("swf/SWFUpload/plugins/handlers.js");
				$field_types = "*.*";
				$field_types_description = "All Files";
				if (isset($field[FIELD_EXTENSIONS]))
					$field_types = "*" . implode("; *", $field[FIELD_EXTENSIONS]);
				$session_ID = session_id();
				$swf_init_script = $html->script()->type("text/javascript")->content("var swfu;
\$(document).ready(function () {
	var settings = {
		flash_url : \"{$SETTINGS['JEKILL_ROOT']}/swf/SWFUpload/Flash/swfupload.swf\",
		upload_url: \"{$SETTINGS['JEKILL_ROOT']}/file_upload.php\",
		file_post_name : \"swf_upload\",
		post_params: {\"table_name\": \"{$table_name}\", \"field_name\": \"{$field_name}\", \"entity_ID\": \"{$entity_ID}\", \"session_ID\": \"{$session_ID}\" },
		file_size_limit : \"100 MB\",
		file_types : \"{$field_types}\",
		file_types_description : \"{$field_types_description}\",
		file_upload_limit : 100,
		file_queue_limit : 0,
		custom_settings : {
			progressTarget : \"{$field_name}_swfupload_progress\",
			cancelButtonId : \"{$field_name}_swfupload_cancel_button\"
		},
		debug: false,

		// Button settings
		button_placeholder_id: \"{$field_name}_swfupload\",
		button_width: 61,
		button_height: 22,
		button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
		button_cursor: SWFUpload.CURSOR.HAND,
	
		// The event handler functions are defined in handlers.js
		file_queued_handler: fileQueued,
		file_queue_error_handler: fileQueueError,
		file_dialog_complete_handler: fileDialogComplete,
		upload_start_handler: uploadStart,
		upload_progress_handler: uploadProgress,
		upload_error_handler: uploadError,
		upload_success_handler: uploadSuccess,
		upload_complete_handler: uploadComplete,
		queue_complete_handler: queueComplete,	// Queue plugin event

	};
	swfu = new SWFUpload(settings);
})");

				/* Check and see if the name of the directory is dependent on a field-value. */
				if ($entity_ID != "" || strpos($field[ROOT_DIRECTORY], "<%") === FALSE) {
					$html->script->add($swf_init_script);
					$extras_div->add($upload_container)->add($upload_button)->add($cancel_button)->add($progress_container)->add($upload_status);
				}
				if ($value != "") {
					$file_icon = $html->img()->src("schema/images/file_icon.gif");
					$interact_URL = $value;
					if (!is_file($_SERVER['DOCUMENT_ROOT'] . $value))
						$file_icon->src("schema/images/broken_file.gif");
					else if (endsWith($value, ".flv") || endsWith($value, ".f4v")) {
						$interact_URL = "play_video.php?video={$value}";
						$file_icon = $html->img()->src("schema/images/video_icon.gif");
					}
					$file_link = $html->a()->href($interact_URL)->add($file_icon)->target("__parent");
					$extras_div->add($file_link);
				}
				break;
			case SET:
				$select_size = $field[SELECT_SIZE];
				if ($select_size == "")
					$select_size = 5;

				$value = explode(",", $value);
				$input = $html->select($field[SET_OPTIONS], $value)->multiple("")->size($select_size);
				break;
			case SUGGEST:
				$input = $html->hidden()->value($value);
				$suggest = $html->text()->id("{$base_field_name}_suggest{$suffix}")->autocomplete("off")->onkeyup("loadLinkSuggestions('{$table_name}', '{$base_field_name}', '{$suffix}')");
				$selected = "";
				$results_div = $html->div()->style("position: absolute; width: 200px;")->class("hidden link_suggestion_results")->id($field_name . "_results");
				// $selected_div = $html->div()->id($field_name . "_selected")->class("link_suggest_value");
				if ($value) {
					if (isset($field[LINK_TABLE])) {
						$link_table_identifier = SchemaManager::get_table_unique_identifier($field[LINK_TABLE]);
						$selected = $mysql->get_row_label($mysql->get($field[LINK_TABLE], "WHERE {$link_table_identifier} = '{$value}'"), $field[LINK_LABEL]);
					}
					else $selected = $field[FIELD_OPTIONS][$value];
				}
				$suggest->value($selected);
/*
				else $selected_div->class .= " hidden";

				if ($selected) {
					$change_link = $html->a()->href("javascript: changeLinkSuggestion('{$base_field_name}', '{$suffix}')")->content("change");
					$selected_div->content("{$selected} ");
					$suggest->class("hidden");
				}
*/
				$input_div->add($suggest)->add($results_div);	// ->add($selected_div)->add($change_link);
				break;
			case TIME:
				if ($value == "")
					$value = "";
				else {
					$time = strtotime($value);
						$am_pm_value = AM;
					if (date("a", $time) == "pm")
						$am_pm_value = PM;
					$value = date("g:i", $time);
					if (date("s", $time) != "00")
						$value .= ":" . date("s", $time);
				}
				$input = $html->text()->class("time")->value($value);
				$am_pm = $html->select($AM_PM, $am_pm_value)->class("am_pm")->id("{$field_name}_am_pm")->onchange("valueChanged('{$field_name}')");
				$extras_div->prepend($am_pm);
				break;
			case TINY_TEXT:
				$input = $html->textarea()->class("tiny_copy_input")->content($value);
				break;
			case TIMESTAMP:
			case URL:
				break;
			case USER_DEFAULT:
				$link_table = $field[LINK_TABLE];
				$link_field = $field[LINK_FIELD];
				$user_field = $SCHEMA[$link_table][$link_field];
				// ($table_name, $field, $data, $suffix, &$attachment_listeners, &$option_listeners, &$rich_editors, &$optional_fields) {
				return SchemaManager::custom_input($TABLE, $link_table, $user_field, $data, $entity_ID, $suffix, $parent_table, $parent_field, $parent_record_ID, $attachment_listeners, $option_listeners, $rich_editors, $optional_fields);
			case VIDEO:
				if ($value != "") {
					/* Insert Flash video player. */
				}
				$input = $html->file();
				break;
		}
		$input->id($field_name);
		if ($field[SELECT_MULTIPLE] != "" || $field_type == MULTI_LINK || $field_type == SET)
			$input->id($field_name)->__set("name", "{$field_name}[]");

		if ($field[FIELD_STATUS] == READ_ONLY)
			$input->disabled(true);

		if ($field[FIELD_UNIQUE])
			$input->onBlur("ensureUnique('{$table_name}', '{$field_name}', '{$entity_ID}', '{$suffix}', '{$value}', '{$field[FIELD_NON_UNIQUE_CALLBACK]}')");


		/* $input->onKeyDown("testForEnter(event)");	/* Premature Submission Prevention. */
		$input->onchange("valueChanged('$field_name')");
		$input_div->add($input);

		if ($field_required == REQUIRED)
			$input_div->content("<span class=\"required_indicator\">{$REQUIRED_INDICATOR}</span>");

		$changed = $html->hidden()->id("{$field_name}_changed")->value($value_changed);
		$input_div->add($changed);

		if ($field_type == SERVER_FILE) {
			$root_directory = $field[ROOT_DIRECTORY];
			if (is_array($field[FIELD_EXTENSIONS]))
				$file_extensions = implode(",", $field[FIELD_EXTENSIONS]);
			$browse_button = $html->button()->value("Browse...")->onClick("browseFile('{$table_name}', '{$base_field_name}', '{$root_directory}', '{$file_extensions}', '{$suffix}')");
			$input_div = $html->div()->class("field_input")->add($input)->add($browse_button)->add($changed);
		}
		else if ($field_type == IMAGE) {
			$input->onchange("\$('#{$field_name}_extras').html('(Save to manipualte image.)'), valueChanged('{$field_name}')");
/* // Use browse images to select image - ISSUE: unique copy of image for each record.
			$browse_button = $html->button()->value("Browse...")->onClick("browseImages('{$table_name}', '{$base_field_name}')");
			$input_div = $html->div()->class("field_input")->add($input)->add($browse_button)->add($changed);
*/
		}
		else if ($field_type == BOOL) {
			$label_div = $html->div()->class("field_label")->content("&nbsp;");
			$input_div = $html->div()->class("field_input")->add($input)->add($field_label_label)->add($changed);
		}
		else if ($field_type == LINK_SUBTABLE || $field_type == LINK_ONE_TO_N || $field_type == LINK_N_TO_N || $field_type == LINK_MUTUAL) {

			/* Prevent infinite loop - if we are a subtable and the field references the parent table. */
			if ($suffix != "" && ($field_type == LINK_MUTUAL || ($field_type == LINK_ONE_TO_N && $field[LINK_TABLE] == $parent_table))) { // $suffix != "") { // Prevent subrecords inside of a subrecord entirely
				return $html->div();
			}
// echo "{$field[LINK_TABLE]} == {$parent_table}<br/>";

			$link_option = SchemaManager::binary_value_array($field[LINK_OPTIONS], true);
			if ($field_type == LINK_SUBTABLE) $link_option = array(LINK_FULLY_EXPANDED => 1, LINK_INLINE => 1);
			$link_table = $field[LINK_TABLE];
			$link_limit = $field[LINK_LIMIT];
			$link_sort = $field[LINK_SORT];

			$associated_hidden = "";
			if (!isset($link_option[LINK_EXPANDED]) && !isset($link_option[LINK_FULLY_EXPANDED]))
				$associated_hidden = "hidden";

			if (isset($link_option[LINK_INLINE]))
				$label_div = "";	// ->remove_all();

			$input_div->remove_all();
			$num_subtable_records = $html->hidden()->id("num_{$field_name}s");

			$subtable_div = $html->div()->class("subrecord_container {$associated_hidden}")->id("{$table_name}_{$field_name}_container")->add($num_subtable_records)->add($subtable_parent_table)->add($subtable_parent_field);
			if ($field_type == LINK_SUBTABLE) {
				$link_table_identifier = SchemaManager::get_table_unique_identifier($link_table);
				$subform_query = "SELECT * FROM {$link_table} WHERE {$link_table_identifier} = '{$value}'";
				$field[LINK_MINIMUM] = 1;
			}
			else if ($field_type == LINK_ONE_TO_N) {
				$link_field = $field[LINK_FIELD];
				if ($entity_ID != "")	// Otherwise, will grab numerous 'Orphaned' records
					$subform_query = "SELECT * FROM {$link_table} WHERE {$link_field} = '{$entity_ID}'";
			}
			else if ($field_type == LINK_N_TO_N) {
				$link_table_identifier = SchemaManager::get_table_unique_identifier($link_table);
				$map_table_name = SchemaManager::get_map_table_name($table_name, $link_table, $base_field_name);
				if ($table_name != $link_table) {
					if (isset($field[LINK_LOCAL_KEY]))
						$parent_ID_field = $field[LINK_LOCAL_KEY];
					else $parent_ID_field = "{$table_name}_ID";
					if (isset($field[LINK_FOREIGN_KEY]))
						$child_ID_field = $field[LINK_FOREIGN_KEY];
					else $child_ID_field = "{$link_table}_ID";
				}
				else {	// One-way N to N with the same table
					$parent_ID_field = "one_ID";
					$child_ID_field = "two_ID";
				}
				$subtable_fields = "{$link_table}.*";
				// TODO: NOTE: !!! This currently doesn't expand link labels and needs to be provided a simple, one-field label!
				if (isset($link_option[LINK_NO_FORM])) {
					$label_field = $field[LINK_LABEL];
					$subtable_fields = "{$link_table}.{$link_table_identifier}, {$link_table}.{$label_field}";
				}

				$subform_query = "SELECT {$map_table_name}.*, {$subtable_fields} FROM {$map_table_name} 
							LEFT JOIN {$link_table} ON ({$map_table_name}.{$child_ID_field} = {$link_table}.{$link_table_identifier}) 
							WHERE {$map_table_name}.{$parent_ID_field} = '{$entity_ID}'";
							// echo $subform_query; exit;
			}
			else {	/* LINK_MUTUAL */
				$table_identifier = SchemaManager::get_table_unique_identifier($table_name);
				$map_table_name = SchemaManager::get_map_table_name($table_name, $table_name, $field_name);
				$subform_query = "SELECT {$map_table_name}.*, {$table_name}.* FROM {$map_table_name}
							LEFT JOIN {$table_name} ON ({$map_table_name}.one_ID = {$table_name}.{$table_identifier}
										OR {$map_table_name}.two_ID = {$table_name}.{$table_identifier})
							WHERE ({$map_table_name}.one_ID = '{$entity_ID}' OR {$map_table_name}.two_ID = '{$entity_ID}')
								AND {$table_name}.{$table_identifier} != '{$entity_ID}'";
			}
			if ($link_sort != "") {
				$link_sort_asc_desc = "";
				if ($link_sort[0] == "-") {
					$link_sort_asc_desc = " DESC";
					$link_sort = substr($link_sort, 1);
				}
				$subform_query .= " ORDER BY {$link_sort}{$link_sort_asc_desc}";
			}
			else if (!isset($field[LINK_MAP_TABLE]) && $field_type != LINK_ONE_TO_N && $field_type != LINK_SUBTABLE)
				$subform_query .= " ORDER BY {$map_table_name}.record_num";

			if ($link_limit != "")
				$subform_query .= " LIMIT {$link_limit}";
//echo "$subform_query<p>";
			$subtable_results = $mysql_connection->sql($subform_query);
			$num_results = $subtable_results->num_rows;	/* $mysql_connection->get_found_rows(); */

			if ($link_limit != "")
				$num_results = min($num_results, ($link_limit + 0));
			$num_results = max($num_results, $field[LINK_MINIMUM]);

			if ($num_results > 0 && $field_type != LINK_SUBTABLE && !isset($link_option[LINK_EXPANDED]) && !isset($link_option[LINK_FULLY_EXPANDED])) {
				$show_subrecords_link = $html->a()->href("javascript: showSubrecords('{$table_name}', '{$field_name}')")->content("Show {$field_label}(s) - ({$num_results} Records)");
				$show_subrecords_link_div = $html->div()->id("show_{$table_name}_{$field_name}")->add($show_subrecords_link);
				$input_div->add($show_subrecords_link_div);
			}

			$num_subtable_records->value($num_results);

			/* NOTE: Any changes to the subrecord display here must also be mirrored in
			   schema/add_subtable_record.php. */
			$i = 1;
			while ($subtable_results->has_next()) {
				$subtable_result = $subtable_results->next();
				$subtable_record_div = SchemaManager::get_subtable_form($entity_ID, $table_name, $field /* $base_field_name */, $i, $subtable_result, $suffix);
				$subtable_div->add($subtable_record_div);
				$i++;
			}

			if (isset($field[LINK_MINIMUM])) {
				while ($field[LINK_MINIMUM] >= $i) {
					$subtable_record = array();
					// $subtable_record = $data;
					$subtable_record_div = SchemaManager::get_subtable_form($entity_ID, $table_name, $field /* $base_field_name */, $i, $subtable_record, $suffix);
					$subtable_div->add($subtable_record_div);
					$i++;
				}
			}

			if ($field_type != LINK_SUBTABLE && !isset($field[LINK_MAXIMUM]) || $i <= $field[LINK_MAXIMUM]) {
				$plus = $html->img()->src("schema/images/plus.gif");
				$add_text = isset($field[LINK_ADD_TEXT]) ? $field[LINK_ADD_TEXT] : "Add a \"" . $SCHEMA[$link_table][TABLE_LABEL] . "\" Record";
				$add_record_link = $html->a()->href("javascript: toggleAddRecordOptions('{$field_name}')")->content($add_text);
				$add_record_div = $html->div()->add($add_record_link);
				$add_new_subtable_link = $html->a()->href("javascript: addSubtableRecord('{$table_name}', '{$base_field_name}', '', '{$suffix}')")->content(" Create new record");
				$add_existing_subtable_link = $html->a()->href("javascript: loadExistingSubtableRecords('{$table_name}', '{$base_field_name}', '{$suffix}')")->content(" Add Existing Record");

				if (!isset($link_option[LINK_EXISTING_ONLY]) && !isset($link_option[LINK_NEW_ONLY])) {
					$close = $html->img()->src("schema/images/delete.gif")->onClick("toggleAddRecordOptions('{$field_name}')");
					$close_record_options_div = $html->div()->class("right")->add($close);
					$add_record_options_div = $html->div()->id("{$field_name}_add_options")->class("dropdown hidden")->add($close_record_options_div);
					$add_record_options_div->add($add_new_subtable_link)->content("<br />")->add($add_existing_subtable_link);
					$add_record_div->add($add_record_options_div);
				}

				// Only allow the association of newly created records
				else if (isset($link_option[LINK_NEW_ONLY]))
					$add_record_link->href("javascript: addSubtableRecord('{$table_name}', '{$base_field_name}', '', '{$suffix}')");

				// Only allow the association of existing records
				else if (isset($link_option[LINK_EXISTING_ONLY]))
					$add_record_link->href("javascript: loadExistingSubtableRecords('{$table_name}', '{$base_field_name}')");

				if ($link_limit != "" && $num_results > $link_limit) {
					$view_all_associated_link = $html->a()->href("")->content("View all associated {$link_table} records");
					$subtable_div->add($view_all_associated_link);
				}


				// NAL: 6/21/11 - Don't think the below needs to be omitted for LINK_NEW_ONLY|LINK_EXISTING_ONLY
				if ($field_type != LINK_MUTUAL) {
					$SCHEMA[$link_table][TABLE_NAME] = $link_table;
					// If TABLE_VALIDATION is not set (on by default), or is on.
					if (!isset($SCHEMA[$link_table][TABLE_VALIDATION]) || $SCHEMA[$link_table][TABLE_VALIDATION]) {
						// TODO: How to test if the function has already been defined?
						$validation_script = SchemaManager::validation_script($SCHEMA[$link_table], $link_table);
						$html->script->add($validation_script);
					}
				}

				//if ($label_div)
				//	$label_div->content("(s)");
			}
			$input_div->add($subtable_div);
			$input_div->add($add_record_div);
		}

		/* Javascript ops. */
		if (isset($field[FIELD_CHANGE]))
			$input->onchange .= "; " . $field[FIELD_CHANGE] . "('{$table_name}', '{$field_name}', '{$suffix}', '{$entity_ID}')";
		if (isset($field[FIELD_CLICK]))
			$input->onclick($field[FIELD_CLICK]);
		if (isset($field[FIELD_DOUBLE_CLICK]))
			$input->ondblclick($field[FIELD_DOUBLE_CLICK]);
		if (isset($field[FIELD_FOCUS]))
			$input->onfocus($field[FIELD_FOCUS]);
		if (isset($field[FIELD_BLUR]))
			$input->onblur($field[FIELD_BLUR]);
		if (isset($field[FIELD_MOUSEOVER]))
			$input->onmouseover($field[FIELD_MOUSEOVER]);
		if (isset($field[FIELD_MOUSEOUT]))
			$input->onmouseover($field[FIELD_MOUSEOUT]);
		if (isset($field[FIELD_KEYUP]))
			$input->onkeyup($field[FIELD_KEYUP]);

		if (!isset($field[FIELD_NO_LABEL]))
			$field_div->add($label_div);

		$field_div->id("{$field_name}_container")->add($input_div)->add($extras_div);

		if (!isset($field[FIELD_NO_CLEAR]))
			$field_div->add($clear_div);

		if (!isset($field[FIELD_CONFIRMATION]) && ($field_type == PASSWORD || $field_type == MD5_PASSWORD))
			$field[FIELD_CONFIRMATION] = 1;

		/* TODO: Omit test on field type. Test for FIELD_CONFIRMATION only.  Replace this with 
		   a recursive call to custom_field() */
		if ($field[FIELD_CONFIRMATION]) {
			$confirm_field = $field;
			$confirm_field[FIELD_LABEL] = "Confirm " . $field[FIELD_LABEL];
			$confirm_field[FIELD_NAME] = "confirm_{$base_field_name}";
			$confirm_field[FIELD_CONFIRMATION] = FALSE;

			$data["confirm_{$field_name}"] = $data[$field_name];

			$confirm_div = SchemaManager::custom_input($TABLE, $table_name, $confirm_field, $data, $entity_ID, $suffix, $parent_table, $parent_field, $parent_record_ID, $attachment_listeners, $option_listeners, $rich_editors, $optional_fields);
/*
			$confirm_input = $html->input()->type("password")->id("confirm_{$field_name}")->value($value);
			// $confirm_input->onKeyDown("testForEnter(event)");	// Premature Submission Prevention.
			$confirm_label_label = $html->label()->for("confirm_{$field_name}")->content("Confirm " . $field_label);
			$confirm_label_div = $html->div()->class("field_label")->add($confirm_label_label);
			$confirm_input_div = $html->div()->class("field_input")->add($confirm_input);
			if ($field_required == REQUIRED)
				$confirm_input_div->content("<span class=\"required_indicator\">{$REQUIRED_INDICATOR}</span>");
			$confirm_div = $html->div()->class("field")->add($confirm_label_div)->add($confirm_input_div)->add($clear_div);
*/
			$field_div = $html->div()->add($field_div)->add($confirm_div);
		}

		if ($field[FIELD_REQUIRED] == OPTIONAL_HIDDEN) {
			$field_div->class("field optional_hidden");
			$optional_fields++;
		}
		else if ($field[FIELD_REQUIRED] == HIDDEN)
			$field_div->class("field hidden");

		return $field_div;
	}

	// TODO: $error_page is not used.
	function subform ($table_name, $fields, $data="", $forward_page="", $error_page="") {
		global $SCHEMA;

		$table = array(TABLE_NAME => $table_name, TABLE_AUTHENTICATION => $SCHEMA[$table_name][TABLE_AUTHENTICATION]);
		foreach ($fields AS $index => $field_name) {

			if (!is_array($field_name)) {
				if (is_int($field_name) || !isset($SCHEMA[$table_name][$field_name]))
					$table[$index] = $field_name;
				else $table[$field_name] = $SCHEMA[$table_name][$field_name];
			}
			else {
				$field = $field_name;
				$field_name = $field[FIELD_NAME];
				$table[$field_name] = $field;
			}
		}
		return SchemaManager::custom_form($table, $table_name, $data, $forward_page);
	}

	function group_subform ($table_name, $group_name, $data="", $forward_page) {
		global $SCHEMA;

		$table = array(TABLE_NAME => $table_name);
		foreach ($SCHEMA[$table_name] AS $field_name => $field) {
			// if (!SchemaManager::has_permissions($field[FIELD_ACCESS])) continue;

			if (!is_array($field_name))
				$table[$field_name] = $SCHEMA[$table_name][$field_name];
			else if ($field[FIELD_GROUP] == $group_name) {
				$field[FIELD_GROUP] = "";	// Unset Group
				$table[$field_name] = $field;
			}
		}
		return SchemaManager::custom_form($table, $table_name, $data, $forward_page);
	}

	function sequential_form ($table_name, $fields, $data="", $forward_page="") {	
		global $SCHEMA, $html;

		$i = 0;
		$table = array(TABLE_NAME => $table_name, TABLE_GROUP_CONTROL => READ_ONLY);
		foreach ($fields AS $key => $field_group) {
			if (is_array($field_group)) {
				$i++;
				foreach ($field_group AS $field_name) {
					if (is_array($field_name)) {		// An explicitly defined field.
						$field = $field_name;
						$field_name = $field[FIELD_NAME];
					}
					else $field = $SCHEMA[$table_name][$field_name];	// A field name

					$field[FIELD_GROUP] = $i;
					$table[$field_name] = $field;
				}
			}
			else {
				$table[$key] = $field_group;	// A table setting.
			}
		}
		$table['_save'] = array("_save", FIELD_LABEL => "Finish", HIDDEN, FIELD_GROUP => $i);
		
		if ($i > 1) {
			$prev = array("_previous", BUTTON, "Previous", HIDDEN, FIELD_NO_CLEAR => 1, FIELD_CLICK => "previousFieldGroup('{$table_name}')", FIELD_GROUP => FIELD_FOOTER_GROUP);
			$next = array("_next", BUTTON, "Next", FIELD_NO_CLEAR => 1, FIELD_CLICK => "nextFieldGroup('{$table_name}')", FIELD_GROUP => FIELD_FOOTER_GROUP);
			$table['_prev'] = $prev;
			$table['_next'] = $next;
		}
		$table[] = $finish;

		$form = SchemaManager::custom_form($table, $table_name, $data, $forward_page);
		$form->class .= " sequential_form";
		$form_type = $html->hidden()->id("{$table_name}_form_type")->value("sequential");
		$form->add($form_type);

		return $form;
	}

	function serialize_table ($table_name, $table) {
		$serialized_schema = serialize($table);
		//$encrypted_schema = mcrypt_cbc(MCRYPT_TripleDES, "{$table_name}_serialization", $serialized_schema, MCRYPT_ENCRYPT, CRYPT_IV);
		//$serialized_schema = bin2hex($encrypted_schema);
		return $serialized_schema;
	}

	function unserialize_table ($table_name, $table_serialization) {
/*
		function hex2bin ($h) {
			if (!is_string($h)) return null;
			$r='';
			for ($a=0; $a<strlen($h); $a+=2) { $r.=chr(hexdec($h{$a}.$h{($a+1)})); }
			return $r;
  		}
*/
		// TODO: Need to keep the CRYPT_IV in $_SESSION for this to work. Any reason to encrypt if kept in $_SESSION?
		// $table_encrypted = hex2bin($table_serialization);
		// $table_serialized = mcrypt_cbc(MCRYPT_TripleDES, "{$table_name}_serialization", $table_encrypted, MCRYPT_DECRYPT, CRYPT_IV);
		// $table_schema = unserialize($table_serialized);
		$table_schema = unserialize($table_serialization);

		return $table_schema;
	}

	function encrypt_data ($data, $key) {
		$encrypted = mcrypt_ecb(MCRYPT_3DES, $key, $data, MCRYPT_ENCRYPT);
		$hex = bin2hex($encrypted);
		return $hex;
	}

	function decrypt_date ($data, $key) {
		$binary = hex2bin($data);
		$decrypted = mcrypt_ecb(MCRYPT_3DES, $key, $data, MCRYPT_DECRYPT);
		return $decrypted;
	}

	/* TODO: Separate field types into inheritance hierarchy. */
	function custom_form ($TABLE, $table_name, $data="", $forward_page="", $process_page="", $suffix="", $parent_table="", $parent_field="", $parent_record_ID="") {
		global $html, $SCHEMA, $mysql_connection, $LOGIN_ID, $MONTH_NAMES, $YEARS, $AM_PM;

		if ($data == "")
			$data = array();

		$access = $_SESSION["{$LOGIN_ID}_permissions"];
		$table_identifier = SchemaManager::get_table_unique_identifier($table_name, $suffix);
		/* Because '$data' array does not know about $suffix. */
		$table_base_identifier = SchemaManager::get_table_unique_identifier($table_name);
		$entity_ID = $data[$table_base_identifier];
		$table = $html->hidden()->id("table")->value($table_name);
		$entity_ID_input = $html->hidden()->id($table_identifier)->value($entity_ID);

		if ($suffix == "") {	/* Top level record. */
			$form = $html->form()->id($table_name . "_form")->method("POST")->autocomplete("off")/*->onsubmit("return {$table_name}Validation()")*/->enctype("multipart/form-data");	/* ->action("schema/save_entity.php"); /* (Make sure form is complete prior to submission) */
			if (isset($TABLE[RECORD_LABEL]) && $entity_ID != "") {
				$record_header = $html->div()->class("header")->content($mysql_connection->get_row_label($data, $TABLE[RECORD_LABEL]));
				$form->add($record_header);
			}
			if ($TABLE[TABLE_AUTHENTICATION]) {
				$authentication = SchemaManager::get_table_authentication($table_name, $data);
				$form->add($authentication);
			}
		}
		else $form = $html->div()->id("{$table_name}{$suffix}");

		$form->class("{$table_name}_form");

		/* Serialize the table schema and send it with the form. */
		// TODO: Don't need to serialize for subtables more than once. Don't serialize at all at the moment.
		if ($suffix == "" && isset($TABLE[TABLE_SERIALIZATION])) {
			$serialized_schema = SchemaManager::serialize_table($table_name, $TABLE);
			$_SESSION["{$table_name}_serialization"] = $serialized_schema;
			//$serialization = $html->hidden()->id("{$table_name}_serialization")->value($serialized_schema);
			// $form->add($serialization);
		}
		else unset($_SESSION["{$table_name}_serialization"]);

		if (!is_array($TABLE))
			return $form;

		$notes_div = $html->div()->class("notes")->id("{$table_name}_notes");
		$error_div = $html->div()->class("error hidden")->id("{$table_name}_error");
		if ((isset($TABLE[TABLE_SCHEMA]) && $access >= $TABLE[TABLE_SCHEMA]) || (!isset($TABLE[TABLE_SCHEMA]) && $access >= EDITOR))
			$warning_div = SchemaManager::check_schema_compatability($table_name, $entity_ID);
		$fields = $html->div()->class("form_input");

		if ($table_name != "") {
			$fields->add($entity_ID_input);

			if ($suffix == "")
				$fields->add($table);
		}

		$attachment_listeners = array();
		$option_listeners = array();
		$rich_editors = array();	// For tinyMCE instances...

		$field_groups = array();
		foreach ($TABLE AS $field) {
			if ($field[FIELD_ACCESS] > $access) {
				// For value-dependent fields: If this field has default / $_SESSION values but is hidden, set them here.
				if (!$entity_ID) {
					if ($field[FIELD_DEFAULT])
						$data[$field[FIELD_NAME]] = $field[FIELD_DEFAULT];
					else if ($field[SESSION_DEFAULT])
						$data[$field[FIELD_NAME]] = $_SESSION[$field[SESSION_DEFAULT]];
				}
				continue;
			}

			if ($field[FIELD_NAME] == "_save" || $field[FIELD_NAME] == "_delete" || $field[FIELD_NAME] == "_copy" || !is_array($field))
				continue;

			$field_div = SchemaManager::custom_input($TABLE, $table_name, $field, $data, $entity_ID, $suffix, $parent_table, $parent_field, $parent_record_ID, $attachment_listeners, $option_listeners, $rich_editors, $optional_fields);
			$field_groups[$field[FIELD_GROUP]][] = $field_div;
		}

		$group_names = array_keys($field_groups);
		if (count($group_names) > 1) {
			if ($TABLE[TABLE_GROUP_CONTROL] != HIDDEN) {
				$groups_menu = SchemaManager::get_field_group_menu($TABLE, $table_name, $group_names, $suffix);
				$fields->add($groups_menu);
			}
		}

		if (isset($field_groups[FIELD_HEADER_GROUP])) {
			$header_group = $html->div();
			foreach ($field_groups[FIELD_HEADER_GROUP] AS $field_div) {
				$header_group->add($field_div);
			}
			$fields->add($header_group);
		}

		$field_groups_div = $html->div()->class("field_groups");
		$i = 0;
		foreach ($field_groups AS $group_name => $group_fields) {
			if ($group_name == "")
				$group_name = ($TABLE[TABLE_DEFAULT_GROUP]) ? $TABLE[TABLE_DEFAULT_GROUP] : "General";
			else if ($group_name == FIELD_HEADER_GROUP || $group_name == FIELD_FOOTER_GROUP)
				continue;

			$group_ID = SchemaManager::get_field_group_ID($table_name, $group_name, $suffix);
			$group_div = $html->div()->class("field_group{$suffix}")->id($group_ID);

			foreach ($group_fields AS $field_div)
				$group_div->add($field_div);

			if ($i++ > 0)
				$group_div->class("field_group{$suffix} hidden");

			$field_groups_div->add($group_div);
		}
		$fields->add($field_groups_div);

		if (isset($field_groups[FIELD_FOOTER_GROUP])) {
			$footer_group = $html->div();
			foreach ($field_groups[FIELD_FOOTER_GROUP] AS $field_div) {
				$footer_group->add($field_div);
			}
			$fields->add($footer_group);
		}

		// Initialize tinyMCE editors, if there are any.
		if (count($rich_editors) > 0)
			SchemaManager::init_tinyMCE($rich_editors);

		if ($access >= ADMINISTRATOR && $table_name != "")
			$save_label = ($entity_ID == "") ? "Add" : "Save";
		else $save_label = "Submit";

		// TODO _save, _copy, and _delete should later use custom_input()
		$save_class = "button";
		if (isset($TABLE['_save'])) {
			if ($TABLE['_save'][FIELD_REQUIRED] == HIDDEN)
				$save_class = "button hidden";

			if (isset($TABLE['_save'][FILE_PATH]))
				$save = $html->img()->class($save_class)->src($TABLE['_save'][FILE_PATH]);
			else $save_label = $TABLE['_save'][FIELD_LABEL];
		}

		if (!isset($save))
			$save = $html->button()->value($save_label)->class($save_class);
		$save->id("_save")->onclick("{$table_name}Validation('$suffix')");

		// $save = $html->submit()->value($save_label)->class("button");
		if ($forward_page != "")
			$forward_page = $html->hidden()->id("forward_page")->value($forward_page);
		else {
			$forward_page = $html->hidden()->id("source_page")->value("{$_SERVER['PHP_SELF']}?{$_SERVER['QUERY_STRING']}");
		}

		if ($process_page != "")
			$process_page = $html->hidden()->id("process_page")->value($process_page);

		$no_label = $html->div()->class("field_label")->content("&nbsp;");
		$save_div = $html->div()->class("field_input");
		$control_div = $html->div()->class("field")->add($no_label)->add($process_page)->add($forward_page)->add($save_div);

		if ($suffix == "" && (!isset($TABLE[TABLE_CHANGE_PROMPT]) || $TABLE[TABLE_CHANGE_PROMPT]))	/* IFF this is a top-level table. */
			$save_changes_script = SchemaManager::save_changes_script($TABLE);
		$field_attachment_script = SchemaManager::field_attachment_script($TABLE, $table_name, $attachment_listeners, $suffix);
		$option_attachment_script = SchemaManager::option_attachment_script($table_name, $option_listeners, $suffix);

		// TABLE_VALIDATION: Default to on.
		if (!isset($TABLE[TABLE_VALIDATION]) || $TABLE[TABLE_VALIDATION])
			$validation_script = SchemaManager::validation_script($TABLE, $table_name, $field_groups);

		if ($optional_fields > 0) {
			$show_optional_link = $html->a()->class("clickable")->onClick("\$('.optional_hidden').slideDown('slow'); \$(this).slideUp();")->content("(Show Optional Fields)");
			$show_optional_div = $html->div()->add($show_optional_link);
			$save_div->add($show_optional_div);
		}

		$html->script->add($validation_script)->add($field_attachment_script)->add($option_attachment_script)->add($save_changes_script);
		if (!isset($TABLE['_save']) || SchemaManager::has_permissions($TABLE['_save'][FIELD_ACCESS]))
			$save_div->add($save);

		if ($entity_ID != "" && $access >= EDITOR) {
			if (!isset($TABLE[RECORD_DELETION]) || (isset($TABLE[RECORD_DELETION]) && $access >= $TABLE[RECORD_DELETION])) {
				$delete_label = "Delete";
				if (isset($TABLE['_delete'])) {
					if (isset($TABLE['_delete'][FILE_PATH]))
						$delete = $html->img()->class("button")->src($TABLE['_delete'][FILE_PATH]);
					else $delete_label = $TABLE['_delete'][FIELD_LABEL];
				}
				if (!isset($delete))
					$delete = $html->button()->class("button")->value($delete_label);
				$delete->onClick("deleteEntity('{$table_name}', {$entity_ID})");
			}
			if (!isset($TABLE[RECORD_CREATION]) || (isset($TABLE[RECORD_CREATION]) && $access >= $TABLE[RECORD_CREATION])) {
				$copy_label = "Copy";
				if (isset($TABLE['_copy'])) {
					if (isset($TABLE['_copy'][FILE_PATH]))
						$copy = $html->img()->class("button")->src($TABLE['_copy'][FILE_PATH]);
					else $copy_label = $TABLE['_copy'][FIELD_LABEL];
				}
				if (!isset($copy))
					$copy = $html->button()->class("button")->value($copy_label);
				$copy->onclick("copyEntity('{$table_name}')");
			}
			$save_div->add($delete)->add($copy);
		}
		if ($suffix == "") {	// Only add once for the top-level record.
			$date_picker_script = $html->script()->add("$(function () { $(\".date\").datepicker(); })");
			$html->script->add($date_picker_script);
		}

		$form->add($notes_div)->add($error_div)->add($warning_div);
		$form->add($fields);
		if ($suffix == "") {
			$control_div->add( $html->div()->class("clear") );
			$form->add($control_div);
		}

		return $form;
	}

	function get_field_group_ID ($table_name, $group_name, $suffix) {
		return "{$table_name}_" . preg_replace("/[^a-z|^A-Z|^0-9|^_]+/", "_", strtolower($group_name) . $suffix);
	}

	function get_field_group_menu ($TABLE, $table_name, $group_names, $suffix) {
		global $html;

		if (!isset($TABLE[TABLE_GROUP_SORT]) || $TABLE[TABLE_GROUP_SORT] != NO_SORT)
			sort($group_names);
		$group_menu = $html->div()->class("submenu");

		$i = 0;
		foreach ($group_names AS $group_name) {
			if ($group_name == "")
				$group_name = ($TABLE[TABLE_DEFAULT_GROUP]) ? $TABLE[TABLE_DEFAULT_GROUP] : "General";
			else if ($group_name == FIELD_HEADER_GROUP || $group_name == FIELD_FOOTER_GROUP)
				continue;

			$group_form_ID = SchemaManager::get_field_group_ID($table_name, $group_name, $suffix);
			if ($TABLE[TABLE_GROUP_CONTROL] != READ_ONLY)
				$group_link = $html->a()->onclick("activateFieldGroup('{$group_form_ID}', '{$suffix}')")->content($group_name);
			else $group_link = $group_name;

			$menu_class = "";
			if (!$i++)		// $group_name == "General")
				$menu_class = " menu_tab_selected";

			$group_menu_tab = $html->div()->class("menu_tab{$menu_class}")->id("{$group_form_ID}_tab")->add($group_link);
			$group_menu->add($group_menu_tab);
		}
		$group_menu->add( $html->div()->class("clear") );

		return $html->div()->class("field_group_submenu")->add($group_menu);
	}

	function get_images_preview_divide ($field_name, $serialized_array) {
		global $html;
		$images = unserialize($serialized_array);

		$preview_div = $html->div();
		$i = 0;
//echo "$serialized_array<p>";
		foreach ($images AS $image) {
			$image_URL = $image['url'];
			$caption = $image['caption'];

			$image = $html->img()->id("{$field_name}_{$i}_image")->src("preview_image.php?url=$image_URL&w=100&h=100&m=S");
			$image_link = $html->a()->href($image_URL)->target("_blank")->add($image);
			$left = $html->img()->src("schema/images/left.gif")->class("clickable")->onClick("shiftFileLeft('{$field_name}', $i)")->title("Shift Image Left");
			$right = $html->img()->src("schema/images/right.gif")->class("clickable")->onClick("shiftFileRight('{$field_name}', $i)")->title("Shift Image Right");
			$crop = $html->img()->src("schema/images/crop_resize.gif")->class("clickable")->onClick("manipulateImage('{$image_URL}')");
			$disassociate = $html->img()->src("schema/images/unlink.gif")->class("clickable")->onClick("disassociateFile('{$field_name}', $i)");
			$caption = $html->input()->id("{$field_name}_{$i}")->class("hidden")->onBlur("setCaption('{$field_name}', {$i}, this.value)")->value($caption);
			$image_control = $html->div()->id("{$field_name}_{$i}_controls")->class("hidden file_control");
			if ($i != 0)
				$image_control->add($left);
			if ($i != count($images)-1)
				$image_control->add($right);
			$image_control->add($crop)->add($disassociate);
			$image_div = $html->div()->id("{$field_name}_{$i}")->onMouseOver("\$('#{$field_name}_{$i} .hidden').stop().fadeTo('fast', 1.0)")->onMouseOut("\$('#{$field_name}_{$i} .hidden').stop().fadeTo('fast', 0.0)")->class("file_bank_file")->add($image_link)->add($image_control)->add($caption);
			$preview_div->add($image_div);
			$i++;
		}
		return $preview_div;
	}


	/* Return any '$table_one' records that are associated with '$table_two' through map records. */
	function get_associated ($table_one, $table_two, $field, $table_two_ID, $where="", $limit="", $order_by="", $fields="*") {
		global $mysql_connection;

		$map_table_name = SchemaManager::get_map_table_name($table_one, $table_two, $field);
		$table_one_identifier = SchemaManager::get_table_unique_identifier($table_one);
		$query = "SELECT {$map_table_name}.*, {$table_one}.{$fields} FROM {$map_table_name}
				LEFT JOIN {$table_one} ON ({$map_table_name}.{$table_one}_ID = {$table_one}.{$table_one_identifier})";

		$where_and = "WHERE";
		if ($where != "") {
			$query .= $where;
			$where_and = "AND";
		}
			
		$query .= " {$where_and} {$map_table_name}.{$table_two}_ID = '{$table_two_ID}'";

		if ($limit != "")
			$query .= " LIMIT {$limit}";
/* echo "$query<BR>"; */
		if ($order_by != "")
			$query .= " ORDER BY {$order_by}";

		$results = $mysql_connection->sql($query);
		$unique_identifier = SchemaManager::get_table_unique_identifier($table_one);
		$records = array();
		while ($results->has_next()) {
			$row = $results->next();
			$records[$row[$unique_identifier]] = $row;
		}
		return $records;
	}

	function get_suffix ($table_name, $field_name, $record_num, $suffix="") {
		return "{$table_name}_{$field_name}{$suffix}_{$record_num}";
	}

	// A bastardization of $mysql->get_row_label() in order to take ENUMERATION values into account.
	function get_record_label ($table_name, $data, $label) {
		global $SCHEMA;

		if (!is_array($label)) {
			$delimiters = "/([^a-zA-Z0-9_]+)/";
			if (preg_match($delimiters, $label)) {
				$label_parts = preg_split($delimiters, $label, -1, PREG_SPLIT_DELIM_CAPTURE);
				return SchemaManager::get_record_label($table_name, $data, $label_parts);
			}
			else {
				if ($SCHEMA[$table_name][$label][FIELD_TYPE] == ENUMERATION)
					return $SCHEMA[$table_name][$label][FIELD_OPTIONS][$label];
				else return SchemaManager::row_format($SCHEMA[$table_name][$label], $data);
			}
		}
		$value = "";
		foreach ($label AS $field_delim) {
// echo "$field_delim => {$data[$field_delim]}<br/>";
			if (preg_match("/[a-zA-Z0-9_]+/", $field_delim) && is_array($SCHEMA[$table_name][$field_delim]) /* isset($data[$field_delim]) */) {
				$value .= SchemaManager::row_format($SCHEMA[$table_name][$field_delim], $data);
/*
				if ($SCHEMA[$table_name][$field_delim][FIELD_TYPE] == ENUMERATION)
					$value .= $SCHEMA[$table_name][$field_delim][FIELD_OPTIONS][$data[$field_delim]];
				else $value .= $data[$field_delim];
*/
			}
			else $value .= $field_delim;
		}
		return $value;
	}

	/*
		$parent_ID:	The ID of the record that will be referring to this subrecord.
		$table_name:	The name of the table of the record that will be referring to this subrecord.
		$field_name:	The field, or field name in $SCHEMA[$table_name] that describes this LINK.
		$record_num:	The number of this subrecord 1-n (for input naming / suffix purposes).
		$subtable_data:	Data associated with this subtable record, to populate form.
	*/
	function get_subtable_form ($parent_ID, $table_name, $field, $record_num, $subtable_data, $suffix="") {
		global $html, $SCHEMA, $SETTINGS, $mysql_connection;

		if (is_array($field))
			$field_name = $field[FIELD_NAME];
		else {
			$field_name = $field;
			$field = $SCHEMA[$table_name][$field_name];
		}

		$link_table = $field[LINK_TABLE];
		$link_label = $field[LINK_LABEL];
		$field_label = $field[FIELD_LABEL];
		$link_option = SchemaManager::binary_value_array($field[LINK_OPTIONS], true);

		if ($field[FIELD_TYPE] == LINK_SUBTABLE)
			$link_option = array(LINK_FULLY_EXPANDED => 1, LINK_INLINE => 1);

		if (!isset($link_option[LINK_INLINE]))
			$row_class = ($record_num%2) ? "row_odd" : "row_even";
		else $subtable_class = "inline_subtable_record";

		$subtable_identifier = SchemaManager::get_table_unique_identifier($link_table);
		$subtable_suffix = "{$suffix}_{$field_name}_{$record_num}";	// NEW_TO_N_SUFFIX
		$subtable_ID = $subtable_data[$subtable_identifier];
		$subrecord_prefix = "{$table_name}_{$field_name}{$suffix}_{$record_num}";
		$subtable_div = $html->div()->class("row subtable_record {$subtable_class} {$row_class}")->id($subrecord_prefix);

		if ((!isset($link_option[LINK_FULLY_EXPANDED]) || isset($link_option[LINK_HEADER])) && $subtable_data[$subtable_identifier]) {
			$link_label = str_replace("<%subrecord_number%>", $record_num, $link_label);
			$subtable_result_label = SchemaManager::get_record_label($link_table, $subtable_data, $link_label);
			if ($subtable_result_label == "") {
				$subrecord_label = "No Name";
				if ($SCHEMA[$link_table][$link_label][FIELD_LABEL]) $subrecord_label = "No " . $SCHEMA[$link_table][$link_label][FIELD_LABEL] . " Specified";
				$subtable_result_label = "&lt; {$subrecord_label} &gt;";
			}
			$subrecord_label_link = $html->a()->id("{$subrecord_prefix}_link")->href("javascript: showSubrecordForm('{$table_name}', '{$field_name}', '{$suffix}', $record_num)")->style("display: block")->add($subtable_result_label);
		}
		$unlink_subrecord_button = $html->img()->id("unlink_{$subrecord_prefix}")->src("{$SETTINGS['JEKILL_ROOT']}/schema/images/unlink.gif")->class("subrecord_control clickable")->onClick("unlinkSubrecord('{$table_name}', '{$field_name}', '{$suffix}', {$parent_ID}, {$subtable_ID}, {$record_num})")->title("Disassociate this {$field_label}");

		/* Don't allow record sorting when a subrecord sort parameter is defined. */ 
		if ($field[LINK_SORT] == "") {
			$subtable_down = $html->img()->class("subrecord_control")->src("{$SETTINGS['JEKILL_ROOT']}/schema/images/down.gif")->onClick("reorderSubrecord('{$table_name}', '{$field_name}', '{$sufix}', {$record_num}, 1)");
			$subtable_up = $html->img()->class("subrecord_control")->src("{$SETTINGS['JEKILL_ROOT']}/schema/images/up.gif")->onClick("reorderSubrecord('{$table_name}', '{$field_name}', '{$suffix}', {$record_num}, -1)");
		}
		if ($field[FIELD_TYPE] == LINK_ONE_TO_N)
			$subtable_delete = $html->img()->id("delete_{$subrecord_prefix}")->class("subrecord_control")->src("{$SETTINGS['JEKILL_ROOT']}/schema/images/close.gif")->alt("Delete this {$field_label}.")->title("Delete this {$field_label}.")->onClick("deleteSubtableRecord('{$field_name}', '{$table_name}', '{$suffix}', '{$subtable_ID}', '{$record_num}')");
		$subtable_number_div = $html->div()->class("subrecord_number")->content($record_num);
		$subtable_record_header_div = $html->div()->class("subtable_record_header")->add($subtable_delete);
		// if SUBTABLE / ONE_TO_N, there is no intermediary table, so disassociation isn't an option.
		if ($field[FIELD_TYPE] != LINK_SUBTABLE && $field[FIELD_TYPE] != LINK_ONE_TO_N && !isset($link_option[LINK_DELETE_PROPEGATE]))
			$subtable_record_header_div->add($unlink_subrecord_button);
		$subtable_record_header_div->add($subtable_down)->add($subtable_up)->add($subrecord_label_link);
		$subtable_record_num = $html->hidden()->id("{$subrecord_prefix}_record_num")->class("record_num")->value($record_num);
		$subtable_deleted = $html->hidden()->id("{$subrecord_prefix}_deleted")->value(0);
		$subtable_disassociated = $html->hidden()->id("{$subrecord_prefix}_disassociated")->value(0);
		if (isset($link_option[LINK_NO_FORM])) {
			if ($subrecord_label_link)
				$subrecord_label_link->href("javascript: //");
			$subtable_identifier = SchemaManager::get_table_unique_identifier($link_table, $subtable_suffix);
			$subtable_form = $html->hidden()->id($subtable_identifier)->value($subtable_ID);
		}
		else $subtable_form = SchemaManager::form($link_table, $subtable_data, "", "", $subtable_suffix, $table_name, $field_name, $parent_ID);
		$subtable_record_div = $html->div()->id("{$subrecord_prefix}_form")->add($subtable_record_num)->add($subtable_deleted)->add($subtable_disassociated)->add($subtable_form);
		if ($subtable_ID != "" && !isset($link_option[LINK_FULLY_EXPANDED])) {
			$subtable_record_div->class("hidden");
		}
		if ($subtable_ID == "")
			$unlink_subrecord_button->class .= " hidden";

		if (!isset($link_option[LINK_INLINE]))
			$subtable_div->add($subtable_record_header_div);
		$subtable_div->add($subtable_record_div)->add( $html->div()->class("clear") );

		return $subtable_div;
	}

	function init_tinyMCE ($textarea_IDs="") {
		global $html;

		if (is_array($textarea_IDs))
			$textarea_IDs = implode(", ", $textarea_IDs);

		if ($textarea_IDs == "")
			$mode = "\"textareas\",";
		else $mode = "\"exact\", elements: \"{$textarea_IDs}\",";

		$html->import("node_modules/tinymce/tinymce.min.js"); // schema/js/tiny_mce/tiny_mce.js");
		$tinyMCE_init = $html->script()->content("tinyMCE.init({ 
	mode : {$mode}
	theme: \"modern\",
	plugins: \"paste,fullscreen,spellchecker\",
	theme_advanced_buttons1 : \"|,bold,italic,underline,|,styleselect,fontselect,fontsizeselect,forecolor,backcolor\",
	theme_advanced_buttons2 : \"cut,copy,paste,pastetext,pasteword,|,link,unlink,bullist,numlist,|,outdent,indent,|,undo,redo,|,removeformat,code,fullscreen\",
	theme_advanced_buttons3 : \"justifyleft,justifycenter,justifyright,justifyfull,|,hr,removeformat,visualaid,|,sub,sup,|,charmap\",
	content_css : \"{$SETTINGS['RICH_EDITOR_CSS']}\"
										});");
/* 											theme : \"simple\", */
		$html->script->add($tinyMCE_init);
	}

	/*	Write javascript to show / hide fields that have "field attachments" - where the visibility
		of an input is dependant on the value of another input. */
	function field_attachment_script ($TABLE, $table_name, $attachment_listeners, $suffix) {
		global $html;

		if (count($attachment_listeners) == 0)
			return;

		$initializations = array();
		$functions = "";
		// For each parent field, and each of it's child fields child fields
		foreach ($attachment_listeners AS $parent_field => $attached_fields) {
			$trigger_function = "change";
			// $initialize_script .= "\$(\"document\").ready(initialize_{$table_name}_{$parent_field}_FieldAttachments('{$suffix}'));\n\n";
			$initializations[] = "initialize_{$table_name}_{$parent_field}_FieldAttachments('{$suffix}');";
			$initialize_script .= "function initialize_{$table_name}_{$parent_field}_FieldAttachments (_suffix) {";

			$initialize_script .= "\n\t\$(\"#{$parent_field}\" + _suffix).{$trigger_function}(function () { {$table_name}_{$parent_field}NotifyListeners(_suffix) });\n\t";
			$functions .= "\n\nfunction {$table_name}_{$parent_field}NotifyListeners (_suffix) {";
			$functions .= SchemaManager::get_field_attachment_parent_value($TABLE, $parent_field);
			$defined_values = array($parent_field);

			// For each child field, and its associated value
			foreach ($attached_fields AS $child_field => $field_value) {
				// If the appearance of a field is predicated by the values of multiple parent fields.
				if (is_array($TABLE[$child_field][FIELD_ATTACHMENT])) {
					$predicates = array();
					foreach ($TABLE[$child_field][FIELD_ATTACHMENT] AS $a_parent_field => $attachment_value) {
						if (!in_array($a_parent_field, $defined_values)) {	// $a_parent_field != $parent_field)
							$functions .= SchemaManager::get_field_attachment_parent_value($TABLE, $a_parent_field);
							$defined_values[] = $a_parent_field;
						}
						// For debugging.
						// $functions .= "\nconsole.log('$a_parent_field ($a_parent_field' + _suffix + '):' + $a_parent_field);";
						$predicates[] = SchemaManager::get_field_attachment_predicate($a_parent_field, $attachment_value);
					}
					$predicate_operator = ($TABLE[$child_field][FIELD_ATTACHMENT_OPERATOR]) ? $TABLE[$child_field][FIELD_ATTACHMENT_OPERATOR] : "&&";
					$predicate = "(" . implode(") {$predicate_operator} (", $predicates) . ")";
				}
				else {	// Only dependent on one parent field value
					$predicate = SchemaManager::get_field_attachment_predicate($parent_field, $field_value);
				}
				$functions .= "\n\tif ({$predicate})\n\t\t\$(\"#{$child_field}\" + _suffix + \"_container\").removeClass(\"hidden\").show(\"slow\");\n\telse \$(\"#{$child_field}\" + _suffix + \"_container\").hide(function () { $(\"#{$child_field}\" + _suffix + \"_container\").addClass(\"hidden\").css(\"display\", \"none\"); });";
			}
			$functions .= "\n}";
			$initialize_script .= "\n}";
		}

		return $html->script()->content($functions . "\n\n" . $initialize_script . "\n\n\$(function () {\n\t" . implode("\n\t", $initializations) . "\n})");
	}

	function get_field_attachment_parent_value ($TABLE, $parent_field) {
		$value_function = "val";
		$value_function_arguments = "";
		if ($TABLE[$parent_field][FIELD_TYPE] == BOOL) {
			$trigger_function = "click";
			$value_function = "attr";
			$value_function_attributes = "\"checked\"";
		}

		$declaration = "\n\tvar {$parent_field} = \$(\"#{$parent_field}\" + _suffix).{$value_function}({$value_function_attributes});";
		if ($TABLE[$parent_field][FIELD_TYPE] == BOOL)
			$declaration .= "\n\t{$parent_field} = ({$parent_field}) ? 1 : 0;";

		return $declaration;
	}

	function get_field_attachment_predicate ($parent_field, $field_value) {
		if (!is_array($field_value))
			$field_value = array($field_value);

		$predicate = "";
		$i = 0;
		foreach ($field_value AS $possible_match) {
			if ($predicate != "")
				$predicate .= " || ";
			$comparator = "==";
			if ($possible_match[0] == '!') {
				$comparator = "!=";
				$possible_match = substr($possible_match, 1);	/* Remove '!' */
			}
			else if ($possible_match[0] == '>') {
				if ($possible_match[1] == '=') {
					$comparator = ">=";
					$possible_match = substr($possible_match, 2);	/* Remove '>' */
				}
				else {
					$comparator = ">";
					$possible_match = substr($possible_match, 1);	/* Remove '>' */
				}
			}
			else if ($possible_match[0] == '<') {
				if ($possible_match[1] == '=') {
					$comparator = "<=";
					$possible_match = substr($possible_match, 2);	/* Remove '>' */
				}
				else {
					$comparator = "<";
					$possible_match = substr($possible_match, 1);	/* Remove '>' */
				}
			}
			$predicate .= "String({$parent_field}) {$comparator} \"{$possible_match}\"";
		}
		return $predicate;
	}

	/*	Writes javascript to swap options of a select input that has "option attachments" - where the options
		that are displayed are dependant on the value of another input. */
	function option_attachment_script ($table_name, $option_listeners, $suffix) {
		global $html, $SETTINGS;

		if (count($option_listeners) == 0)
			return;

		$functions = "";
		$initialize_script = "\$(\"document\").ready(initializeOptionAttachments('{$suffix}'));\n\nfunction initializeOptionAttachments (_suffix) {";
		foreach ($option_listeners AS $parent_field => $attached_fields) {
			$initialize_script .= "\n\t\$(\"#{$parent_field}\").change(function () { {$parent_field}NotifyOptionListeners(_suffix); });";
			$functions .= "function {$parent_field}NotifyOptionListeners (_suffix) {\n\tvar value = \$(\"#{$parent_field}\" + _suffix).val();";
			foreach ($attached_fields AS $field_name => $option_attachments) {
				$functions .= "\n\tvar {$field_name} = \$(\"#{$field_name}\" + _suffix).val();";
				$functions .= "\n\t\$(\"#{$field_name}\" + _suffix).children().remove();";
				if (is_array($option_attachments)) {	// Pre-defined options
					$initialize_script .= "\n\t{$parent_field}NotifyOptionListeners(_suffix);";
					foreach ($option_attachments AS $value_attachment => $options) {
						$functions .= "\n\tif (value + \"\" == \"{$value_attachment}\") {";
						foreach ($options AS $value => $label) {
							$functions .= "\n\t\t\$(\"<option value='{$value}'>{$label}</OPTION>\").appendTo(\"#{$field_name}\" + _suffix);";
						}
						$functions .= "\n\t}";
					}
				}
				else {	// LINK_ATTACHMENT. Look up options with AJAX.
					$functions .= "\n\t\t$.post(\"{$SETTINGS['JEKILL_ROOT']}/schema/get_link_attachment_options.php\", { table_name: \"{$table_name}\", field_name: \"{$field_name}\", value: value }, function (response) {
\t\t\t$(\"#{$field_name}\").empty().append(response);
\t\t});";
				}
				$functions .= "\$(\"#{$field_name}\").val({$field_name});";
			}
			$functions .= "\n}";
		}
		$initialize_script .= "\n}";
		return $html->script()->content($functions . "\n\n" . $initialize_script);
	}

	function save_changes_script ($TABLE) {
		global $html;

		$script = "\$(\"document\").ready(function () { window.onbeforeunload = saveChanges; });";
		$script .= "\n\nfunction saveChanges () {\n\tvar saveMessage = \"You have made changes that have not been saved. If you leave this page, your changes will be lost.\";";
		foreach ($TABLE AS $field) {
			$field_name = $field[FIELD_NAME];

			$script .= "\n\tif ($(\"#{$field_name}_changed\").val() + \"\" == \"1\") return saveMessage;";
		}
		$script .= "\n}";
		return $html->script()->content($script);
	}

	/* Prior to inserting / updating: Turn all data into a form usable by the database. */
	/* $suffix: The data in this record may be a subrecord of another table. */
	function format_data ($TABLE, &$data, $suffix="") {
		global $SCHEMA, $mysql_connection, $AM_PM, $COMMON_WORDS;

/*
		$table_name = $data['table'];

		if (!isset($SCHEMA[$table_name]))
			return;
*/
		foreach ($TABLE as $field) {
			$base_field_name = $field[FIELD_NAME];
			$field_name = $base_field_name . $suffix;
			$field_type = $field[FIELD_TYPE];

			switch ($field_type) {
				case BOOL:
					$data[$field_name] = ($data[$field_name] !== 0 && ($data[$field_name] == "on" || $data[$field_name] == 1)) ? 1 : 0;
					break;
				case CREDIT_CARD:
					// Encrypt for storage here.
					// $data[$field_name] = SchemaManager::encrypt_data($data[$field_name]);
					$data[$field_name] = "XXXX-XXXX-XXXX-" . substr($data[$field_name], 12);
					break;
				case DATE:
					$field_options = $field[FIELD_OPTIONS];
					if (isset($field_options[SEPARATE_DATE_COMPONENTS])) {
						$month = $data["{$field_name}_month{$suffix}"];
						$day = $data["{$field_name}_day{$suffix}"];
						$year = $data["{$field_name}_year{$suffix}"];
						$data[$field_name] = "{$year}-{$month}-{$day}";
					}
					else if ($data[$field_name] != "" && $data[$field_name] != "0000-00-00")
						$data[$field_name] = date("Y-m-d", strtotime($data[$field_name]));					else if ($field[FIELD_REQUIRED] == GENERATED) {
						$data[$field_name] = date("Y-m-d");
						$data["{$field_name}_changed"] = 1;
					}
					else $data[$field_name] = "__NULL__";
					break;
				case DATETIME:
					if ($data[$field_name] || $field[FIELD_DEFAULT_VALUE] || $field[FIELD_REQUIRED] == GENERATED) {
						if ($field[FIELD_REQUIRED] == GENERATED)
							$data["{$field_name}_changed"] = 1;

						$field_epoch = time();
						if ($data[$field_name] != "")
							$field_epoch = strtotime($data[$field_name]);
					
						$data[$field_name] = date("Y-m-d G:i:s", $field_epoch);
					}
					else $data[$field_name] = "__NULL__";
					break;
				case ENUMERATION:
					if ($field[SELECT_MULTIPLE]) {
						/* Multiple select, up to 64 values (BIGINT is 2^64) */
						$value = 0;

						if (is_array($data[$field_name])) {
							foreach ($data[$field_name] AS $selected_value) {
								$flag = pow(2, $selected_value);	// Set bit flag
								$value = $value | $flag;		// Bitwise-OR with existing flags
							}
							$data[$field_name] = $value;
						}
					}
					break;
				case KEYWORDS:
					// If changed, don't regenerate
					if (!$data["{$field_name}_changed"]) {
						$delimiters = "([^a-zA-Z0-9_]+)";
						if (preg_match($delimiters, $field[LINK_FIELD])) {
							$dependent_fields = preg_split($delimiters, $field[LINK_FIELD]);
						}
						else $dependent_fields = array($field[LINK_FIELD]);

						$changed = false;
						foreach ($dependent_fields AS $dependent_field) {
							if ($data["{$dependent_field}{$suffix}_changed"]) {
								$changed = true;
								break;
							}
						}
						if ($changed) {
							$raw_value = $mysql_connection->get_row_label($data, $field[LINK_FIELD]);
							$reg_words = implode("|", $COMMON_WORDS);
							$raw_value = strip_tags($raw_value);							
							$raw_value = preg_replace(array("/\b({$reg_words})\b\s*/i", "([^a-zA-Z0-9_\s])"), "", $raw_value);
							// $raw_value = preg_replace("/\s\s+/", " ", $raw_value);

							$words = explode(" ", strtolower($raw_value));
							$tallied_words = array();
							foreach ($words AS $word) {
								if (array_key_exists($word, $tallied_words))
									$tallied_words[$word]++;
								else $tallied_words[$word] = 1;
							}
							arsort($tallied_words);
							$tallied_words = array_keys($tallied_words);
							if (isset($field[KEYWORD_LIMIT]))
								$tallied_words = array_slice($tallied_words, 0, $field[KEYWORD_LIMIT]);

							$data[$field_name] = implode(" ", $tallied_words);
							$data["{$field_name}_changed"] = 1;
						}
					}
					break;
				case MD5_PASSWORD:
					// This is done on client-side now.
					// $data[$field_name] = md5($data[$field_name]);
					break;
				case MULTI_LINK:
					if (is_array($data[$field_name]))
						$data[$field_name] = "," . implode(",", $data[$field_name]) . ",";
					break;
				case MILITIME:
					// Make .5 => 500 milis, and .250 be 250 milis, etc.
					$mili_exponent = 3-strlen($data["{$field_name}_milis"]);
					$data[$field_name] = $data["{$field_name}_milis"] * pow(10, $mili_exponent)
										+ $data["{$field_name}_seconds"] * 1000
										+ $data["{$field_name}_minutes"] * 60000
										+ $data["{$field_name}_hours"] * 3600000;
					break;
				case RICH_URL_ID:
					if ($data[$field_name] == "") {
						$url_values = $mysql_connection->get_row_label($data, $field[LINK_FIELD], $suffix);
						$url_values = strtolower($url_values);
						$rich_ID = url_namify($url_values);	

						$i = 1;
						do {
							$test_rich_ID = $rich_ID;
							if ($i > 1)
								$test_rich_ID .= "-{$i}";
							$count = $mysql_connection->count($table_name, $field_name, "WHERE {$field_name} = '{$test_rich_ID}'");
							if ($count == 0)
								$rich_ID = $test_rich_ID;
							$i++;
						}
						while ($count > 0);
						$data[$field_name] = $rich_ID;
					}
					break;
				case SET:
					if (is_array($data[$field_name]))
						$data[$field_name] = implode(",", $data[$field_name]);
					break;
				case TELEPHONE_NUMBER:
					$data[$field_name] = strip_nonnumeric($data[$field_name]);
					break;
				case TIME:
					if ($data[$field_name] != "") {
						$time_string = $data[$field_name] . " " . $AM_PM[$data["{$field_name}_am_pm"]];
						$data[$field_name] = date("H:i:s", strtotime($time_string));
					}
					else $data[$field_name] = "__NULL__";
					break;
				
			}
		}
	}

	// TODO: user_table, $password_field
	function login_form ($login_type="email", $login_destination="", $user_table="user", $password_field="password") {
		global $LOGO_URL, $html, $SCHEMA, $SETTINGS;

		$header_div = $html->div()->class("center");
		$error_div = $html->div()->class("error")->id("login_error");

		if ($LOGO_URL != "") {
			$logo = $html->img()->src($LOGO_URL);
			$header_div->add($logo);
		}

		$login_table = $html->table(2);
		$user_table_input = $html->hidden()->id("user_type")->value($user_table);

		if ($login_type == "email") {
			if ($login_destination == "")
				$login_destination = "index.php";
			$email = $html->text()->id("login_email")->class("login");
			$login_table->add_datum("Email")->add_datum($email);
		}
		else {
			if ($login_destination == "")
				$login_destination = "control_panel.php";
			$username = $html->text()->id("login_username")->class("login");
			$login_table->add_datum("Username")->add_datum($username);
		}

		$authentication = SchemaManager::get_table_authentication($user_table, array());
		$password = $html->input()->type("password")->class("login")->id("login_password");
		$password_function = ($SCHEMA[$user_table][$password_field][FIELD_TYPE] == MD5_PASSWORD) ? "hex_md5" : "''";
		$remain_logged_in = $html->checkbox()->id("remain_logged_in");
		$keep_logged_in = $html->div()->class("")->add($remain_logged_in)->content(" Keep me logged in unless I log out");
		$submit = $html->button()->value("Submit")->onClick("login('". $SETTINGS['JEKILL_ROOT'] . "', '{$login_destination}', {$password_function})");
		$login_table->add_datum("Password")->add_datum($password); /* ->add_datum($keep_logged_in, 2) */ 
		$login_table->add_datum("&nbsp;")->add_datum($submit);

		$login_type_input = $html->hidden()->id("login_type")->value($login_type);
		/* $login_form */
		$form_div = $html->div()->id("login_form")->add($login_type_input)->add($user_table_input)->add($authentication)->add($login_table);

		// $form_div = $html->div()->add($login_form);

		$login_div = $html->div()->class("login_frame center");
		$login_div->add($header_div)->add($error_div)->add($form_div);

		return $login_div;
	}

	function get_table_authentication ($table_name, $data="") {
		global $html;

		session_start();
		$table_identifier = SchemaManager::get_table_unique_identifier($table_name);

		// Salt with unique ID - that way, unique_ID cannot be changed by user in order to edit records
		$authentication_token = md5($data[$table_identifier] . uniqid(rand(), true));
		$_SESSION["{$table_name}_authentication"] = $authentication_token;
		$authentication = $html->hidden()->id("{$table_name}_authentication")->value($authentication_token);

		return $authentication;
	}

	function are_associated ($table_one, $table_two, $field_name, $entity_one_ID, $entity_two_ID) {
		global $mysql, $SCHEMA;

		$table_one_identifier = "{$table_one}_ID";
		$table_two_identifier = "{$table_two}_ID";
		if (isset($SCHEMA[$table_one][$field_name][LOCAL_KEY]))
			$table_one_identifier = $SCHEMA[$table_one][$field_name][LOCAL_KEY];
		if (isset($SCHEMA[$table_one][$field_name][FOREIGN_KEY]))
			$table_two_identifier = $SCHEMA[$table_one][$field_name][FOREIGN_KEY];
		$map_table = SchemaManager::get_map_table_name($table_one, $table_two, $field_name);
		$num_associations = $mysql->count($map_table, "*", "WHERE {$table_one_identifier} = '{$entity_one_ID}' AND {$table_two_identifier} = '{$entity_two_ID}'");

		return ($num_associations > 0);
	}

	function get_map_table_name ($table_one, $table_two, $field_name) {
		global $SCHEMA;

		if (isset($SCHEMA[$table_one][$field_name][LINK_MAP_TABLE]))
			return $SCHEMA[$table_one][$field_name][LINK_MAP_TABLE];
		else if (isset($SCHEMA[$table_two][$field_name][LINK_MAP_TABLE]))
			return $SCHEMA[$table_two][$field_name][LINK_MAP_TABLE];

		if ($table_one == $table_two) {
			return "{$table_one}_{$field_name}_map";
		}
		else {
			$tables = array($table_one, $table_two);
			sort($tables, SORT_STRING);
			return "{$tables[0]}_{$tables[1]}_{$field_name}_map";
		}
	}

	function authenticate_form_data ($table_name, $data) {
		global $SCHEMA;

return true;
		// If this table doesn't require authentication, return true.
		if (!isset($data["{$table_name}_authentication"]) && !isset($SCHEMA[$table_name][TABLE_AUTHENTICATION]))
			return true;
		else {	// Otherwise, make sure $_SESSION authentication 1.) Is set and 2.) Matches
			session_start();
			// echo $data["{$table_name}_authentication"] . " vs " . $_SESSION["{$table_name}_authentication"]; exit;

			return isset($_SESSION["{$table_name}_authentication"]) && ($data["{$table_name}_authentication"] == $_SESSION["{$table_name}_authentication"]);
		}
	}

	// TODO TODO TODO: Atomic check for FIELD_UNIQUE, fail update / return to form if non-unique
	/*
		$previous_suffix: For $subtable_default.
	*/
	function persist ($data, $previous_suffix="", $suffix="", $parent_table="") {
		global $SCHEMA, $mysql_connection;

		$table_name = $data['table'];
		$access = $_SESSION["{$LOGIN_ID}_permissions"];

		if ($suffix == "") {
			if (!SchemaManager::authenticate_form_data($table_name, $data)) {
				throw new Exception("The data submitted for this form could not be authenticated.");
				return;
			}
		}

		/* foreach ($data as $key => $value) echo "$key => $value<BR>"; echo $SCHEMA[$table_name]; exit; */
		if ($SCHEMA[$table_name] == "") {
			if (isset($_SESSION["{$table_name}_serialization"])) {
				$SCHEMA[$table_name] = SchemaManager::unserialize_table($table_name, $_SESSION["{$table_name}_serialization"]);
			}
			else {
				echo "Unknown table: {$table_name}";
				return 0;
			}
		}

		SchemaManager::format_data($SCHEMA[$table_name], $data, $suffix);

		$table_identifier = SchemaManager::get_table_unique_identifier($table_name, $suffix);
		$entity_ID = $data[$table_identifier];

		$new_record = 0;
		if ($entity_ID == "") {		/* Insert new row */
			$insert_query = "INSERT INTO {$table_name} () VALUES ()";
			$mysql_connection->query($insert_query);
			$entity_ID = $mysql_connection->get_insert_ID();
			$data[$table_identifier] = $entity_ID;
			$new_record = 1;
		}

		$updates = array();
		foreach ($SCHEMA[$table_name] AS $field) {
			if (!is_array($field))	/* If this is a table setting, and not an actual field. */
				continue;

			$field_name = $field[FIELD_NAME];
			$field_type = $field[FIELD_TYPE];

			if ($field_type == HTML || $field_type == JEKILL_CONTENT || $field_type == IMAGE_ANNOTATION)
				continue;

			if ($suffix != "") {
				if (/*$field_type == LINK_ONE_TO_N || */$field_type == LINK_MUTUAL)
					continue;
/*
				else if ($field_type == LINK_N_TO_N && $SCHEMA[$table_name][$field_name][LINK_TABLE] == $parent_table)
					continue;
*/
			}

			/* Intermediary "map" record must be created to link tables, if it doesn't already exist. */
			if ($field_type == LINK_SUBTABLE || $field_type == LINK_N_TO_N || $field_type == LINK_ONE_TO_N || $field_type == LINK_MUTUAL) {
				$link_table = $field[LINK_TABLE];

				/* Check to ensure this field isn't a two-way-reference. (Causing infinite recursion when attempting to save).
				   It's okay if the field is a LINK field type, because that is a more distanced association. */
				if ($suffix != "" && $SCHEMA[$link_table][$field_name][LINK_TABLE] == $table_name && $SCHEMA[$link_table][$field_name][FIELD_TYPE] != LINK)
					continue;

				$map_table_name = SchemaManager::get_map_table_name($table_name, $link_table, $field[FIELD_NAME]);

				$data['table'] = $link_table;
				/* Loop over all subrecords here: */
				$num_subrecords = max($data["num_{$field_name}{$suffix}s"], $field[LINK_MINIMUM]);

				if (isset($field[LINK_MAXIMUM]))
					$num_subrecords = min($num_subrecords, $field[LINK_MAXIMUM]);

				if ($field_type == LINK_SUBTABLE)
					$num_subrecords = 1;

				for ($i=1; $i<=$num_subrecords; $i++) {
					$subrecord_suffix = "{$suffix}_{$field_name}_{$i}";
					$subrecord_identifier = SchemaManager::get_table_unique_identifier($link_table, $subrecord_suffix);
					$subrecord_prefix = "{$table_name}_{$field_name}{$suffix}_{$i}";
					$subrecord_num = $data["{$subrecord_prefix}_record_num"];

					/* Subrecord has been deleted or disassociated. */
					if ($data["{$subrecord_prefix}_disassociated"] == 1 || $data["{$subrecord_prefix}_deleted"] == 1) {
						$subrecord_ID = $data[$subrecord_identifier];
						SchemaManager::disassociate($table_name, $link_table, $field_name, $field_type, $entity_ID, $subrecord_ID);

						/* Subrecord has been deleted */
						if ($data["{$subrecord_prefix}_deleted"] == 1) {
							SchemaManager::delete($link_table, $subrecord_ID);
						}
					}
					else {
						$link_option = SchemaManager::binary_value_array($field[LINK_OPTIONS], true);
						// If the form is not included with the record, don't save it (this can wipe generated fields, like url_ID).
						if (!isset($link_option[LINK_NO_FORM]))
							$subrecord_ID = SchemaManager::persist($data, $suffix, $subrecord_suffix, $table_name);
						// ID must be passed with data.
						else $subrecord_ID = $data[$subrecord_identifier];

						if (isset($SCHEMA[$table_name][$field_name][LINK_LOCAL_KEY]))
							$map_table_ID = $SCHEMA[$table_name][$field_name][LINK_LOCAL_KEY];
						else $map_table_ID = "{$table_name}_ID";

						if (isset($SCHEMA[$table_name][$field_name][LINK_FOREIGN_KEY]))
							$map_link_table_ID = $SCHEMA[$table_name][$field_name][LINK_FOREIGN_KEY];
						else $map_link_table_ID = "{$link_table}_ID";

						if ($table_name == $link_table) {
							$map_table_ID = "one_ID";
							$map_link_table_ID = "two_ID";
						}


						if ($data[$subrecord_identifier] == "")
							$map_count = 0;
						else if ($field_type != LINK_MUTUAL) {
							$map_count = $mysql_connection->count($map_table_name, "{$map_table_ID}", "WHERE {$map_table_ID} = '{$entity_ID}' AND {$map_link_table_ID} = '{$subrecord_ID}'");
							// echo "count({$map_table_name}, {$map_table_ID}, WHERE {$map_table_ID} = '{$entity_ID}' AND {$map_link_table_ID} = '{$subrecord_ID}'<p>";
						}
						else {	// LINK_MUTUAL
							$map_count = $mysql_connection->count($map_table_name, "{$map_table_ID}", "WHERE ({$map_table_ID} = '{$entity_ID}' AND {$map_link_table_ID} = '{$subrecord_ID}') OR ({$map_table_ID} = '{$subrecord_ID}' AND {$map_link_table_ID} = '{$entity_ID}')");
						}

						if ($map_count == 0) {
							// $map_insert_query = "INSERT INTO {$map_table_name} ({$map_table_ID}, record_num, {$map_link_table_ID}) VALUES ('{$entity_ID}', '{$subrecord_num}', '{$subrecord_ID}')";
							$map_insert_query = "INSERT INTO {$map_table_name} ({$map_table_ID}, {$map_link_table_ID}) VALUES ('{$entity_ID}', '{$subrecord_ID}')";
 //echo $map_insert_query . "<p>"; exit;
							$mysql_connection->query($map_insert_query);
						}
						/* Non-Jekill maps will fail gracefully without 'record_num' here. */
						$map_update_query = "UPDATE {$map_table_name} SET record_num = '{$subrecord_num}' WHERE {$map_table_ID} = '{$entity_ID}' AND {$map_link_table_ID} = '{$subrecord_ID}'";
						$mysql_connection->query($map_update_query);
					}
				}
				if ($field_type == LINK_SUBTABLE) {
					$data[$field_name . $suffix] = $subrecord_ID;
					$data["{$field_name}{$suffix}_changed"] = 1;
				}
				else continue;
			}

			$value = $data[$field_name . $suffix];
			if ($field_type == USER_DEFAULT) {
				if ($data[$field_name . $suffix] == "" && $access < $field[FIELD_ACCESS]) {
					$user = $mysql_connection->get("user", "WHERE user_ID = '{$_SESSION[$login_ID]}'");
					$link_field = $field[LINK_FIELD];
					$value = $data[$field_name . $suffix] = $user[$link_field];
				}
			}

			if ($field_type == FILE || $field_type == IMAGE || $field_type == VIDEO) {
				$data[$field_name . $suffix] = SchemaManager::save_uploaded_file($table_name, $field, $entity_ID, $suffix, $data);
				$value = $data[$field_name . $suffix];
			}

			if ($value == "") {
				if ($field[SESSION_DEFAULT] != "")
					$value = $_SESSION[$field[SESSION_DEFAULT]];
				else if ($suffix != "" && $field[SUBTABLE_DEFAULT] != "") {
					$subtable_default_field = $field[SUBTABLE_DEFAULT];
					/* If this is a subrecord of a subrecord (if not, $previous_suffix will be ""). */
					$subtable_default_field .= $previous_suffix;
					$value = $data[$subtable_default_field];
				}
			}

			/* If the record is new, and no value has been specified where a 
			   default value is defined, use the default value. */
			if ($new_record && $value == "" && isset($field[FIELD_DEFAULT]))
				$value = $field[FIELD_DEFAULT];

			if (!strcmp($value, "__NULL__"))
				$value = "NULL";
			else $value = "'" . $value . "'";

			if ($field_type == PASSWORD)
				$value = "password({$value})";

			/* Force save every time since tinyMCE and onChange don't play nice. */
			if ($field_type != HTML_COPY && $field_type != RICH_URL_ID) {
				/* The ID, or the field has not been changed. */
				if ($field_type == ID || ($data["{$field_name}{$suffix}_changed"] == 0 && !$new_record))
					continue;
			}

			$updates[] = "{$field_name} = {$value}";
		}
		// var_dump($updates);

		/* Nothing to update. (However, might need to update subrecords...) */
		if (count($updates) == 0) {
			/* echo "Nothing to update."; */
			return $entity_ID;
		}

		$update_clause = implode(", ", $updates);
		$table_base_identifier = SchemaManager::get_table_unique_identifier($table_name);
		$query = "UPDATE {$table_name} SET {$update_clause} WHERE {$table_base_identifier} = '{$entity_ID}'";
 //echo "{$query}<P>"; exit;
		$mysql_connection->query($query);
		if ($entity_ID == "")
			$entity_ID = $mysql_connection->get_insert_ID();

		return $entity_ID;
	}

	function result_linkbars ($page_num, $search_page="", $parameters="", $max_results="", $results_descriptor="Results", $arrows="") {
		global $mysql_connection, $html, $DEFAULT_MAX_RESULTS, $DEFAULT_MAX_PAGE_DISPLAY;

		$num_results = $mysql_connection->get_found_rows();

		if ($max_results == "")
			$max_results = $DEFAULT_MAX_RESULTS;

		if ($arrows == "")
			$arrows = array("|<<", "<<", ">>", ">>|");

		$pages_div = $html->div();
		$overview_div = $html->div()->class("search_result_overview");

		if ($num_results > $max_results) {
			$pages = array();

			$num_pages = ceil($num_results/$max_results);
			$page_start = max(1, $page_num-ceil($DEFAULT_MAX_PAGE_DISPLAY/2));
			$page_stop = min($num_pages, $page_start+$DEFAULT_MAX_PAGE_DISPLAY);

			if (1 < $page_start) {
				$prev_page = $page_num-1;
				$prev_page_link = $html->a()->href("$search_page?$parameters&page=$prev_page")->content($arrows[1]);
				$first_page_link = $html->a()->href("$search_page?$parameters&page=1")->content($arrows[0]);
				$prev_page_div = $html->div()->class("search_result_page")->add($prev_page_link);
				$first_page_div = $html->div()->class("search_result_page")->add($first_page_link);
				$pages[] = $first_page_div;
				$pages[] = $prev_page_div;
			}
			for ($i=$page_start; $i<=$page_stop; $i++) {
				if ($i == $page_num)
					$page_div = $html->div()->class("search_current_result_page")->content($i);
				else {
					$page_link = $html->a()->href("$search_page?$parameters&page=$i")->content($i);
					$page_div = $html->div()->class("search_result_page")->add($page_link);
				}
				$pages[] = $page_div;
			}
			if ($num_pages > $page_stop) {
				$next_page = $page_num+1;
				$next_page_link = $html->a()->href("$search_page?$parameters&page=$next_page")->content($arrows[2]);
				$last_page_link = $html->a()->href("$search_page?$parameters&page=$num_pages")->content($arrows[3]);
				$next_page_div = $html->div()->class("search_result_page")->add($next_page_link);
				$last_page_div = $html->div()->class("search_result_page")->add($last_page_link);
				$pages[] = $next_page_div;
				$pages[] = $last_page_div;
			}
			$display_start = (($page_num-1)*$max_results)+1;
			$display_stop = min($num_results, $page_num*$max_results);
			$overview_div->content("Displaying {$display_start} - {$display_stop} of {$num_results} {$results_descriptor}");

			$pages = array_reverse($pages);
			foreach ($pages as $page_div)
				$pages_div->add($page_div);

			$pages_div->add( $html->div()->class("clear") );
		}
		return array($pages_div, $overview_div);
	}

	/* Convert data into the form it will presented in browse rows. */
	function row_format ($field, $data) {
		global $SCHEMA, $html, $mysql_connection;
		$options = $field[FIELD_OPTIONS];
		$value = $data[$field[FIELD_NAME]];

		$MAX_PREVIEW_CHARS = 30;
		$formatted_value = $value;

		if ($field[FIELD_TYPE] == USER_DEFAULT) {
			$link_table = $field[LINK_TABLE];
			$link_field = $field[LINK_FIELD];
			$field = $SCHEMA[$link_table][$link_field];
		}

		switch ($field[FIELD_TYPE]) {
			case BOOL:
				$formatted_value = ($value == 1) ? "Yes" : "No";
				break;
			case COPY:
			case HTML_COPY:
				$formatted_value = strip_tags($formatted_value);
				break;
			case DATE:
				if ($value == "")
					$formatted_value = "";
				else $formatted_value = date("m / d / Y", strtotime($value));
				break;
			case DATETIME:
				if ($value != "")
					$formatted_value = date("g:ia M j, Y", strtotime($value));
				break;
			case ENUMERATION:
				if ($field[SELECT_MULTIPLE] != "") {
					// Split into multiple values and retrieve from $options
					$selected_values = SchemaManager::binary_value_array($value);
					$formatted_value = "";
					foreach ($selected_values AS $value) {
						if ($formatted_value != "")
							$formatted_value .= ", ";
						$formatted_value .= $field[FIELD_OPTIONS][$value];
					}
				}
				else {
					if (isset($field[OPTION_ATTACHMENT]))
						$options = $field[OPTION_ATTACHMENT_SOURCES][$data[$field[OPTION_ATTACHMENT]]];
					$formatted_value = $options[$value];
				}
				break;
			case FILE:
			case SERVER_FILE:
				return $value;
			case ID:
			case IMAGE:
			case IMAGE_EXCERPT:
				return $value;
			case LINK:
			case LINK_SUBTABLE:
				$link_table = $field[LINK_TABLE];
				$link_label = $field[LINK_LABEL];
				if ($value == "") {
					$formatted_value = "";
					break;
				}

				if ($field[LINK_ATTACHMENT] != "") {
					$attached_value = $data[$field[LINK_ATTACHMENT] . $suffix];
					$link_attachment = $field[LINK_ATTACHMENT];
					if (is_array($link_table)) {	// Link attachment may just be to change LINK_WHERE (table / label doesn't change with value)
						if ($attached_value == "" && !isset($field[LINK_TABLE][""]))
							$link_table = current($link_table);
						else $link_table = $link_table[$attached_value];
					}

					if (is_array($link_label)) {
						if ($attached_value == "" && !isset($field[LINK_LABEL][""]))
							$link_label = current($link_label);
						else $link_label = $link_label[$attached_value];
					}
				}
//echo "$link_table $link_label ($formatted_value)<br/>";
				$link_table_identifier = SchemaManager::get_table_unique_identifier($link_table);
				$associated = $mysql_connection->get($link_table, "WHERE {$link_table_identifier} = '{$formatted_value}'");	// $link_label);
// var_dump($associated);
				$formatted_value = $mysql_connection->get_row_label($associated, $link_label);
				break;
			case MILITIME:
				if ($value > 0) {
					$time = $value;
					$milis = str_pad($time%1000, 3, "0", STR_PAD_RIGHT);
					$time = floor($time/1000);
					$seconds = str_pad($time%60, 2, "0", STR_PAD_LEFT);
					$time = floor($time/60);
					$minutes = str_pad($time%60, 2, "0", STR_PAD_LEFT);
					$hours = floor($time/60);

					$hours = ($hours) ? "{$hours}:" : "";

					$formatted_value = "{$hours}{$minutes}:{$seconds}.{$milis}";
				}
				break;
			case MONEY:
			case GMONEY:
			case KMONEY:
				break;
			case SERVER_FILE:
				return $value;
			case TELEPHONE_NUMBER:
				return format_telephone($value);
			case SET:
				$values = explode(",", $value);
				$formatted_value = "";
				foreach ($values AS $value) {
					if ($formatted_value) $formatted_value .= ", ";

					$formatted_value .= $field[SET_OPTIONS][$value];
				}
				break;
			case SENTENCE:
			case VIDEO:
			default:
		}

		if (trim($formatted_value) == "")
			$formatted_value = "&nbsp;";
		else if (strlen($formatted_value) > $MAX_PREVIEW_CHARS)
			$formatted_value = substr($formatted_value, 0, $MAX_PREVIEW_CHARS) . "...";

		return $formatted_value;
	}

	function custom_row_header ($table_name) {
		global $html, $SCHEMA;

		$headers = explode(",", $SCHEMA[$table_name][RECORD_BROWSE_HEADERS]);
		$header_width = floor(100/(count($headers)+1));
		$headers_div = $html->div();
		foreach ($headers AS $header) {
			$header_div = $html->div()->add($header)->class("row_field_header")->style("width: {$header_width}%");
			$headers_div->add($header_div);
		}
		$headers_div->add( $html->div()->class("clear") );
		return $headers_div;
	}

	function row_header ($table_name, $subtable=0) {
		global $SCHEMA, $ROW_WIDTHS, $html, $CONTROL_PANEL_WIDTH;

		if (isset($SCHEMA[$table_name][RECORD_BROWSE_HEADERS]))
			return SchemaManager::custom_row_header($table_name);

		$MAX_WIDTH = $CONTROL_PANEL_WIDTH;
		$current_width = 0;
		$row_header_div = $html->div()->class("row_header");
		foreach ($SCHEMA[$table_name] AS $field) {
			if (!is_array($field))	/* If this is just a table setting, not a field. */
				continue;

			$field_name = $field[FIELD_NAME];
			$field_type = $field[FIELD_TYPE];
			$field_label = $field[FIELD_LABEL];
			$width = $ROW_WIDTHS[$field_type];

			if ($field[FIELD_TYPE] == USER_DEFAULT) {
				$link_table = $field[LINK_TABLE];
				$link_field = $field[LINK_FIELD];
				$width = $ROW_WIDTHS[$SCHEMA[$link_table][$link_field][FIELD_TYPE]];
			}

			if ($width == "")
				$width = 100;

			$access = $_SESSION["{$LOGIN_ID}_permissions"];
			if ($field[FIELD_ACCESS] > $access /* || $field[FIELD_PREVIEW] == 0 */) {
				continue;
			}
			else if ($field_type == HTML || $field_type == VIDEO || $field_type == PASSWORD || $field_type == MD5_PASSWORD || $field_type == RICH_URL_ID || $field_type == IMAGES /* || $field_type == LINK_ONE_TO_N || $field_type == LINK_N_TO_N */) {
				continue;
			}
			else if (($current_width + $width) < $MAX_WIDTH) {
				$sort = ($_REQUEST['sort'] == $field_name) ? "-" . $field_name : $field_name;
				$header_link_action = "?table={$table_name}&sort={$sort}&search_for={$_REQUEST['search_for']}&search_in={$_REQUEST['search_in']}&op={$_REQUEST['op']}";
				if ($subtable || $field_type == LINK_ONE_TO_N || $field_type == LINK_N_TO_N)
					$header_link_action = "javascript: void(0)";

				$sort_link = $html->a()->href($header_link_action)->content($field_label)->title("Sort by {$field_label}");
				$header_div = $html->div()->class("row_field_header")->add($sort_link);
				$header_div->style("width: {$width}px");
				$row_header_div->add($header_div);
				$current_width += $width;
			}
			else continue;
		}
		$row_header_div->add( $html->div()->class("clear") );

		return $row_header_div;
	}


	function row_field ($table_name, $field, $data, $row_class) {
		global $html, $SCHEMA, $ROW_WIDTHS;

		$field_name = $field[FIELD_NAME];
		$field_type = $field[FIELD_TYPE];
		$width = $ROW_WIDTHS[$field_type];
		$entity_identifier = SchemaManager::get_table_unique_identifier($table_name);
		$entity_ID = $data[$entity_identifier];

		$row_content = SchemaManager::row_format($field, $data);
		$field_div = $html->div()->id("{$table_name}_{$entity_ID}_{$field_name}")->class("row_field $row_class")->style("width: {$ROW_WIDTHS[$field_type]}px;");

		/* Field Alignment. */
		switch ($field_type) {
			case BOOL:
				/* $field_div->class($field_div->class . " center"); */
		}

		switch ($field_type) {
			case COLOR:
				$color_div = $html->div()->style("background-color: #{$data[$field_name]}; height: 15px; width: 15px");
				$field_div->add($color_div);
				break;
			case JEKILL_CONTENT:
				$content_field = $field[LINK_FIELD];
				$edit_content_link = $html->a()->href("jekill.php?jtable={$table_name}&jfield={$content_field}&jid={$entity_ID}&jinclude={$field_name}")->content("Edit Page Content");
				$field_div->add($edit_content_link);
				break;
			case FILE:
			case SERVER_FILE:
				if ($row_content != "" && $row_content != "&nbsp;") {
					$file_URL = $row_content;
					$file_icon = "schema/images/file_icon.gif";
					if (!is_file($_SERVER['DOCUMENT_ROOT'] . $file_URL))
						$file_icon = "schema/images/broken_file.gif";
					else if (endsWith($row_content, ".flv") || endsWith($row_content, ".f4v")) {
						$file_icon = "schema/images/video_icon.gif";
						$file_URL = "play_video.php?video={$file_URL}";
					}
					$image = $html->img()->class("clickable")->src($file_icon);
					$image_link = $html->a()->href($file_URL)->add($image)->target("__parent");
					$field_div->add($image_link);
				}
				else $field_div->content(" - ");
				break;
			case IMAGE:
			case IMAGE_EXCERPT:
				if ($row_content != "" && $row_content != "&nbsp;") {
					$image = $html->img()->class("clickable")->src("schema/images/image_icon.jpg");
					$image->onMouseOver("imagePreview(event, '{$row_content}')");
					$image->onMouseOut("closeImagePreview()");
					$image_link = $html->a()->href($row_content)->add($image);
					$field_div->add($image_link);
				}
				else $field_div->content("<I>-</I>");
				break;
			case MILITIME:
				if ($row_content > 0) {
					$time = $row_content;
					$milis = str_pad($time%1000, 3, "0", STR_PAD_RIGHT);
					$time = floor($time/1000);
					$seconds = str_pad($time%60, 2, "0", STR_PAD_LEFT);
					$time = floor($time/60);
					$minutes = str_pad($time%60, 2, "0", STR_PAD_LEFT);
					$hours = floor($time/60);

					$hours = ($hours) ? "{$hours}:" : "";

					$field_div->content("{$hours}{$minutes}:{$seconds}.{$milis}");
				}
				break;
			case USER_DEFAULT:
				$link_table = $field[LINK_TABLE];
				$link_field = $field[LINK_FIELD];
				return SchemaManager::row_field($link_table, $SCHEMA[$link_table][$link_field], $data, $row_class);
			case LINK_N_TO_N:
			case LINK_ONE_TO_N:
				$load_subrecords = $html->img()->class("clickable")->id("{$field_name}_{$entity_ID}_subrecords")->src("schema/images/down.gif")->onclick("loadAssociatedRecords('{$table_name}', '{$field_name}', {$entity_ID})");
				$field_div->add($load_subrecords);
				break;
			case IMAGES:
			case PASSWORD:
			case MD5_PASSWORD:
			case RICH_URL_ID;
			case VIDEO:
				continue;
				default:
				$field_div->content($row_content);
				break;
		}
		return $field_div;
	}

	function row ($table_name, $data, $row_class="") {
		global $SCHEMA, $ROW_WIDTHS, $html, $CONTROL_PANEL_WIDTH;

		$MAX_WIDTH = $CONTROL_PANEL_WIDTH-10;
		$table_identifier = SchemaManager::get_table_unique_identifier($table_name);
		$entity_ID = $data[$table_identifier];
		$current_width = 0;
		$row_ID = "{$table_name}_{$entity_ID}_{$row}";
		$row_div = $html->div()->class("row {$row_class}")->id($row_ID)->onDblClick("redirect('?func=form&table={$table_name}&id={$entity_ID}')");

		$access = $_SESSION["{$LOGIN_ID}_permissions"];
		$fields = $SCHEMA[$table_name];

		if (isset($SCHEMA[$table_name][RECORD_BROWSE_FIELDS])) {
			$fields = explode(",", $SCHEMA[$table_name][RECORD_BROWSE_FIELDS]);
			$field_width = floor(100/(count($fields)+1));
		}
		
		foreach ($fields AS $field) {
			if (is_array($field)) {
				$field_name = $field[FIELD_NAME];
				$field_type = $field[FIELD_TYPE];
				$width = $ROW_WIDTHS[$field_type];

				if ($field[FIELD_ACCESS] > $access || $field_type == HTML) {
					continue;
				}

				$field_div = SchemaManager::row_field($table_name, $field, $data, $row_class);

				if ($field[FIELD_ATTACHMENT] && !SchemaManager::is_field_attached($SCHEMA[$table_name], $table_name, $field, $data))
					$field_div->remove_all();

				if (($current_width + $ROW_WIDTHS[$field_type]) < $MAX_WIDTH) {
					$row_div->add($field_div);
					$current_width += $ROW_WIDTHS[$field_type];
				}
				else continue;	/* Try succeeding columns if the current is too large to fit. */
			}
/*	NAL: 4/15/11 - For custom rows, but causes table attributes to show up in normal rows.
			else {
				$field_name = trim($field);
				$field_div = $html->div()->class("row_field {$row_class}")->style("width: {$field_width}%")->content($data[$field_name]);
				$row_div->add($field_div);
			}
*/
		}
		$edit_button = $html->img()->src("schema/images/edit.gif")->title("Edit this Record");
		$delete_button = $html->img()->src("schema/images/delete.gif")->title("Delete this Record");
		$edit_record = $html->a()->href("?func=form&table={$table_name}&id={$entity_ID}")->add($edit_button);
		$delete_record = $html->a()->href("javascript: deleteEntity('{$table_name}', '{$entity_ID}', '{$row_ID}')")->add($delete_button);
		$control_div = $html->div()->id("control_{$entity_ID}")->class("row_control transparent")->add($edit_record)->content("&nbsp;")->add($delete_record);
		$row_div->add($control_div);
		$subrecord_div = $html->div()->id("{$table_name}_{$entity_ID}_subrecords")->class("hidden");	// ->class("subrecord_rows");
		$row_div->add( $html->div()->class("clear") )->add($subrecord_div)->onMouseOver("$('#control_{$entity_ID}').stop().fadeTo('fast', 1.0)")->onMouseOut("$('#control_{$entity_ID}').stop().fadeTo('fast', 0.0)");

		return $row_div;
	}

	function delete_uploaded_file ($table_name, $field_name, $entity_ID, $filename) {
		global $mysql_connection, $SCHEMA;

		$record = array();
		$table_identifier = SchemaManager::get_table_unique_identifier($table_name);
		if (isset($SCHEMA[$table_name][$field_name][FILE_PATH])) {
			$record = $mysql_connection->get($table_name, "WHERE {$table_identifier} = '{$entity_ID}'");
		}

		$uploaded_filename = SchemaManager::get_uploaded_filename($table_name, $field_name, $entity_ID, $filename, $data);

		$query = "UPDATE {$table_name} SET {$field_name} = '' WHERE {$table_identifier} = '{$entity_ID}'";
		$mysql_connection->query($query);
		unlink("{$_SERVER['DOCUMENT_ROOT']}/" . $uploaded_filename);	// . $filename);	
	}

	function replace_field_value ($string, $data) {
		// ([a-zA-Z]+\()*   ... (\))* Field name can be surrounded by 1 function name.
		// Try to include multiple functions later.
		$field_pattern = "/([a-zA-Z]+\()*<%[a-zA-Z_0-9]*%>(\))*/";
		while (preg_match($field_pattern, $string, $matches)) {
			foreach ($matches AS $match) {
				// If this is a sub-pattern.
				if (!preg_match($field_pattern, $match))
					continue;

				if (preg_match("/[a-zA-Z]*\(.*\)/", $match)) {
					$field_index = strpos($match, "(");
					$function_name = substr($match, 0, $field_index);
					$field_name = substr($match, $field_index+3, -3);	// +3: '(<%' -3: '%>)'
					$field_value = $function_name($data[$field_name]);
					// Escape RegEx special characters for preg_replace, below
					$match = str_replace(array("(", ")"), array("\\(", "\\)"), $match);
				}
				else {
					$field_name = substr($match, 2, -2);	// strip '<%' and '%>'
					$field_value = $data[$field_name];
				}
				$string = preg_replace("/{$match}/", $field_value, $string);
			}
		}
		return $string;
	}

	function create_thumbnails ($table_name, $field_name, $filename, $data) {
		global $SCHEMA;

		if (isset($SCHEMA[$table_name][$field_name][IMAGE_THUMBNAIL])) {
// $handle = fopen("here.txt", "w+");

			$thumbnail_specs = $SCHEMA[$table_name][$field_name][IMAGE_THUMBNAIL];
			if (!is_array(current($thumbnail_specs)))
				$thumbnail_spects = array($thumbnail_specs);

			$base_filename = basename($filename);
			$dot_index = strrpos($base_filename, ".");
			$base_filename = substr($base_filename, 0, $dot_index-1);
			$base_filename_dir = dirname($filename);
			$extension = substr($filename, $dot_index);
			foreach ($thumbnail_specs AS $thumb_spec) {
				$thumb_filename = SchemaManager::replace_field_value($thumb_spec[FILE_PATH], $data);
				$thumb_filename = str_replace(array("[BASE_FILE_NAME]", "[BASE_FILE_NAME_EXTENSION]", "[BASE_FILE_NAME_DIRECTORY]"), array($base_filename, $extension, $base_filename_dir), $thumb_filename);
				$thumb_filename = "{$_SERVER['DOCUMENT_ROOT']}{$thumb_filename}";
				// fwrite($handle, "\n\r{$thumb_filename}");
				$zoom = isset($thumb_spec[IMAGE_ZOOM]) ? $thumb_spec[IMAGE_ZOOM] : FIT;
				$background_color = ($thumb_spec[IMAGE_BACKGROUND_COLOR]) ? $thumb_spec[IMAGE_BACKGROUND_COLOR] : "-000000";
				$quality = ($thumb_spec[IMAGE_QUALITY]) ? $thumb_spec[IMAGE_QUALITY] : 100;
				$x = ($thumb_spec[IMAGE_X]) ? $thumb_spec[IMAGE_X] : 0;
				$y = ($thumb_spec[IMAGE_Y]) ? $thumb_spec[IMAGE_Y] : 0;
				$align = ($thumb_spec[IMAGE_ALIGN]) ? $thumb_spec[IMAGE_ALIGN] : " ";
				image_excerpt($filename, $thumb_spec[IMAGE_WIDTH], $thumb_spec[IMAGE_HEIGHT], $zoom, $background_color, $thumb_filename, $quality, $x, $y, $align);
			}
// fclose($handle);
		}
	}

	function get_uploaded_filename ($table_name, $field_name, $entity_ID, $original_filename, $data="") {
		global $SCHEMA;

		if (!is_dir("{$_SERVER['DOCUMENT_ROOT']}/images/schema"))
			mkdir("{$_SERVER['DOCUMENT_ROOT']}/images/schema");

		if (!is_dir("{$_SERVER['DOCUMENT_ROOT']}/images/schema/{$table_name}"))
			mkdir("{$_SERVER['DOCUMENT_ROOT']}/images/schema/{$table_name}");

		if (isset($SCHEMA[$table_name][$field_name][FILE_PATH])) {
			$file_path = SchemaManager::replace_field_value($SCHEMA[$table_name][$field_name][FILE_PATH], $data);
		}
		else {
			$filename_suffix = ($entity_ID == "") ? "" : "_{$entity_ID}";
			$file_path = "/images/schema/{$table_name}/{$field_name}{$filename_suffix}";
		}

		$extension = strtolower(strrchr($original_filename, "."));
		$filename = "{$file_path}{$extension}";

		return $filename;
	}

	function save_uploaded_file ($table_name, $field, $entity_ID="", $suffix="", $data="") {
		$field_name = $field[FIELD_NAME];
		$original_filename = $_FILES["{$field_name}{$suffix}"]['name'];
		$temp_filename = $_FILES["{$field_name}{$suffix}"]['tmp_name'];

		/* If no file uploaded. */
		if ($original_filename == "")
			return "";

		$filename = SchemaManager::get_uploaded_filename($table_name, $field_name, $entity_ID, $original_filename, $data);
		if (is_file($filename))
			unlink($filename);

		copy($temp_filename, "{$_SERVER['DOCUMENT_ROOT']}/$filename");
		chmod("{$_SERVER['DOCUMENT_ROOT']}/{$filename}", 0777);

		if ($field[FIELD_TYPE] == IMAGE) {
			if (isset($field[IMAGE_AUTO_RESIZE]) && (isset($field[IMAGE_MAX_WIDTH]) || isset($field[IMAGE_MAX_HEIGHT]))) {
				$image_URL = "{$_SERVER['DOCUMENT_ROOT']}/{$filename}";
				$image_size = getimagesize($image_URL);
			
				/* If the image is horizontally oriented. TODO: If both are over spec, see which one is more over spec. */
				if ($image_size[0] > $image_size[1]) {
					/* If width is greater than defined max width. */
					if (isset($field[IMAGE_MAX_WIDTH]) && $image_size[0] > $field[IMAGE_MAX_WIDTH]) {
					resize_image_uniform($image_URL, $field[IMAGE_MAX_WIDTH]);
					}
				}
				else if (isset($field[IMAGE_MAX_HEIGHT]) && $image_size[1] > $field[IMAGE_MAX_HEIGHT]) {
					resize_image_uniform($image_URL, "", $field[IMAGE_MAX_HEIGHT]);
				}
			}
			if (isset($field[IMAGE_THUMBNAIL]))
				SchemaManager::create_thumbnails($table_name, $field_name, $filename, $data);
		}

		return $filename;
	}

	function validation_script ($TABLE, $table_name, $field_groups="") {
		global $DEFAULT_VALIDATION, $SETTINGS, $html;

		$access = $_SESSION["{$LOGIN_ID}_permissions"];
		// $table_name = $TABLE[TABLE_NAME];
		$attached = array();	// Non required fields whose values must be obtained for field attachment.
		$draft = isset($TABLE['visible']) ? "!\$(\"#visible\").attr(\"checked\")" : "false";
		$num_field_groups = 0;
		if ($field_groups) {
			unset($field_groups[FIELD_HEADER_GROUP]);
			unset($field_groups[FIELD_FOOTER_GROUP]);
			$num_field_groups = count($field_groups)-1;
			$field_group_names = array_keys($field_groups);
//foreach ($field_group_names AS $key => $val)
//echo "$key => $val<br/>";
		}

		$table_processor = isset($TABLE[TABLE_PROCESSOR]) ? $TABLE[TABLE_PROCESSOR] : "{$SETTINGS['JEKILL_ROOT']}/schema/save_entity.php";
		$validation_header = "\nfunction {$table_name}Validation (_suffix) {";
		$validation_script = "\n";
		$validation_vars = "\n\tvar errors = new Array();\n\tvar draft = {$draft};\n\tvar errorFieldGroup = {$num_field_groups};";

		if ($TABLE[TABLE_SUBMISSION] == AJAX_SUBMISSION) {
			$ajax_fields = array("table: \"{$table_name}\"", "_submission_method: \"ajax\"");
			if ($TABLE[TABLE_AUTHENTICATION]) {
				$validation_vars .= "\n\tvar table_authentication = $(\"#{$table_name}_authentication\").val();";
				$ajax_fields[] = "{$table_name}_authentication: table_authentication";
			}
		}

		foreach ($TABLE AS $field) {
			if (!is_array($field))	/* If the field is a table setting, not a proper field */
				continue;

			$value_function = "val";
			$value_function_arguments = "";
			$field_selector = "#{$field[FIELD_NAME]}";
			$field_selector_suffix = "";
			$field_name = $field[FIELD_NAME];
			$field_type = $field[FIELD_TYPE];
			$field_label = $field[FIELD_LABEL];
			$required = $field[FIELD_REQUIRED];
			$field_options = $field[FIELD_OPTIONS];
			$validation_function = $field[FIELD_VALIDATION];
			$validation_arguments = $field_name;
			$field_attachment = $field[FIELD_ATTACHMENT];
			$field_attachment_value = $field[FIELD_ATTACHMENT_VALUE];

			if ($field_type == HTML)
				continue;

			/* Set default non-empty validation if not specified. */
			if ($required == REQUIRED && ($validation_function == ""))
				$validation_function = $DEFAULT_VALIDATION[$field_type];

			$field_attachment_condition = "";
			if ($field_attachment) {
				if (!is_array($field_attachment))
					$field_attachment = array($field_attachment => $field_attachment_value);

				$predicates = array();
				foreach ($field_attachment AS $parent_field => $target_value) {
					// If the parent field is not included in this perspective of the table
					if (!isset($TABLE[$parent_field]))
						continue;

					/* If relied upon field is not required, capture it's value in Javascript here. */
					if ($TABLE[$parent_field][FIELD_REQUIRED] == NOT_REQUIRED && !isset($attached[$parent_field])) {
						$attached[$parent_field] = 1;
						// If AJAX_SUBMISSION, we'll already have this.
						if ($TABLE[TABLE_SUBMISSION] != AJAX_SUBMISSION) {
							$validation_vars .= SchemaManager::get_field_attachment_parent_value($TABLE, $parent_field);
							// $validation_vars .= "\n\tvar {$parent_field} = \$(\"#{$parent_field}\" + _suffix).val();";
						}
					}
					$predicates[] = SchemaManager::get_field_attachment_predicate($parent_field, $target_value);
				}

				if (count($predicates)) {
					$attachment_operator = ($field[FIELD_ATTACHMENT_OPERATOR]) ? $field[FIELD_ATTACHMENT_OPERATOR] : "&&";
					$field_attachment_condition = " && ((" . implode(") {$attachment_operator} (", $predicates) . "))";
				}

/*
				$field_attachment_comparator = "==";
				if ($field_attachment_value[0] == "!") {
					$field_attachment_comparator = "!=";
					$field_attachment_value = substr($field_attachment_value, 1);
				}
				else if ($field_attachment_value[0] == ">") {
					if ($field_attachment_value[1] == "=") {
						$field_attachment_comparator = ">=";
						$field_attachment_value = substr($field_attachment_value, 2);
					}
					else {
						$field_attachment_comparator = ">";
						$field_attachment_value = substr($field_attachment_value, 1);
					}
				}
				else if ($field_attachment_value[0] == "<") {
					if ($field_attachment_value[1] == "=") {
						$field_attachment_comparator = "<=";
						$field_attachment_value = substr($field_attachment_value, 2);
					}
					else {
						$field_attachment_comparator = "<";
						$field_attachment_value = substr($field_attachment_value, 1);
					}
				}
				$field_attachment_condition = " && (String({$field_attachment}) {$field_attachment_comparator} \"{$field_attachment_value}\")";
*/
			}

			if ($field_type == ENUMERATION && $field[SELECT_MULTIPLE] || $field_type == SET) {
				$field_selector_suffix = " option:selected";
				$value_function = "size";
			}

			//if ($field_type == SET) {
			//	$field_selector_suffix = "[] " . $field_selector_suffix;
			//}

			if ($field_type == ENUMERATION && $field[FIELD_VARIATION] == RADIO) {
				// TODO: / NOTE: This will currently not work in a subtable!
				$field_selector = "[name='{$field_name}']";
				$field_selector_suffix = ":checked";
			}

			if ($field_type == BOOL) {
				$value_function = "attr";
				$value_function_arguments = "\"checked\"";
			}
			else if ($field_type == PASSWORD || $field_type == MD5_PASSWORD)
				$validation_arguments = "'{$field_name}'";
			else if ($field_type == HTML_COPY) {
				if ($required != REQUIRED)
					$validation_vars .= "\n\tvar {$field_name} = tinyMCE.get(\"{$field_name}\" + _suffix + \"_editor\").getContent();";
				$validation_script .= "\n\t\$(\"{$field_selector}\").val({$field_name});";
			}

			if (($required != REQUIRED || $field[FIELD_ACCESS] > $access) && $TABLE[TABLE_SUBMISSION] != AJAX_SUBMISSION) {
				continue;
			}
			if ($field_type == DATE && isset($field_options[SEPARATE_DATE_COMPONENTS])) {
				$validation_vars .= "\n\tvar {$field_name}Month = \$(\"#{$field_name}_month\" + _suffix).val();";
				$validation_vars .= "\n\tvar {$field_name}Day = \$(\"#{$field_name}_day\" + _suffix).val();";
				$validation_vars .= "\n\tvar {$field_name}Year = \$(\"#{$field_name}_year\" + _suffix).val();";

				/* Month */
				$validation_script .= "\n\tif (!isNonZero({$field_name}Month)) {";
				$validation_script .= "\n\t\terrors['{$field_name}_month'] = \"Please enter a valid {$field_label} month.\";";
				$validation_script .= "\n\t\terrors.push(\"Please enter a valid {$field_label} month.\");";
				$validation_script .= "\n\t\t\$(\"#{$field_name}_month\" + _suffix).addClass(\"error_input\");";
				$validation_script .= "\n\t}";
				$validation_script .= "\n\telse \$(\"#{$field_name}_month\" + _suffix).removeClass(\"error_input\");";

				/* Day */
				$validation_script .= "\n\tif (!isNonZero({$field_name}Day)) {";
				// $validation_script .= "\n\t\terrors['{$field_name}_day'] = \"Please enter a valid {$field_label} day.\";";
				$validation_script .= "\n\t\terrors.push(\"Please enter a valid {$field_label} day.\");";
				$validation_script .= "\n\t\t\$(\"#{$field_name}_day\" + _suffix).addClass(\"error_input\");";
				$validation_script .= "\n\t}";
				$validation_script .= "\n\telse \$(\"#{$field_name}_day\" + _suffix).removeClass(\"error_input\");";

				/* Year */
				$validation_script .= "\n\tif (!isNonZero({$field_name}Year)) {";
				// $validation_script .= "\n\t\terrors['{$field_name}'] = \"Please enter a valid {$field_label} year.\";";
				$validation_script .= "\n\t\terrors.push(\"Please enter a valid {$field_label} year.\");";
				$validation_script .= "\n\t\t\$(\"#{$field_name}_year\" + _suffix).addClass(\"error_input\");";
				$validation_script .= "\n\t}";
				$validation_script .= "\n\telse \$(\"#{$field_name}_year\" + _suffix).removeClass(\"error_input\");";

				if ($TABLE[TABLE_SUBMISSION] == AJAX_SUBMISSION) {
					$ajax_fields[] = "{$field_name}_month: {$field_name}_month";
					$ajax_fields[] = "{$field_name}_day: {$field_name}_day";
					$ajax_fields[] = "{$field_name}_year: {$field_name}_year";
				}
				continue;
			}
			else {
				$predicate_supplement = "";
				$field_label = $field[FIELD_LABEL];

				if ($TABLE[TABLE_SUBMISSION] == AJAX_SUBMISSION)
					$ajax_fields[] = "{$field_name}: {$field_name}";

				if ($field_type == HTML_COPY) {
					$validation_vars .= "\n\tvar {$field_name} = tinyMCE.get(\"{$field_name}\" + _suffix + \"_editor\").getContent();";
				}
				else if ($field_type == LINK_N_TO_N || $field_type == LINK_ONE_TO_N || $field_type == LINK_SUBTABLE) {
					// TODO: N_TO_N / ONE_TO_N will not work with AJAX_SUBMISSION
					// TODO: Will currently not work with LINK_ATTACHMENT
					$link_table = $TABLE[$field_name][LINK_TABLE];
					$validation_vars .= "\n\tvar num_{$field_name}s = $(\"#num_{$field_name}\" + _suffix + \"s\").val();";
//					$validation_script .= "\n\terrors['{$field_name}'] = \"\";";
					$validation_script .= "\n\tfor (i=1; i<=num_{$field_name}s; i++) {";
					$validation_script .= "\n\t\tvar fieldSuffix = _suffix + \"_{$field_name}_\" + i;";
					$validation_script .= "\n\t\tvar disassociated = parseInt(\$(\"#{$table_name}_{$field_name}_\" + i + \"_disassociated\").val());";
					$validation_script .= "\n\t\tvar deleted = parseInt(\$(\"#{$table_name}_{$field_name}_\" + i + \"_deleted\").val());";
					$validation_script .= "\n\t\tif (!deleted && !disassociated) {";
					$validation_script .= "\n\t\t\tvar subtableErrors = {$link_table}Validation(fieldSuffix);";
					$validation_script .= "\n\t\t\terrors = errors.concat(subtableErrors);";
					$validation_script .= "\n\t\t}";
					$validation_script .= "\n\t}";
				}
				else {
					$validation_vars .= "\n\tvar {$field_name} = \$(\"{$field_selector}\" + _suffix";
					if ($field_selector_suffix != "")
						$validation_vars .= " + \"{$field_selector_suffix}\"";
					$validation_vars .= ").{$value_function}({$value_function_arguments});";
				}

				if ($field_type != LINK_N_TO_N && $field_type != LINK_ONE_TO_N && $field_type != LINK_SUBTABLE) {
					if ($field_type == CREDIT_CARD) {
						$predicate_supplement = " && !{$field_name}.match(/[XXXX-]{3}[0-9]{3,4}/)";
					}
					$subtable_predicate = ($field[SUBTABLE_DEFAULT]) ? "(_suffix == \"\") && " : "";
					$validation_script .= "\n\tif ({$subtable_predicate}!{$validation_function}({$validation_arguments}){$field_attachment_condition}{$predicate_supplement}) {";

					if ($field[FIELD_ERROR_MESSAGE] != "")
						$error_message = $field[FIELD_ERROR_MESSAGE];
					else if ($field_type == PASSWORD || $field_type == MD5_PASSWORD) {
						if ($field[FIELD_CONFIRMATION] !== false)
							$error_message = "The {$field_label} you entered is either invalid or does not match.";
						else $error_message = "The {$field_label} you enteres is invalid.";
					}
					else $error_message = "Please enter a valid {$field_label}.";
					$error_message = str_replace("\"", "\\\"", $error_message);
					// $validation_script .= "\n\t\terrors['{$field_name}'] = \"{$error_message}\";";
					$validation_script .= "\n\t\terrors.push(\"{$error_message}\");";

					// $error_field_name: The ID of the element that is actually displayed
					$error_field_name = $field_name;
					if ($field_type == MD5_PASSWORD)
						$error_field_name = "{$field_name}_plaintext";
					else if ($field_type == SUGGEST)
						$error_field_name = "{$field_name}\" + \"_suggest";

					$validation_script .= "\n\t\t\$(\"#{$error_field_name}\" + _suffix).addClass(\"error_input\");";

					if ($field[FIELD_CONFIRMATION] !== false && ($field_type == PASSWORD || $field_type == MD5_PASSWORD))
						$validation_script .= "\n\t\t\$(\"#confirm_{$error_field_name}\" + _suffix).addClass(\"error_input\");";
					if ($field_groups) {
						$field_group_index = array_search($field[FIELD_GROUP], $field_group_names);
						$validation_script .= "\n\t\terrorFieldGroup = Math.min(errorFieldGroup, {$field_group_index});";
// else { echo $field[FIELD_GROUP] . "<br/>"; foreach ($field_group_names AS $key => $value) echo "{$key} => {$value}<br/>"; exit; }
					}
					$validation_script .= "\n\t}";
					$validation_script .= "\n\telse {";
					$validation_script .= "\n\t\t\$(\"#{$error_field_name}\" + _suffix).removeClass(\"error_input\");";
					if ($field[FIELD_CONFIRMATION] !== FALSE && ($field_type == PASSWORD || $field_type == MD5_PASSWORD))
						$validation_script .= "\n\t\t\$(\"#confirm_{$field_name}\" + _suffix).removeClass(\"error_input\");";
					$validation_script .= "\n\t}";
				}
			}
		}

		// Subtables only report their errors, they do not, themselves, display them
		// TODO: If no errors, return JSON fields from N_TO_N or ONE_TO_N subtable
		$validation_script .= "\n\tif (_suffix != \"\")\n\t\treturn errors;";
		$validation_script .= "\n\telse if (errors.length > 0 && !draft) {\n\t\tregisterErrors(\"{$table_name}_error\", errors);";
		if ($field_groups) {
			$validation_script .= "\n\t\tsetFieldGroup(\"{$table_name}\", errorFieldGroup);";
		}
		$validation_script .= "\n\t\treturn false;\n\t}\n\telse {";
		if ($TABLE[TABLE_CONFIRMATION]) {
			$validation_script .= "\n\t\tvar {$table_name}Confirmation = confirm(\"" . htmlentities($TABLE[TABLE_CONFIRMATION]) . "\");\n\t\tif (!{$table_name}Confirmation) return false;";
		}

		if ($TABLE[TABLE_SUBMISSION] == AJAX_SUBMISSION) {
			$ajax_fields = implode(", ", $ajax_fields);
			if ($TABLE[TABLE_CALLBACK])
				$ajax_callback = "{$TABLE[TABLE_CALLBACK]}(args, response);";
			$validation_script .= "\n\t\tvar args = { {$ajax_fields} };\n\t\t$.ajax({ type: \"POST\", url: \"{$SETTINGS['JEKILL_ROOT']}/schema/save_entity.php\", data: args, success: function (response) {\n\t\t\t{$ajax_callback}\n\t\t},\n\t" /*. "error: function (request) { alert(request.getAllResponseHeaders()) }" */ . "});\n\t}";
		}
		else {
			$validation_script .= "\n\t\twindow.onbeforeunload = null;\n\t\t\$(\"#{$table_name}_form\").attr(\"action\", \"{$table_processor}\").submit();";
			$validation_script .= "\n\t}";
		}

		$validation = $validation_header . $validation_vars . $validation_script . "\n}";
// echo $validation;
		$script = $html->script()->type("text/javascript")->content($validation);
		return $script;
	}

	function phone_home () {
		$request = "http://www.classiccarstudio.com/licensing/";
		$session = curl_init($request);
		curl_setopt($session, CURLOPT_POST, 1);

		$params = "domain=" . $_SERVER['SERVER_NAME'];

		curl_setopt($session, CURLOPT_POSTFIELDS , $params);
		curl_setopt($session, CURLOPT_HTTPHEADER, array("Content-Type:application/atom+xml"));
		curl_setopt($session, CURLOPT_HEADER, false); /* Do not return headers */
		curl_setopt($session, CURLOPT_RETURNTRANSFER, 1); /* If you set this to 0, it will take you to a page with the http response */

		/* Execute cURL session and close it */
		$response = curl_exec($session);

		curl_close($session);
		return $response;
	}

}

?>
