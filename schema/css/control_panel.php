<?php

require_once("../../base/settings.php");

if ($SETTINGS['CONTROL_PANEL_BACKGROUND_COLOR'] == "")
	$SETTINGS['CONTROL_PANEL_BACKGROUND_COLOR'] = "FFFFFF";

if ($SETTINGS['CONTROL_PANEL_COLOR'] == "")
	$SETTINGS['CONTROL_PANEL_COLOR'] = "9999AA";

if ($SETTINGS['CONTROL_PANEL_TAB_COLOR'] == "")
	$SETTINGS['CONTROL_PANEL_TAB_COLOR'] = "CCCCCC";

$r = substr($SETTINGS['CONTROL_PANEL_TAB_COLOR'], 0, 2);
$g = substr($SETTINGS['CONTROL_PANEL_TAB_COLOR'], 2, 2);
$b = substr($SETTINGS['CONTROL_PANEL_TAB_COLOR'], 4, 2);
$correction = .9;

$SETTINGS['CONTROL_PANEL_SUBTAB_COLOR'] = dechex(hexdec($r)/$correction) . dechex(hexdec($g)/$correction) . dechex(hexdec($b)/$correction);

if ($SETTINGS['CONTROL_PANEL_TEXT_COLOR'] == "")
	$SETTINGS['CONTROL_PANEL_TEXT_COLOR'] = "000000";

if ($SETTINGS['CONTROL_PANEL_LINK_COLOR'] == "")
	$SETTINGS['CONTROL_PANEL_LINK_COLOR'] = "FFFFFF";

if ($SETTINGS['CONTROL_PANEL_ROW_EVEN_COLOR'] == "")
	$SETTINGS['CONTROL_PANEL_ROW_EVEN_COLOR'] = "AAAAAA";

if ($SETTINGS['CONTROL_PANEL_ROW_ODD_COLOR'] == "")
	$SETTINGS['CONTROL_PANEL_ROW_ODD_COLOR'] = "CCCCCC";

header("Content-type: text/css");

?>

a {
	cursor: pointer;
}

a img {
	border: none;
}

.absolute {
	position: absolute;
}

body {
	background-color: #<?php echo $SETTINGS['CONTROL_PANEL_BACKGROUND_COLOR']; ?>;
	color: #<?php echo $SETTINGS['CONTROL_PANEL_TEXT_COLOR']; ?>;
	font-family: Arial;
	font-size: 12px;
}

body.login {
	margin: 0px;
	padding: 0px;
	background-color: #<?php echo $SETTINGS['CONTROL_PANEL_COLOR']; ?>;
	text-align: center;
}

/*
.button_link {
	background: url("<?php echo $SETTINGS['JEKILL_ROOT']; ?>/schema/images/pill_button.png");
	color: #000000;
	display: inline;
	height: 20px;
	padding-top: 5px;
	text-align: center;
	text-decoration: none;
	width: 125px;
}

.button_link:hover {
	background: url("<?php echo $SETTINGS['JEKILL_ROOT']; ?>/schema/images/pill_button_selected.png");
}
*/

.button, .file_label {
	cursor: pointer;
	border-top: solid #CCCCCC 1px;
	border-bottom: solid #666666 1px;
	border-left: solid #AAAAAA 1px;
	border-right: solid #888888 1px;
	-moz-border-radius: 10px;
	margin-right: 3px;
	width: 85px;
}

.button:hover {
	opacity: 0.8;
	filter: alpha(opacity=80);
}

.button:active {
	background-color: #666666;
	color: #CCCCCC;
}

.center {
	text-align: center;
}

.clear {
	clear: both;
}

.control_panel_frame {
	margin-left: auto;
	margin-right: auto;
	width: 1200px;
}

#control_panel_body {
	background-color: #<?php echo $SETTINGS['CONTROL_PANEL_COLOR']; ?>;
	clear: left;
	/* border: solid #333333 1px; */
	margin-left: auto;
	margin-right: auto;
	text-align: left;
	width: 1200px;
}

#control_panel_content {
	padding: 10px;
}

.copy_input {
	height: 200px;
	width: 600px;
}

.tiny_copy_input {
	height: 100px;
	width: 600px;
}

.deleted {
	background=color: #FFCCCC;
}

.disassociated {
	background-color: #CCCC99
}

