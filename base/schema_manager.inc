<?php

require_once("define.php");

srand();
//$block_size = mcrypt_get_block_size(MCRYPT_TripleDES, MCRYPT_MODE_CBC);
// $CRYPT_IV = mcrypt_create_iv(mcrypt_get_block_size(MCRYPT_TripleDES, MCRYPT_MODE_CBC), MCRYPT_DEV_RANDOM);
//$CRYPT_IV = mcrypt_create_iv($block_size, MCRYPT_RAND);

define("CRYPT_IV", $CRYPT_IV);

$COMMON_WORDS = array("a", "able", "about", "across", "after", "all", "almost", "also", "am", "among", "an", "and", "any", "are", "as", "at", "be", "because", "been", "but", "by", "can", "cannot", "could", "dear", "did", "do", "does", "down", "either", "else", "ever", "every", "for", "from", "get", "got", "had", "has", "have", "he", "her", "hers", "him", "his", "how", "however", "i", "if", "in", "into", "is", "it", "its", "just", "least", "let", "like", "likely", "may", "me", "might", "most", "must", "my", "neither", "no", "nor", "not", "of", "off", "often", "on", "once", "only", "or", "other", "our", "own", "rather", "said", "say", "says", "she", "should", "since", "so", "some", "than", "that", "the", "their", "theirs", "them", "then", "there", "these", "they", "this", "tis", "to", "too", "twas", "up", "upon", "us", "wants", "was", "we", "went", "were", "what", "when", "where", "which", "while", "who", "whom", "why", "will", "with", "would", "yet", "you", "your");

/********************/
/*  Copy Modifiers  */	// DEPRECATED
/********************/
define("COPY_NAME",		0);
define("COPY_INCLUDE_PAGE",	1);
define("COPY_DESCRIPTION",	2);

$COPY_MODIFIERS = array(
			COPY_NAME		=> "COPY_NAME",
			COPY_INCLUDE_PAGE	=> "COPY_INCLUDE_PAGE",
			COPY_DESCRIPTION	=> "COPY_DESCRIPTION"
		);

define("TABLE_SERIALIZATION_SALT", "T@bl3C3r3@l");

define("NO_SORT",	1);

define("DATBASE_TABLE",	0);
define("CUTOM_TABLE",	1);
define("TABLE_GROUP",	2);

/* Database Table Engines */
define("MYISAM",	0);
define("INNODB",	1);

/* Submission Types */
define("FORM_SUBMISSION",	0);	// Values for TABLE_SUBMISSION
define("AJAX_SUBMISSION",	1);

/* Default Field Groups (Always Visible) */
define("FIELD_HEADER_GROUP",	"_FIELD_HEADER_GROUP_");
define("FIELD_FOOTER_GROUP",	"_FIELD_FOOTER_GROUP_");

