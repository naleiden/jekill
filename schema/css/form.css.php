<?php

require_once("../../base/settings.php");

header("Content-Type: text/css");

?>

.button {
	cursor: pointer;
}

.clear {
	clear: both;
}

.clickable {
	cursor: pointer;
}

.deleted {
	background-color: #FFDDDD;
}

.error {
	/* display: none; */
	-moz-border-radius: 15px;
	padding: 15px;
}

.error, .error_input {
	background-color: #FFCCCC;
	border: dotted #CC0000 1px;
	color: #CC0000;
}

/*
.file {
	opacity: 0;
	filter: alpha(opacity=0);
}
*/

.field {
	/* clear: left; */
	/* height: 20px; */
	padding: 5px 0px;
	margin: 0px;
}

.field_extras {
	float: left;
	padding-left: 5px;
	width: 225px;
}

.field_label {
	float: left;
	padding-right: 5px;
	text-align: right;
	width: 120px;
}

.field_help {
	background: url("<?php echo $SETTINGS['JEKILL_ROOT']; ?>/schema/images/help_icon.png");
	float: left;
	height: 15px;
	width: 15px;
}

.field_help_text {
	background-color: #FFFFFF;
	display: none;
	font-size: 10px;
	padding: 10px;
	position: absolute;
	width: 175px;
}

.field_help:hover .field_help_text {
	display: block;
}

.field_input {
	float: left;
	padding-right: 5px;
}


.hint {
	color: #AAAAAA;
	font-style: italic;
	text-align: center;
}

.hidden {
	display: none;
}

label {
	cursor: pointer;
}

.left {
	float: left;
}

.link_suggestion_results {
	background-color: #FFFFFF;
	position: absolute;
	z-index: 999;
}

.link_suggestion {
	padding: 5px;
}

.link_suggestion_selected {
	background-color: #9999CC;
}

.link_suggestion_selected a {
	color: #FFFFFF;
}

.link_suggestion a {
	color: #0000CC;
}

.message {
	background-color: #CCFFCC;
	border: dotted #00CC00 1px;
	color: #009900;
	padding: 15px;
}

.optional_hidden {
	display: none;
}

/*
select {
	cursor: pointer;
	opacity: 0;
	filter: alpha(opacity=0);
}
*/

.warning {
	background: url("../images/warning.gif");
	background-repeat: no-repeat;
	background-position: 10px 5px;
	background-color: #FFFF99;
	border: dotted #FF9900 1px;
	color: #FF9900;
	padding: 10px;
	padding-top: 55px;
}