.dropdown {
	background-color: #FFFFFF;
	position: absolute;
	padding: 5px;
	padding-left: 20px;
}

.militime input {
	text-align: right;
	width: 30px;
}

.subtable_record .dropdown a {
	color: #666666;
}

#existing_record_search {
	width: 99%;
}

.field_header {
	font-size: 14px;
	font-weight: bold;
	margin: 10px;
	margin-left: 125px;
}

.file_bank {
	position: relative;
	width: 500px;
}

.file_bank_file input {
	width: 100px;
}

.file_bank_file {
	float: left;
	height: 100px;
	margin: 5px;
	position: relative;
	text-align: center;
	width: 100px;
}

.file_bank_file .file_control {
	position: absolute;
	right: 5px;
	top: 5px;
}

.file_label {
	background-color: #EEEEEE;
	display: block;
	padding: 5px 10px;
	text-align: center;
	width: 65px;
}

.footer {
	margin-top: 30px;
}

.gallery {
	float: left;
	height: 200px;
	padding: 10px 5px;
	text-align: center;
	width: 150px;
}

.header, .footer {
	font-size: 16px;
	font-weight: bold;
	margin-left: 15px;
}

.header {
	margin-bottom: 30px;
}

.header a, .footer a {
	color: #<?php echo $SETTINGS['CONTROL_PANEL_LINK_COLOR']; ?>;
	text-decoration: none;
}

.header a:hover, .footer a:hover {
	text-decoration: underline;
}

img.preview {
	border: solid #333333 1px;
}

input, select, textarea {
	border: 1px solid #<?php echo $SETTINGS['CONTROL_PANEL_TEXT_COLOR']; ?>;
	padding: 3px;
}

input.checkbox, input.file {
	border-width: 0px;
}

.input, .name {
	width: 200px;
}

input.login {
	width: 150px;
}

#layout_control {
	float: right;
}

.login_frame {
	background-color: #FFFFFF;
	/* border: solid #666666 1px; */
	margin-left: auto;
	margin-right: auto;
	margin-top: 100px;
	padding: 40px;
	padding-bottom: 60px;
	width: 350px;
}

.login_frame img {
	width: 300px;
}

#logo_header {
	text-align: right;
}

.menubar_carrier {
	position: relative;
	top: 1px;
	width: 2500px;
}

.menubar {
	/* background: url("../images/menu_background.jpg"); */
	overflow: hidden;
/*	margin-left: 20px; */
	position: relative;
	width: 1200px;
}

.menubar_container {
	float: left;
	width: 1200px;
}

.menubar_control {
	float: left;
}

.menubar_control img {
	margin-left: 5px;
}

.menu_tab {
	background-color: #<?php echo $SETTINGS['CONTROL_PANEL_TAB_COLOR']; ?>;
	/* border: solid #333333 1px; */
	font-size: 8pt;
	float: left;
	margin-right: 3px;
	padding-top: 5px;
	padding-left: 15px;
	padding-right: 15px;
	padding-bottom: 2px;
	text-align: center;
}

.menu_tab a {
	color: #333333;
	font-size: 8pt;
	font-weight: bold;
	text-decoration: none;
}

.menu_tab_selected {
	/* Must be same color as control_panel_body background-color */
	background-color: #<?php echo $SETTINGS['CONTROL_PANEL_COLOR']; ?>;
	/* border-bottom: solid #82BA41 1px; */
	color: #FFFFFF;
	font-weight: bold;
}

.menu_tab_selected a {
	color: #<?php echo $SETTINGS['CONTROL_PANEL_LINK_COLOR']; ?>;
}

.notice {
	/* text-decoration: underline; */
	font-style: italic;
}

.page_header {

}

.page_body {

}

.page_footer {
	font-size: 9pt;
	text-align: center;
}

.rows {
	/* border: solid #333333 1px; */
	border-bottom: none;
}

.row {
	/* border-bottom: solid #<?php echo $SETTINGS['CONTROL_PANEL_COLOR']; ?> 1px; */ /* #333333 */
	padding-left: 5px;
	position: relative;
}

.row a {
	color: #FFFFFF;
	font-weight: bold;
	text-decoration: none;
}

.row a:hover {
	text-decoration: underline;
}

.row a:visited {
	color: #FFFFFF;
}

.row_control {
	position: absolute;
	right: 20px;
	top: 10px;
}