/*********************/
/*  Table Modifiers  */
/*********************/
define("TABLE_NAME",		0);
define("TABLE_LABEL",		1);
define("TABLE_SORT",		2);
define("TABLE_GROUP_SORT",	3);
define("TABLE_ACCESS",		4);
define("TABLE_UNIQUE_ID",	5);
define("TABLE_ENGINE",		6);	// MyISAM or InnoDB (InnoDB automatically used for N to N)
define("TABLE_TYPE",		7);	// DATABASE_TABLE, TABLE_GROUP or CUSTOM_TABLE. Default to DATABASE_TABLE
define("TABLE_PARENT",		8);	// This table is a "child concern" of its parent table
define("TABLE_BROWSE",		9);	// The script that generates this table's browse page. For CUSTOM_TABLE
define("TABLE_FORM",		10);	// The script that generates this table's form. For CUSTOM_TABLE
define("TABLE_STYLE",		11);
define("TABLE_SUBMISSION",	12);	// Whether a form post, an ajax post, maybe later a SOAP / REST submission...
define("TABLE_PROCESSOR",	13);	// The script that saves the table data. /schema/save_entity.php by default
define("TABLE_PREPROCESSOR",	14);	// Defines a script that will process a table's record prior to saving in schema/save_entity.php
define("TABLE_POSTPROCESSOR",	15);	// Defines a script that will process a table's record after saving in schema/save_entity.php
define("TABLE_CALLBACK",	16);	// AJAX_SUBMISSION callback - javascript function to be called after ajax form submission. Function name only - no args.
define("TABLE_PUBLISHED",	17);	// Whether or not this table is published (publicly visible or only for internal purposes)
define("TABLE_AUTHENTICATION",	18);	// Make sure $_POST data contains same {$table_name}_authentication as $_SESSION before saving.
define("TABLE_VALIDATION",	19);	// TODO: Whether or not to generate validation javascript
define("TABLE_CHANGE_PROMPT",	20);	// On by default. Set TABLE_CHANGE_PROMPT to prevent a warning from displaying when leaving page after changes have been made.
define("TABLE_CHANGE_WARNING",	20);
define("TABLE_CAPTCHA",		21);	// TODO: Display a Captcha before submitting table. TODO
define("TABLE_CONFIRMATION",	22);	// Confirmation message prior to form submit.
define("TABLE_GROUP_CONTROL",	23);	// HIDDEN to hide FIELD_GROUP controls, READ_ONLY to deactivate FIELD_GROUP control links.
define("TABLE_DEFAULT_GROUP",	24);	// Allows you to set the name of the default group: TABLE_DEFAULT_GROUP => "Foo". "General" by default.
define("TABLE_SERIALIZATION",	25);	// Serialize the TABLE and 
define("RECORD_ACCESS",		26);	// Defines which level of user can view all records (no record limitations)
define("RECORD_ACCESS_FIELD",	27);	// Defines the field on this table that must match the value of the specified user's field
define("RECORD_ACCESS_USER_FIELD",	28);	// Defines the field on the 'user' table that must match the value of the specified field on this table
define("RECORD_CREATION",	29);	// The user level that can create records of this table
define("RECORD_DELETION",	30);	//          ""             delete       ""
define("RECORD_LABEL",		31);	// The field which holds the label for a record
define("RECORD_WHERE",		32);	// Limit which types of records are displayed in preview rows
define("DEFAULT_BROWSE_LAYOUT",	33);	// "row" if not specified. Change the default layout of a table's records when browsing.
define("RECORD_BROWSE_FIELDS",	34);	// For supplying custom fields in the "row" layout type, ex: SUM(table_name.points). Comma delimited. MUST USE TABLE NAME IN FIELD REFERENCE. May need RECORD_GROUP_BY
define("RECORD_BROWSE_HEADERS",	35);	// For defining headers to custom fields defined by RECORD_FIELDS
define("RECORD_GROUP_BY",	36);	// For displaying aggregate data in the 'Browse' perspective. References a fieldname: RECORD_GROUP_BY => "customer"
define("RECORD_WHERE",		37);	// Limit which types of records are displayed in preview rows
define("DEFAULT_BROWSE_LAYOUT",	38);	// "row" if not specified. Change the default layout of a table's records when browsing.
define("TABLE_BROWSE_LIMIT",	39);	// The number of results to return by default in the 'browse' mode.


$TABLE_MODIFIERS = array(
			TABLE_NAME	=> "TABLE_NAME",
			TABLE_LABEL	=> "TABLE_LABEL",
			TABLE_SORT	=> "TABLE_SORT",
			TABLE_ACCESS	=> "TABLE_ACCESS",
			TABLE_UNIQUE_ID	=> "TABLE_UNIQUE_ID",
			TABLE_PARENT	=> "TABLE_PARENT",
			TABLE_PROCESSOR	=> "TABLE_PROCESSOR",
			TABLE_PREPROCESSOR	=> "TABLE_PREPROCESSOR",
			TABLE_POSTPROCESSOR	=> "TABLE_POSTPROCESSOR",
			TABLE_PUBLISHED		=> "TABLE_PUBLISHED",
			RECORD_ACCESS		=> "RECORD_ACCESS",
			RECORD_CREATION		=> "RECORD_CREATION",
			RECORD_ACCESS_FIELD	=> "RECORD_ACCESS_FIELD",
			RECORD_ACCESS_USER_FIELD=> "RECORD_ACCESS_USER_FIELD"
		);


