<?php

require_once("../base/database_schema.php");
require_once("../base/schema_manager.php");
require_once("../base/settings.php");

$settings_php = "\$SETTINGS = array();\n";

foreach ($_SETTINGS AS $setting_name => $setting) {
	if ($_REQUEST["{$setting_name}_changed"]) {
		if ($setting[FIELD_TYPE] == IMAGE || $setting[FIELD_TYPE] == FILE) {
			$filename = SchemaManager::save_uploaded_file("settings", $setting);
			$_REQUEST[$setting_name] = $filename;
		}
		$setting_value = $_REQUEST[$setting_name];
	}
	else $setting_value = $SETTINGS[$setting_name];
	// $setting_value = addslashes($setting_value);
	$setting_value = str_replace("\"", "\\\"", stripslashes($setting_value));

	$settings_php .= "\n\$SETTINGS['{$setting_name}'] = \"{$setting_value}\";";
}

$settings_php = "<?php\n\n{$settings_php}\n\n?>";

$settings_file_handle = fopen("../base/settings.php", "w+");
fwrite($settings_file_handle, $settings_php);
fclose($settings_file_handle);

header("Location: ../control_panel.php?func=settings");
exit;

?>