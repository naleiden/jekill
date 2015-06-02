<?php

echo "This feature is disabled."; exit;

// require_once("../base/database_schema.php");
require_once("../base/schema_manager.inc");

function write_attributes ($entity, $MODIFIERS="") {
	global $TABLE_MODIFIERS, $COPY_MODIFIERS, $FIELD_MODIFIERS, $REQUIRED_OPTIONS, $FIELD_TYPES, $FIELD_PERMISSIONS;

	if ($MODIFIERS == "") {
		$MODIFIERS = $FIELD_MODIFIERS;
		$field = 1;			// $field: SETTING or field in SCHEMA
	}

	$entity_definition = "";
	foreach ($entity AS $modifier => $value) {

		if ($entity_definition != "")
			$entity_definition .= ", ";

		if ($field) {
			switch ($modifier) {
				case FIELD_REQUIRED:	$value = $REQUIRED_OPTIONS[$value];	break;
				case FIELD_TYPE:	$value = $FIELD_TYPES[$value];		break;
				case FIELD_ACCESS:	$value = $FIELD_PERMISSIONS[$value];	break;
				default:		$value = "\"{$value}\"";		break;
			}
		}
		/* Allow for the direct reference to PHP variables. */
		else if ($value[0] != "$")
			$value = "\"{$value}\"";

		$entity_definition .= "{$MODIFIERS[$modifier]} => {$value}";
	}
	return $entity_definition;
}

$schema = "require_once(\"define.php\");\nrequire_once(\"schema_manager.php\");\n\n\$_SETTINGS = array();\n\$COPY = array();\n\$SCHEMA = array();\n";

foreach ($_SETTINGS AS $setting_name => $setting) {
	$setting_definition = write_attributes($setting);
	$schema .= "\n\$_SETTINGS['{$setting_name}']\t\t= array({$setting_definition});";
}

$schema .= "\n";

foreach ($COPY AS $copy_ID => $copy) {
	$copy_definition = write_attributes($copy, $COPY_MODIFIERS);
	$schema .= "\n\$COPY['{$copy_ID}']\t\t= array({$copy_definition});";
}

foreach ($SCHEMA AS $schema_table_name => $table) {
	$table_settings = array();
	$table_fields = "";
	foreach ($table AS $field_name => $field) {
		if (!is_array($field)) {	// '$field' is a table setting, not a true field.
			$table_settings[] = "$TABLE_MODIFIERS[$field_name] => \"{$field}\"";
		}
		else {
			$field_definition = write_attributes($field);
			$table_fields .= "\n\$SCHEMA['{$schema_table_name}']['{$field_name}']\t\t= array({$field_definition});";
		}
	}
	$table_definition = (count($table_settings) > 0) ? implode(", ", $table_settings) : "";
	$table_header = "\n\n\$SCHEMA['{$schema_table_name}'] = array({$table_definition});";
	$schema .= $table_header . $table_fields;
}

$schema = "<?php\n\n{$schema}\n\n?>";

copy("../base/database_schema.php", "../base/database_schema." . date("d.m.Y.h:i") . ".php");
$schema_handle = fopen("../base/database_schema.php", "w+");
fwrite($schema_handle, $schema);
fclose($schema_handle);

?>