/*****************/
/*  Field Types  */
/*****************/
define("TEXT_ARRAY",	1);	// Stored as a serialized PHP array. Can be stored natively when MySQL supports SQL array datatype
define("BOOL",		2);
define("BUTTON",	3);
define("COLOR",		4);
define("COPY",		5);
define("CREDIT_CARD",	6);
define("DATE",		7);
define("DATE_RANGE",	8);	// DATETIME - DATETIME: TODO!
define("DATETIME",	9);
define("EMAIL",		10);
define("ENUMERATION",	11);	// Use MULTIPLE_SELECT to allow non-mutual exclusion
define("FILE",		12);
define("HIDDEN_VALUE",	13);	// type="hidden" input
define("HTML",		14);	// Raw HTML. Not form / database related.
define("HTML_COPY",	15);
define("ID",		16);
define("IMAGE",		17);
define("IMAGES",	18);
define("IMAGE_EXCERPT",	19);	// File path. Generated by IMAGE field in same table reference by LINK_FIELD
define("IMAGE_ANNOTATION",	20);
define("IMAGE_SELECT",	21);
define("GLOBAL_DEFAULT",22);	// Default to $_GET, $_POST, or $_SESSION?
define("KEYWORDS",	23);
define("LINK",		24);
define("LINK_N_TO_N",	25);	// Indirect non-exclusive, two-way relationship: Tables are connected via intermediary "map" table.
define("LINK_ONE_TO_N",	26);	// Indirect exclusive, one-way relationship: Other table points to this table. Field is "implicit" for this table.
define("LINK_MUTUAL",	27);	// Table and LINK_TABLE must be the same. When A -> B also means we want B -> A
define("LINK_SUBTABLE",	28);	// Fully expanded subtable form.
define("MULTI_LINK",	29);	// A SELECT_MULTIPLE LINK. A different type because we need to change the storage type.
define("NAME",		30);
define("NOTES",		31);
define("NUMBER",	32);
define("TINY_NUMBER",	33);
define("MONEY",		34);
define("KMONEY",	35);
define("GMONEY",	36);
define("PASSWORD",	37);
define("MD5_PASSWORD",	38);
define("SENTENCE",	39);
define("SERVER_FILE",	40);
define("SERVER_FOLDER",	41);
define("SET",		42);		// Using the MySQL SET field type.
define("SUBTABLE",	43);
define("SUGGEST",	44);
define("TIME",		45);
define("TIMESTAMP",	46);
define("MILITIME",	47);
define("YEAR",		48);
define("URL",		49);
define("USER_DEFAULT",	50);	// Type defined and value set by field defined by LINK_FIELD on LINK_TABLE for current user.
define("VIDEO",		51);
define("RICH_URL_ID",	52);
define("RANDOM_PIN",	53);
define("JEKILL_CONTENT",54);

$FIELD_TYPES = array(
		TEXT_ARRAY	=> "TEXT_ARRAY",
		BOOL		=> "BOOL",
		BUTTON		=> "BUTTON",
		COLOR		=> "COLOR",
		COPY		=> "COPY",
		DATE		=> "DATE",
		DATETIME	=> "DATETIME",
		EMAIL		=> "EMAIL",
		ENUMERATION	=> "ENUMERATION",
		FILE		=> "FILE",
		HTML_COPY	=> "HTML_COPY",
		ID		=> "ID",
		IMAGE		=> "IMAGE",
		JEKILL_CONTENT	=> "JEKILL_CONTENT",
		LINK		=> "LINK",
		LINK_N_TO_N	=> "LINK_N_TO_N",
		LINK_ONE_TO_N	=> "LINK_ONE_TO_N",
		LINK_MUTUAL	=> "LINK_MUTUAL",
		LINK_SUBTABLE	=> "LINK_SUBTABLE",
		MULTI_LINK	=> "MULTI_LINK",
		MILITIME	=> "MILITIME",
		NAME		=> "NAME",
		NOTES		=> "NOTES",
		NUMBER		=> "NUMBER",
		TINY_NUMBER	=> "TINY_NUMBER",
		MONEY		=> "MONEY",
		KMONEY		=> "KMONEY",
		GMONEY		=> "GMONEY",
		PASSWORD	=> "PASSWORD",
		SENTENCE	=> "SENTENCE",
		SERVER_FILE	=> "SERVER_FILE",
		SERVER_FOLDER	=> "SERVER_FOLDER",
		SET		=> "SET",
		SUBTABLE	=> "SUBTABLE",
		TIME		=> "TIME",
		TIMESTAMP	=> "TIMESTAMP",
		URL		=> "URL",
		VIDEO		=> "VIDEO",
		RICH_URL_ID	=> "RICH_URL_ID",
		IMAGE_EXCERPT	=> "IMAGE_EXCERPT",
		YEAR		=> "YEAR"
	);