.row_even {
	background-color: #<?php echo $SETTINGS['CONTROL_PANEL_ROW_EVEN_COLOR']; ?>;
	padding-bottom: 3px;
	padding-top: 3px;
}

.row_odd {
	background-color: #<?php echo $SETTINGS['CONTROL_PANEL_ROW_ODD_COLOR']; ?>;
	padding-bottom: 3px;
	padding-top: 3px;
}

.row_odd .subrecord_container .row_even, .row_even .subrecord_container .row_even {
	background-color: #888888;
}

.row_odd .subrecord_container .row_odd, .row_even .subrecord_container .row_odd {
	background-color: #999999;
}

.row_field_header {
	font-weight: bold;
	/* text-align: center; */
}

.row_field_header a {
	color: #<?php echo $SETTINGS['CONTROL_PANEL_LINK_COLOR']; ?>;
	text-decoration: none;
}

.row_field_header a:hover {
	text-decoration: underline;
}

.row_field, .row_field_header {
	float: left;
}

.right {
	text-align: right;
}

#search {
	float: right;
}

#search input, #search select {
	margin-left: 5px;
}

.search_current_result_page {
	background-color: #<?php echo $SETTINGS['CONTROL_PANEL_TAB_COLOR']; ?>;
	border: solid #333333 1px;
	float: right;
	margin: 2px;
	padding: 3px;
}

.search_result_overview {
	text-align: right;
}

.search_result_page {
	float: right;
	margin-top: 2px;
	padding: 3px;
}

.search_result_page a {
	color: #FFFFFF;
	font-weight: bold;
	text-decoration: none;
}

.search_result_page a:hover {
	text-decoration: underline;
}

.sentence {
	width: 300px;
}

.submenu {
	/* border-bottom: solid #<?php echo $SETTINGS['CONTROL_PANEL_TEXT_COLOR']; ?> 1px; */
	border-bottom: solid #<?php echo $SETTINGS['CONTROL_PANEL_SUBTAB_COLOR']; ?> 1px;
	padding: 5px;
	padding-bottom: 0px;
}

.menubar .submenu {
	background-color: #<?php echo $SETTINGS['CONTROL_PANEL_COLOR']; ?>;
}

.submenu .menu_tab_selected {
	background-color: #<?php echo $SETTINGS['CONTROL_PANEL_SUBTAB_COLOR']; ?>; 
}

.submenu .menu_tab_selected a {
	color: #<?php echo $SETTINGS['CONTROL_PANEL_COLOR']; ?>;
}

#subrecord_chooser {
	border: solid #333333 1px;
	height: 300px;
	margin-bottom: 5px;
	overflow: auto;
}

.subrecord_container {
	/* display: none; */
	/* overflow: hidden; 6/22/11 Why is this here? */
}

.subrecord_control {
	float: right;
	cursor: pointer;
	margin-left: 5px;
}

.subrecord_number {
	/* border-bottom: solid #666666 1px; */
	padding: 10px;
}

.subrecord_number {
	border: solid #FFFFFF 2px;
	color: #FFFFFF;
	font-size: 14px;
	font-weight: bold;
	margin: 2px;
	padding: 5px;
	position: absolute;
}

.subtable_record {
	clear: left;
	padding: 5px;
}

.subrecord_rows .row_even {

}

.subrecord_rows .row_odd {

}

.subrecord_rows {
	background-color: #<?php echo $SETTINGS['CONTROL_PANEL_COLOR']; ?>;
	clear: left;
	margin-right: 10px;
	margin-bottom: 15px;
	padding: 10px;
	padding-top: 10px;
}

.subrecord_header {
	background-color: #<?php echo $SETTINGS['CONTROL_PANEL_COLOR']; ?>;
	padding: 5px;
	padding-bottom: 0px;
	text-align: center;
	width: 175px;
}

.subtable_record_header .subrecord_control {
	display: none;
}

.subtable_record_header:hover .subrecord_control {
	display: block;
	float: right;
}

.subheader {
	margin-top: 15px;
}

.swfupload {
	position: absolute;
}

table {
	font-size: 12px;
	margin-left: auto;
	margin-right: auto;
	/* width: 100% */
}

.time {
	width: 75px;
}

.transparent {
	opacity: 0.0;
	-moz-opacity: 0.0;
	filter: alpha(opacity=0);
}