// LINK OPTIONS
define("LINK_NEW_ONLY",		2);
define("LINK_EXISTING_ONLY",	4);
define("LINK_EXPANDED",		8);
define("LINK_FULLY_EXPANDED",	16);
define("LINK_INLINE",		32);
define("LINK_READ_ONLY",	64);
define("LINK_DELETE_PROPEGATE",128);	// Do not allow a subrecord to be disassociated, only deleted.
define("LINK_DELETE_ONLY",	128);
define("LINK_HEADER",		256);	// Force the subrecord header to appear
define("LINK_LABEL_ONLY",	512);	// No subtable form is included in parent form. Best when used with LINK_EXISTING_ONLY.
define("LINK_NO_FORM",		512);	// "

define("RADIO",		1);

/*********************/
/*  Field Modifiers  */
/*********************/
define("FIELD_NAME",		0);
define("FIELD_TYPE",		1);
define("FIELD_LABEL",		2);
define("FIELD_REQUIRED",	3);	// Option numbers before & including 'FIELD_REQUIRED' should not be changed.
define("FIELD_DEFAULT",		4);	// The default value given to this field.
define("FIELD_DEFAULT_VALUE",	4);	// ""
define("FIELD_VALIDATION",	5);	// The validation function used to determine valid input if FIELD_REQUIRED ('notEmpty' by default)
define("FIELD_UNIQUE",		6);	// Whether the field must have a unique value in its table
define("FIELD_ACCESS",		7);	// Who can see / edit this field.
define("FIELD_VALUE",		8);	// 
define("FIELD_STATUS",		9);	// Read Only? Deprecated?
define("FIELD_OPTIONS",		10);	// Options for ENUMERATIONs, non-required options for other types
define("SET_OPTIONS",		10);	// For SETs
define("FIELD_ARGUMENTS",	11);
define("FIELD_ARGS",		11);
define("FIELD_CONFIRMATION",	12);	// Require input confirmation. Now just PASSWORD types. Could later be used for EMAIL
define("FIELD_HASH",		13);	// TODO: Hash function used to process field data before storage. Must be available in js and php.
define("FIELD_VARIATION",	14);	// Used to display a different input type, e.g., radio buttons instead of a select
define("FIELD_EXTENSIONS",	15);	// Used in conjunction with SERVER_FILE to specify selectable extensions.
define("FIELD_CONSTRAINT",	16);	// Define a constraint on a field, e.g., Must be > 0 or > <%other_field%> TODO: Implement this in tableValidation function
define("FIELD_MAX_LENGTH",	17);
define("FIELD_MINIMUM",		18);	// Used for SLIDER datatype, among other things
define("FIELD_MAXIMUM",		19);
define("FIELD_ENCODING",	20);	// The name of an encryption algorithm available through mcrypt_list_algorithms 
define("SUBFORM_TABLE",		21);	// Aggregate data - this table is composed of another table

// Image Constraints
define("IMAGE_HEIGHT",		22);	// Preferred width and height.
define("IMAGE_WIDTH",		23);
define("IMAGE_X",		24);
define("IMAGE_Y",		25);
define("IMAGE_BACKGROUND_COLOR",26);
define("IMAGE_QUALITY",		27);
define("IMAGE_ALIGN",		28);
define("IMAGE_ZOOM",		29);
define("IMAGE_MAX_HEIGHT",	30);	// Maximum height allowed for image
define("IMAGE_MAX_SIZE",	31);	// Maximum size in Kb
define("IMAGE_MAX_WIDTH",	32);	// Used to force resizing on upload
define("IMAGE_MIN_HEIGHT",	33);	// Used to force resizing on upload
define("IMAGE_MIN_WIDTH",	34);
define("IMAGE_OPTIMUM_HEIGHT",	35);	// Dimensions for cropping purposes.
define("IMAGE_OPTIMUM_WIDTH",	36);	// Dimensions for cropping purposes.
define("IMAGE_ASPECT_RATIO",	37);
define("IMAGE_ASPECT_DELTA",	38);	// The acceptable +- when comparing IMAGE_ASPECT_RATIO to actual ratio
define("IMAGE_AUTO_RESIZE",	39);	// Auto resize the image to (IMAGE_MAX_WIDTH x computed height) on upload.
define("IMAGE_THUMBNAIL",	40);	// Automatically generate one or multiple thumbnails. => array(array(FILE_PATH => "[BASE_FILE_NAME_DIRECTORY][BASE_FILE_NAME][BASE_FILE_NAME_EXTENSION]", IMAGE_WIDTH => X, IMAGE_HEIGHT => X, IMAGE_ZOOM => X, etc.));
define("IMAGE_FORCE_COMPLIANCE",41);	// Do not let records be set to visible unless they comply with image dimension requirements.
define("IMAGE_EXRAS_ACCESS",	42);	// Do not allow image to be cropped or deleted directly from from.
define("IMAGE_PREVIEW",		43);	// Create a preview of the image that can be clicked to reveal the file upload input.
define("IMAGE_DEFAULT_PREVIEW",	44);	// If no file is uploaded, show this in the preview

// Link
define("LINK_TABLE",		45);
define("LINK_LABEL",		46);	// Defines field in the linked table that will identify the table. Usually 'title'
define("LINK_WHERE",		47);	// <%field_name%> can be used to dynamically insert the value of a field. Can be surrounded by 1 function call, e.g., strtolower(<%field_name%>)
define("KEYWORD_LIMIT",		48);
define("LINK_LIMIT",		48);
define("LINK_SORT",		49);
define("LINK_FIELD",		50);	// Defines link field that points TO the table (1 to N: implicit relationship)
define("LINK_OPTIONS",		51);	// LINK_NEW_ONLY | LINK_EXISTING_ONLY | LINK_EXPANDED
define("LINK_MAP_TABLE",	52);	// The table that holds the N to N map info.
define("LINK_MAP_SORT",		53);
define("LINK_LOCAL_KEY",	54);	// The id for records of *this* type in the map table
define("LINK_FOREIGN_KEY",	55);	// The id for record of the other type in the map table
define("LINK_MINIMUM",		56);	// Minimum # of associations. Can be used to force a default association.
define("LINK_MAXIMUM",		57);	// Maximum # of associations.
define("LINK_ATTACHMENT",	58);	// If the LINK_TABLE depends on another field in this table. LINK_TABLE => array("" => "user", ...), LINK_LABEL => array("" => first_name, ...). Just for LINK now (Not LINK_N_TO_N, etc.)
define("LINK_ADD_TEXT",		59);

// Files / Server File
define("FILE_PATH",		60);	// Where to store a file, complete path with filename, omitting extension. <%field_name%> can be used to dynamically insert the value of a field.  Can be surrounded by 1 function call, e.g., strtolower(<%field_name%>)
define("ROOT_DIRECTORY",	61);	// The folder that a SERVER_FILE browse starts off in
define("EXPLORE_PARENT",	62);	// Whether to allow exploring to parent directories

/* Field Attachment changes the visiblity of a given field in a form based on the value of another field. */
define("FIELD_ATTACHMENT",	63);	// A field name in this table whose value changes the appearance of this field.
define("FIELD_ATTACHMENT_VALUE",64);	// The value of the field specified FIELD_ATTACHMENT that makes this field visible
define("FIELD_ATTACHMENT_OPERATOR",	65);

/* Option Attachment changes the options provided in an ENUMERATION based on the value of another field. */
define("OPTION_ATTACHMENT",	66);
define("OPTION_ATTACHMENT_SOURCES",	67);	// An array of values to options

define("FIELD_RELEVANCE",	68);	// Whether or not this field is relevant. TOP_LEVEL_ONLY or SUBRECORD_ONLY
define("SUBTABLE_DEFAULT",	69);	// Default value for a subtable field. Specifies field of parent table to default to 
define("SESSION_DEFAULT",	70);	// 
define("SELECT_MULTIPLE",	71);	// ENUMERATION/LINK modifier - allow selection of multiple values;
define("SELECT_SIZE",		72);    // The number of options to show. SELECT_MULTIPLE must be set.
define("FIELD_GROUP",		73);	// Divide table up into multiple "grouped" forms.

define("FIELD_CHANGE",		74);
define("FIELD_CLICK",		75);
define("FIELD_DOUBLE_CLICK",	76);
define("FIELD_FOCUS",		77);

define("FIELD_BLUR",		78);
define("FIELD_MOUSEOVER",	79);
define("FIELD_MOUSEOUT",	80);
define("FIELD_KEYUP",		81);

define("FIELD_NO_CLEAR",	82);
define("FIELD_NO_LABEL",	83);

define("ANNOTATION_RESIZABLE",	84);	// Whether or not an annotation can be resized
define("ANNOTATION_WIDTH",	85);	// If numeric, the fixed width of this annotation. If a string, the field that should hold the width value
define("ANNOTATION_HEIGHT",	86);	// "
define("ANNOTATION_X",		87);	// "
define("ANNOTATION_Y",		88);	// "

define("STRENGTH_INDICATOR",	89);	// TODO: Provide a strength indicator for a password
define("STRENGTH_REQUIREMENT",	90);	// TODO: Require a minimum strength for a password
define("FIELD_NON_UNIQUE_CALLBACK", 91);	// The callback to invoke when a duplicate value is detected.

define("FIELD_CLASS",		94);	// The HTML class to apply to the input
define("FIELD_REQUIRED_INDICATOR", 95);	// '*' By default
define("FIELD_ERROR_MESSAGE",	96);	// Provide a custom error message for when a field value is invalid or incomplete.
define("FIELD_PREVIEW",		97);	// Set to 0 to prevent field from appearing in preview rows
define("FIELD_NOTES",		98);
define("FIELD_HELP",		99);

$FIELD_MODIFIERS = array(
		FIELD_NAME		=> "FIELD_NAME",
		FIELD_TYPE		=> "FIELD_TYPE",
		FIELD_LABEL		=> "FIELD_LABEL",
		FIELD_REQUIRED		=> "FIELD_REQUIRED",
		FIELD_DEFAULT		=> "FIELD_DEFAULT",
		FIELD_VALIDATION	=> "FIELD_VALIDATION",
		FIELD_ACCESS		=> "FIELD_ACCESS",
		FIELD_UNIQUE		=> "FIELD_UNIQUE",
		FIELD_OPTIONS		=> "FIELD_OPTIONS",
		FIELD_EXTENSIONS	=> "FIELD_EXTENSIONS",
		LINK_TABLE		=> "LINK_TABLE",
		SUBFORM_TABLE		=> "SUBFORM_TABLE",
		IMAGE_HEIGHT		=> "IMAGE_HEIGHT",
		IMAGE_WIDTH		=> "IMAGE_WIDTH",
		IMAGE_MAX_HEIGHT	=> "IMAGE_MAX_HEIGHT",
		IMAGE_MAX_SIZE		=> "IMAGE_MAX_SIZE",
		IMAGE_MAX_WIDTH		=> "IMAGE_MAX_WIDTH",
		IMAGE_MIN_HEIGHT	=> "IMAGE_MIN_HEIGHT",
		IMAGE_MIN_WIDTH		=> "IMAGE_MIN_WIDTH",
		IMAGE_OPTIMUM_HEIGHT	=> "IMAGE_OPTIMUM_HEIGHT",
		IMAGE_OPTIMUM_WIDTH	=> "IMAGE_OPTIMUM_WIDTH",
		IMAGE_ASPECT_RATIO	=> "IMAGE_ASPECT_RATIO",
		IMAGE_ASPECT_DELTA	=> "IMAGE_ASPECT_DELTA",
		LINK_LABEL		=> "LINK_LABEL",
		LINK_WHERE		=> "LINK_WHERE",
		LINK_LIMIT		=> "LINK_LIMIT",
		LINK_SORT		=> "LINK_SORT",
		LINK_FIELD		=> "LINK_FIELD",
		FIELD_ATTACHMENT	=> "FIELD_ATTACHMENT",
		FIELD_ATTACHMENT_VALUE	=> "FIELD_ATTACHMENT_VALUE",
		OPTION_ATTACHMENT	=> "OPTION_ATTACHMENT",
		OPTION_ATTACHMENT_SOURCES		=> "OPTION_ATTACHMENT_SOURCES",
		FIELD_NOTES		=> "FIELD_NOTES");

/*******************/
/*  Field Options  */
/*******************/

define("SEPARATE_DATE_COMPONENTS",	1);
define("MINIMUM_AGE",			2);

$FIELD_OPTIONS = array(
		SEPARATE_DATE_COMPONENTS	=> "SEPARATE_DATE_COMPONENTS",
		MINIMUM_AGE	=> "MINIMUM_AGE"
	);

define("NOT_REQUIRED",	0);
define("REQUIRED",	1);
define("OPTIONAL",	2);	// OPTIONAL / HIDDEN / OPTIONAL_HIDDEN: all the same.
define("OPTIONAL_HIDDEN",	2);
define("GENERATED",	3);	// Not required but will be generated if left unfilled.
define("HIDDEN",	4);
define("VALIDATED",	5);	// Not required, but if present, must be correct.
define("USER_DEFAULT",	6);	// Hidden, except from ADMINISTRATOR. Value is specified by a field of the current user. LINK_FIELD specifies property on 'user' table.

$REQUIRED_OPTIONS = array(NOT_REQUIRED => "NOT_REQUIRED", REQUIRED => "REQUIRED", OPTIONAL_HIDDEN => "OPTIONAL_HIDDEN");

define("DEFAULT_RELEVANCE",	0);
define("TOP_LEVEL_ONLY",	1);
define("SUBRECORD_ONLY",	2);

$RELEVANCE_OPTIONS = array();

$ROW_WIDTHS = array(
		TEXT_ARRAY => 0,
		BOOL => 100,
		COLOR => 50,
		COPY => 200,
		CREDIT_CARD => 0,
		DATE => 100,
		DATETIME => 150,
		EMAIL => 150,
		ENUMERATION => 200,
		HIDDEN_VALUE => 0,
		HTML_COPY => 200,
		ID => 0,
		IMAGE => 100,
		IMAGE_EXCERPT => 100,
		KEYWORDS => 150,
		LINK => 200,
		LINK_MUTUAL => 50,
		LINK_N_TO_N => 100,
		LINK_ONE_TO_N => 100,
		LINK_SUBTABLE => 100,
		MULTI_LINK => 200,
		NUMBER => 75,
		TINY_NUMBER => 50,
		MONEY => 75,
		KMONEY => 75,
		GMONEY => 75,
		NAME => 150,
		PASSWORD => 0,
		SENTENCE => 200,
		SERVER_FILE => 50,
		SERVER_FOLDER => 50,
		SET => 200,
		TELEPHONE_NUMBER => 75,
		TIME => 100,
		TIMESTAMP => 100,
		URL => 150,
		VIDEO => 0,
		YEAR => 50
	);

$DATATYPES = array(
		TEXT_ARRAY => "TEXT",	// To store serialized version of array.
		BOOL => "BOOL",
		COLOR => "CHAR(6)",
		COPY => "TEXT",
		CREDIT_CARD => "CHAR(128)",
		DATE => "DATE",
		DATETIME => "DATETIME",
		EMAIL => "CHAR(64)",
		ENUMERATION => "CHAR(32)",	// "INT",	// CHAR allows for Alpha keys to Enumerations, e.g.: "IL" => "Illinois"
		FILE => "CHAR(128)",
		HIDDEN_VALUE => "CHAR(255)",
		HTML_COPY => "TEXT",
		ID => "SERIAL",
		IMAGE => "CHAR(128)",
		IMAGES => "TEXT",
		IMAGE_EXCERPT => "CHAR(255)",
		IMAGE_SELECT => "CHAR(255)",
/* 		JEKILL_CONTENT => "CHAR(255)", */
		KEYWORDS => "CHAR(255)",
		LINK => "BIGINT",
		LINK_SUBTABLE => "BIGINT",
		MULTI_LINK => "CHAR(255)",
		MILITIME => "BIGINT",
		NAME => "CHAR(64)",
		TINY_NUMBER => "TINYINT",
		MONEY => "DECIMAL(5, 2)",
		KMONEY => "DECIMAL(8, 2)",
		GMONEY => "DECIMAL(11, 2)",
		NUMBER => "INT",
		PASSWORD => "CHAR(80)",
		MD5_PASSWORD => "CHAR(64)",
		RANDOM_PIN => "CHAR(32)",
		RICH_URL_ID => "CHAR(255)",
		SENTENCE => "VARCHAR(255)",
		SERVER_FILE => "CHAR(128)",
		SET => "SET",
		SUGGEST => "CHAR(64)",
		TELEPHONE_NUMBER => "BIGINT",
		TIME => "TIME",
		TIMESTAMP => "BIGINT",
		URL => "CHAR(255)",
		VIDEO => "CHAR(128)",
		YEAR => "YEAR"
	);

$DEFAULT_VALIDATION = array(
		BOOL => "isChecked",
		COLOR => "isColor",
		COPY => "notEmpty",
		CREDIT_CARD => "isValidCreditCard",
		DATE => "isValidDate",
		EMAIL => "isValidEmail",
		ENUMERATION => "notEmpty",
		FILE => "notEmpty",
		HTML_COPY => "notEmpty",
		IMAGE => "notEmpty",
/* 		JEKILL_CONTENT => "isValidURL", */
		LINK => "notEmpty",
		NAME => "notEmpty",
		MONEY => "isNonZero",
		KMONEY => "isNonZero",
		GMONEY => "isNonZero",
		NUMBER => "isNumber",
		TINY_NUMBER => "isNumber",
		PASSWORD => "isValidPassword",
		MD5_PASSWORD => "isValidPassword",
		SENTENCE => "notEmpty",
		TELEPHONE_NUMBER => "isValidTelephone",
		TIMESTAMP => "isValidDateAndTime",
		URL => "isValidURL",
		VIDEO => "notEmpty"
	);

/* Accessibility / User Types */
define("USER", 		0);
define("CONTRIBUTER",	1);
define("WRITER",	2);
define("EDITOR",	3);
define("ADMINISTRATOR",	4);
define("HIDDEN",	9);
define("DIETY",		10);

$FIELD_PERMISSIONS = array(
			USER		=> "USER",
			CONTRIBUTER	=> "CONTRIBUTER",
			WRITER		=> "WRITER",
			EDITOR		=> "EDITOR",
			ADMINISTRATOR	=> "ADMINISTRATOR",
			HIDDEN		=> "HIDDEN",
			DIETY		=> "DIETY"
		);

$PERMISSIONS = array(USER => "User", CONTRIBUTER => "Contributer", WRITER => "Writer", EDITOR => "Editor", ADMINISTRATOR => "Administrator");

$SEARCH_OPERATORS = array("=" => " = ", "LIKE" => "Like", ">" => " &gt; ", ">=" => " &ge; ", "<" => " &lt; ", "<=" => " &le; ");

define("READ_WRITE",	0);
define("READ_ONLY",	1);
define("DEPRECATED",	2);

define("AM",	0);
define("PM",	1);

$AM_PM = array(AM => "a.m.", PM => "p.m.");

?>
