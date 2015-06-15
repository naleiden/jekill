<?php

error_reporting(0);

/* spl_autoload_register(function ($classname) {
	$classname = preg_replace("/([a-z]{1})([A-Z]{1})/", "$1_$2", $classname);

	require_once("php/" . strtolower($classname) . ".php");
}); */

$DATABASE_NAME = "drawittoknowit";

if ($_SERVER['REMOTE_ADDR'] == "127.0.0.1") {
	$DATABASE_HOST = "localhost";
	$DATABASE_USER = "root";
	$DATABASE_PASSWORD = "";

    define("SITE_PROTOCOL", "http://");
    define("SITE_HOST",     "drawittoknowit.localhost");
}
else {
	$DATABASE_NAME = "ditki";
	$DATABASE_HOST = "localhost";
	$DATABASE_USER = "ditki-web";
	$DATABASE_PASSWORD = "Ad5r6p^9";
}

define("FACEBOOK_APP_ID", 181726328568984);

/* Formats */
define("JSON", "json");
define("HTML", "html");
define("PDF",   "pdf");
define("XML",   "xml");

define("EMAIL_REGEX",	"(.)+@([a-z1-9][a-z0-9\-])(\.[a-z]{2, 3}){1,2}");	// Just look for the '@' and the '.' 
define("IP_REGEX",	"/([1-2]?[0-9]?[0-9]\.){3}[1-2][0-9]?[0-9]/");	// TODO: Use lookahead to restrict IP bytes to 0-255 (currently allows 0-299)
define("IP_MASK_REGEX",	"/([1-2]?[0-9]?[0-9])(\.[1-2]?[0-9]?[0-9]){0,3}/");	// Only 1-3 bytes of IP
define("NAME_REGEX",	"/[a-z\s'\-]{2,}/i");
define("URL_ID_REGEX", "/[a-z0-9\-]+/i");
define("DATE_REGEX", "/[0-1]?[0-9]\/[0-3]?[0-9]\/[1-2][0-9]{3}/");

define("MOBILE_HOST", ($_SERVER['MOBILE_HOST']) ? $_SERVER['MOBILE_HOST'] : "m.drawittoknowit.com");
define("MOBILE_OVERRIDE_COOKIE",	"_ditki_mobile_override");

define("FIFTEEN_MINUTE_SECONDS", 900);
define("HOUR_SECONDS",	3600);
define("DAY_SECONDS",	86400);
define("WEEK_SECONDS",  604800);
define("THIRTY_DAY_SECONDS", 2592000);
define("YEAR_SECONDS",  31536000);
define("FIVE_YEAR_SECONDS", 157680000);

/* Salts */	// (Kind of a hack, but enough security for email for auth hashes.)
define("IOS_AUTH_SALT",		"@ll0w_I05_@cc355!");
define("VIDEO_HASH_SALT",	"S@ltyV1d305!");
define("REGISTRATION_SALT",	"R3g15t3r3dUp!");
define("REFERRAL_COUPON_SALT",  "R3f3r3v3ry0n3!");

define("PAGEVIEW_GRACE_PERIOD",		900);	// 15 minutes: The amount of time two pageviews of the same page are deemed to be the same.

/* Errors */
define("INCORRECT_LOGIN", 0);

$DEFAULT_MAX_PAGE_DISPLAY = 20;

$DEFAULT_MAX_RESULTS = 15;

$CONTROL_PANEL_WIDTH = 900;

$COMPANY_NAME = "Draw It To Know It";
$COMPANY_DOMAIN = "DrawItToKnowIt.com";
$COMPANY_URL = "http://www.drawittoknowit.com";

$CONTACT_SUBJECTS = array("" => "&lt; Please Select a Subject &gt;", 
						"Having Trouble with my Subscription" => "Having Trouble with my Subscription",
						"Residency Program Subscription" => "Residency Program Subscription",
						"Institutional Subscription" => "Institutional Subscription",
						"Questions About the Website" => "Questions About the Website",
						"Request a Guest Lecture" => "Request a Guest Lecture",
						// "Provide Free Access" => "Provide Free Access",
						"Other" => "Other");

$LEAD_SOURCES = array("" => "< Select a Lead Source >", "professor" => "Professor", "friend" => "Friend", "search_engine" => "Search engine (e.g., Google, Bing)", "you_tube" => "YouTube tutorial", "facebook" => "Facebook", "textbook" => "Draw It To Know It textbook", "itunes" => "iTunes", "other" => "Other");
$OCCUPATIONS = array("" => "< Select an Occupation >", "undergraduate" => "Undergraduate Student", "grad_student" => "Graduate Student", "medical_student" => "Medical Student", "medical_resident" => "Medical Resident", "nursing_student" => "Nursing Student", "other" => "Other");

define("MEDICAL_SCIENCES",		"medical-sciences");
define("ANATOMICAL_SCIENCES",	"anatomical-sciences");
define("BIOLOGICAL_SCIENCES",	"biological-sciences");
define("PHYSICAL_SCIENCES",		"physical-sciences");

$DIVISIONS = array(/* "" => "< Select a Division >", */ MEDICAL_SCIENCES => "Medical Sciences", ANATOMICAL_SCIENCES => "Anatomical Sciences", BIOLOGICAL_SCIENCES => "Biological Sciences", PHYSICAL_SCIENCES => "Physical Sciences");

define("NEUROANATOMY",	"neuroanatomy");
define("GROSS_ANATOMY",	"gross-anatomy");
define("ANATOMY_PHYSIOLOGY", "anatomy-physiology");
define("PHYSIOLOGY",	"physiology");
define("CELL_BIOLOGY_HISTOLOGY",	"cell-biology-histology");

$MEDICAL_SCIENCE_DISCIPLINES = array(NEUROANATOMY => "Neuroanatomy", GROSS_ANATOMY => "Gross Anatomy",
									ANATOMY_PHYSIOLOGY => "Anatomy & Physiology", PHYSIOLOGY => "Physiology",
									CELL_BIOLOGY_HISTOLOGY => "Cell Biology/Histology");

// An individual
define("AMBASSADOR_COUPON_MODE",	"coupon-mode");
define("AMBASSADOR_REFERRAL_MODE",	"referral-mode");
define("AMBASSADOR_MODE",	AMBASSADOR_REFERRAL_MODE);

define("CODE_BASED_COUPON",     "code-based");
define("REFERRAL_BASED_COUPON", "referral-based");

$COUPON_TYPES = array(CODE_BASED_COUPON => "Code-based", REFERRAL_BASED_COUPON => "Referral-based");

define("REFERRAL_EMAIL",    "email");
define("REFERRAL_FACEBOOK", "facebook");
define("REFERRAL_LINK",     "link");
define("REFERRAL_TWITTER",  "twitter");

$REFERRAL_SOURCES = array(REFERRAL_EMAIL => "E-mail", REFERRAL_FACEBOOK => "Facebook", REFERRAL_LINK => "Link", REFERRAL_TWITTER => "Twitter");

define("AMBASSADOR_CODE",		"code-based");
define("AMBASSADOR_REFERRAL",	"referral-based");
$AMBASSADOR_TYPES = array(AMBASSADOR_CODE => "Coupon-based", AMBASSADOR_REFERRAL => "Referral-based (Encoded Link)");

define("INSTITUTIONAL_ADMINISTRATOR", "administrator");
define("COURSE_DIRECTOR", "course-director");

$INSTITUTIONAL_USER_TYPES = array(INSTITUTIONAL_ADMINISTRATOR => "Administrator", COURSE_DIRECTOR => "Course Director");

define("INDIVIDUAL_SUBSCRIPTION",	"individual");
define("GROUP_SUBSCRIPTION",		"group");
define("GROUP_MEMBER_SUBSCRIPTION",	"group_member");
define("SITE_LICENSE",				"site_license");

$SUBSCRIPTION_PURCHASE_TYPES = array(INDIVIDUAL_SUBSCRIPTION => "Individual Subscription", GROUP_SUBSCRIPTION => "Group Subscription", GROUP_MEMBER_SUBSCRIPTION => "Group Member Subscription");	// , SITE_LICENSE => "Site License Subscription");

$GROUP_SUBSCRIPTION_TYPES = array(GROUP_SUBSCRIPTION => "Group Subscription", SITE_LICENSE => "Site License");

define("GROUP_SMALL",	"group-license-small");
define("GROUP_MEDIUM",	"group-license-medium");
define("GROUP_LARGE",	"group-license-large");
define("GROUP_LIBRARY",	"group-library");
define("GROUP_INSTITUTION",	"group-institution");
define("GROUP_RESIDENCY",	"group-license-residency");
define("GROUP_CLERKSHIP",	"group-license-clerkship");
define("GROUP_RESIDENCY_CLERKSHIP",	"group-residency-clerkship");
define("GROUP_HOSPITAL", "group-hospital");

$GROUP_TYPES = array(
	GROUP_SMALL => "Group License",	// "Small Group License"
	// GROUP_MEDIUM => "Medium Group License",
	// GROUP_LARGE => "Large Group License",
	GROUP_INSTITUTION => "Institutional License",
	GROUP_LIBRARY => "Library License",
	GROUP_RESIDENCY => "Residency License",
	GROUP_CLERKSHIP => "Clerkship License",
	GROUP_RESIDENCY_CLERKSHIP => "Residency &amp; Clerkship Licenses",
	GROUP_HOSPITAL => "Hospital License"
);

$GROUP_PRICING = array(
	/*
	GROUP_SMALL => "(5-50 users) - $10 per user",
	GROUP_MEDIUM => "(51-100 users) - $7 per user",
	GROUP_LARGE => "(101-400 users) - $5 per user",
	GROUP_LIBRARY => " - see text for pricing",
	GROUP_RESIDENCY_CLERKSHIP => " - see text for pricing"
	*/
);

$GROUP_DESCRIPTIONS = array(
	GROUP_SMALL => "Access to Website for One Year - $15/student<br/>
Access to Website for One Year + Lifetime Access to iPhone/iPad App - $20/student<br/>
Faculty are Free</br>
We will provide you with a Group Code for access.",
	GROUP_INSTITUTION => "Access to Website for One Year - $3500/year<br/>
Access to Website for One Year + Lifetime Access to iPhone/iPad App - $4000/year<br/>
We use IP authentication for Access, which allows for Registration for Off-Site Web Access & Mobile Access.",
	GROUP_RESIDENCY => "Access to Website for One Year - $15/resident<br/>
Lifetime access to the Website + Lifetime Access to iPhone/iPad App - $25/resident<br/>
Faculty are Free<br/>
We will provide you with a Group Code for access.",
	GROUP_CLERKSHIP => "Access to Website for One Year - $400/year<br/>
Access to Website for One Year + Lifetime Access to iPhone/iPad App - $500/year<br/>
Faculty are Free<br/>
We will provide you with a Group Code for access.",
	GROUP_HOSPITAL => "Access to Website for One Year - $500/year<br/>
Access to Website for One Year + Lifetime Access to iPhone/iPad App - $750/year<br/>
We use IP authentication for Access, which allows for Registration for Off-Site Web Access & Mobile Access."
/*	
	GROUP_SMALL => "For small groups, departments, programs, or classrooms.",
	GROUP_MEDIUM => "For medium-sized groups, departments, programs, or classrooms",
	GROUP_LARGE => "For large groups, departments, programs, or classrooms",
	GROUP_LIBRARY => "We make IP Authenticated access available for libraries. For Hospital libraries, we charge a flat fee of $500 per year. For medical school libraries and other academic libraries, we charge $4 per anticipated user, with a maximum fee of $2,800 per year.",	 // we charge a flat fee of $2,800 per year unless the total number of anticipated users is less than 700, in which case we charge $4 per anticipated user.",
	GROUP_RESIDENCY_CLERKSHIP => "<div><a href=\"/group-license/group-license-residency.html\">Residency license</a> provides a one year subscription to DrawItToKnowIt.com for each neurology resident and any interested faculty; cost is $195 per program. For small programs, consider purchasing a <a href=\"/group-license/group-license-small.html\">small group license</a> instead.</div><div><a href=\"/group-license/group-license-clerkship.html\">Clerkship license</a> provides a one month subscription for each medical student during his/her neurology rotation; cost is $2 per student with a maximum of $285 per program (faculty are free).</div>"
*/
);

define("MIN_PASSING_GRADE",		0.7);

define("SUPPLEMENT",	1);
define("EXAM",		2);

$SUPPLEMENT_TYPES = array(SUPPLEMENT => "Supplement", EXAM => "Exam");

define("SUBJECT_SELF_ASSESSMENT",	"self-assessment");
define("POST_TUTORIAL_QUIZ",		"lesson-quiz");
define("_EXAM",					"");

$TEST_TYPES = array("" => "&lt; Select a Test Type &gt;",
					SUBJECT_SELF_ASSESSMENT => "Subject Self-Assessment",
					POST_TUTORIAL_QUIZ => "Post Tutorial Quiz",
					_EXAM => ""
				);

define("QUESTION_HEADING",	0);
define("TRUE_OR_FALSE",		1);
define("MULTIPLE_CHOICE",	2);
define("RATE_1_5",			3);
define("FREE_RESPONSE",		4);
define("SHORT_ANSWER",		5);

define("A", "a");
define("B", "b");
define("C", "c");
define("D", "d");
define("E", "e");
define("F", "f");

define("OPTIONS_INLINE",	"inline");
define("OPTIONS_ROWS",		"rows");

$OPTION_DISPLAY_TYPES = array(OPTIONS_ROWS => "Display options in rows", OPTIONS_INLINE => "Display options inline");

// RANK_1_5 types.
define("RATE_AGREEMENT",		"agreement");
define("RATE_FREQUENCY",		"frequency");
define("RATE_IMPORTANCE",		"importance");
define("RATE_LIKELIHOOD",		"likelihood");
define("RATE_QUALITY",			"quality");
define("RATE_SATISFACTION",		"satisfaction");
define("RATE_CUSTOM",			"custom");

$AGREEMENT_OPTIONS = array(1 => "Strongly Disagree", 2 => "Disagree", 3 => "No Opinion", 4 => "Agree", 5 => "Strongly Agree");
$FREQUENCY_OPTIONS = array(1 => "Never", 2 => "Rarely", 3 => "Sometimes", 4 => "Often", 5 => "Always");
$QUALITY_OPTIONS = array(1 => "Poor", 2 => "Fair", 3 => "Good", 4 => "Very Good", 5 => "Excellent");
$SATISFACTION_OPTIONS = array(1 => "Extremely Dissatisfied", 2 => "Dissatisfied", 3 => "Neutral", 4 => "Satisfied", 5 => "Extremely Satisfied");
$LIKELIHOOD_OPTIONS = array(1 => "Extremely Unlikely", 2 => "Unlikely", 3 => "Probable", 4 => "Likely", 5 => "Very Likely");
$IMPORTANCE_OPTIONS = array(1 => "Unimportant", 2 => "Slightly important", 3 => "Important", 4 => "Very important", 5 => "Critical");

$RATE_FORMATS = array(RATE_1_5 => "Rate 1-5",
					RATE_AGREEMENT => "Rate Agreement",
					RATE_FREQUENCY => "Rate Frequency",
					RATE_IMPORTANCE => "Rate Importance",
					RATE_LIKELIHOOD => "Rate Likelihood",
					RATE_QUALITY => "Rate Quality",
					RATE_SATISFACTION => "Rate Satisfaction",
					RATE_CUSTOM => "Custom Options");
$RATE_OPTION_MAP = array(RATE_1_5 => "",
						RATE_AGREEMENT => $AGREEMENT_OPTIONS,
						RATE_FREQUENCY => $FREQUENCY_OPTIONS,
						RATE_IMPORTANCE => $IMPORTANCE_OPTIONS,
						RATE_LIKELIHOOD => $LIKELIHOOD_OPTIONS,
						RATE_QUALITY => $QUALITY_OPTIONS,
						RATE_SATISFACTION => $SATISFACTION_OPTIONS);

$QUESTION_FORMATS = array(
						TRUE_OR_FALSE => "True or False",
						MULTIPLE_CHOICE => "Multiple Choice",
						RATE_1_5 => "Rating 1-5",
						SHORT_ANSWER => "Short Answer",
						FREE_RESPONSE => "Free Response",
						QUESTION_HEADING => "Question Heading"
					);

$TRUE_FALSE_ANSWERS = array(1 => "True", 0 => "False");
$MULTIPLE_CHOICE_ANSWERS = array(A => "a.)", B => "b.)", C => "c.)", D => "d.)", E => "e.)", F => "f.)");

$ANSWER_OPTIONS = array(TRUE_OR_FALSE => $TRUE_FALSE_ANSWERS, MULTIPLE_CHOICE => $MULTIPLE_CHOICE_ANSWERS);


define("SCHEDULE_IMMEDIATE",		"signup");
define("SCHEDULE_DATE",				"date");
define("SCHEDULE_ANNUAL_DATE",		"annual_date");
define("SCHEDULE_SIGNUP_OFFSET",	"signup_offset");

$SCHEDULE_TYPES = array(SCHEDULE_IMMEDIATE => "Immediately/On signup", SCHEDULE_SIGNUP_OFFSET => "Offset from Signup", SCHEDULE_DATE => "Specific Date", SCHEDULE_ANNUAL_DATE => "Annual Date");

$SEARCH_OPERATORS = array("=" => " = ", "LIKE" => "Like", ">" => " &gt; ", ">=" => " &ge; ", "<" => " &lt; ", "<=" => " &le; ");

define("GRACE_HOUR",		3600);
define("GRACE_HALF_DAY",	43200);
define("GRACE_DAY",			86400);
define("GRACE_THREE_DAY",	259200);
define("GRACE_WEEK",		604800);
define("GRACE_TWO_WEEK",	1209600);
define("GRACE_TEN_DAY",		864000);
define("GRACE_MONTH",		2592000);
define("GRACE_THREE_MONTH",	7862400);


$SURVEY_GRACE_PERIODS = array(GRACE_HOUR => "1 Hour", GRACE_HALF_DAY => "12 Hours", GRACE_DAY => "1 Day", GRACE_THREE_DAY => "3 Days", GRACE_WEEK => "1 Week",
							GRACE_TEN_DAY => "10 Days", GRACE_TWO_WEEK => "Two Weeks", GRACE_MONTH => "1 Month", GRACE_THREE_MONTH => "3 Months");

$SCHEDULE_OFFSETS = $SURVEY_GRACE_PERIODS;

/* Site License Types */

define("INTRANET_REFERRAL",	1);
define("IP_RANGE",		2);

$SITE_LICENSE_TYPES = array("" => "&lt; Select a Site Type &gt;", INTRANET_REFERRAL => "Intranet Referral", IP_RANGE => "IP Range");

define("IP_MASK",	0);
$IP_RANGE_TYPES = array(IP_MASK => "IP Mask", IP_RANGE => "IP Range");

define("MUSCLE_GROUP",		0);
define("NERVE",			1);
define("NERVE_ROOT",		2);
define("PLEXUS_COMPONENT",	3);
define("ARTICULATION_SITE",	4);
define("ANATOMICAL_SITE",	5);
define("MUSCLE_ACTION",		6);
define("OTHER_ANATOMICAL",	7);
define("MUSCLE_DEPTH",		8);
define("SENSORY_COVERAGE",	9);

$ANATOMICAL_COMPONENTS = array(ANATOMICAL_SITE => "Anatomical Site", ARTICULATION_SITE => "Articulation Site", MUSCLE_ACTION => "Muscle Action", MUSCLE_DEPTH => "Muscle Depth", MUSCLE_GROUP => "Muscle Group", NERVE => "Nerve", NERVE_ROOT => "Nerve Root", PLEXUS_COMPONENT => "Plexus", SENSORY_COVERAGE => "Sensory Coverage", OTHER_ANATOMICAL => "Other");

define("UPPER_EXTREMITY",	1);
define("LOWER_EXTREMITY",	2);
define("HEAD_NECK",		3);
define("THORACOABDOMEN_BACK",	4);
define("PELVIS_PERINEUM",	5);

$ANATOMICAL_LOCI = array(UPPER_EXTREMITY => "Upper extremity",
				LOWER_EXTREMITY => "Lower extremity",
				HEAD_NECK => "Head and Neck",
				THORACOABDOMEN_BACK => "Thoracoabdomen and Back",
				PELVIS_PERINEUM => "Pelvis and Perineum");

$ANATOMICAL_LOCI_URL_MAP = array("upper-extremity" => UPPER_EXTREMITY,
				"lower-extremity" => LOWER_EXTREMITY,
				"head-and-neck" => HEAD_NECK,
				"thoracoabdomen-and-back" => THORACOABDOMEN_BACK,
				"pelvis-and-perineum" => PELVIS_PERINEUM);

$ANATOMICAL_LOCI_MAP = array_flip($ANATOMICAL_LOCI_URL_MAP);

define("INTERACTIVE_TUTORIAL",	1);
define("CLINICAL_VIGNETTE",	2);
define("RADIOGRAPHIC_IMAGES",	3);

$TUTORIAL_TYPES = array("" => "&lt; Select a Tutorial Type &gt;", INTERACTIVE_TUTORIAL => "Interactive Tutorial", CLINICAL_VIGNETTE => "Clinical Vignette", RADIOGRAPHIC_IMAGES => "Radiographic Images");

define("LESSON_SHORT",		"short");
define("LESSON_STANDARD",	"standard");
define("LESSON_ADVANCED",	"advanced");

$LESSON_TIERS = array("" => "&lt; Select a Tier &gt;", LESSON_SHORT => "Short", LESSON_STANDARD => "Intermediate", LESSON_ADVANCED => "Advanced");

define("STUDENT_MEDICAL",		"medical");
define("STUDENT_ALLIED_HEALTH",	"allied_health");
define("STUDENT_NURSING",		"nursing");
define("STUDENT_NEUROLOGY_RESIDENT", "resident");
define("INSTITUTION_SPECIFIC", "institution");

$STUDENT_TYPES = array("" => "&lt; Select a Student Type &gt;",
						STUDENT_NEUROLOGY_RESIDENT => "Neurology Resident",
						STUDENT_MEDICAL => "Medical Student",
						STUDENT_ALLIED_HEALTH => "Allied Health Student",
						STUDENT_NURSING => "Nursing Student",
						INSTITUTION_SPECIFIC => "Institutional Curricula");

$STUDENT_DESCRIPTIONS = array(STUDENT_MEDICAL => "Ideal for Medical, Osteopathic, Dental, Chiropractic, and Electrodiagnostic Students",
								STUDENT_ALLIED_HEALTH => "Ideal for Rehabilitation Therapy (Physical, Occupational, Speech), Advanced Nursing, and Electrodiagnostic Technician Students",
								STUDENT_NURSING => "Ideal for Nursing Students or anyone looking for an introduction to neuroanatomy",
								STUDENT_NEUROLOGY_RESIDENT => "Ideal for Neurology Residents, Graduate Anatomy Students, and Graduate Neuroscience Students"
							);

define("STUDY_PLAN_COMPREHENSIVE",	"comprehensive");
define("STUDY_PLAN_HIGH_YIELD",		"high_yield");

$STUDY_PLAN_TYPES = array("" => "&lt; Select a Plan Type &gt;", STUDY_PLAN_COMPREHENSIVE => "Comprehensive", STUDY_PLAN_HIGH_YIELD => "Crash Course");

// Assume INTERACTIVE_TUTORIAL from above
define("FLASH_RADIOGRAPH",	2);

$RADIOGRAPH_TYPES = array("" => "&lt; Select a Type &gt;", FLASH_RADIOGRAPH => "Flash Radiograph", INTERACTIVE_TUTORIAL => "Interactive Tutorial");

define("AXIAL",		1);
define("CORONAL",	2);
define("SAGITTAL",	3);

$PERSPECTIVES = array("" => "&lt; Select a Perspective &gt;", AXIAL => "Axial", CORONAL => "Coronal", SAGITTAL => "Sagittal");

define("CUSTOMER_ID", "DITKI_CUSTOMER_user_ID");
define("CUSTOMER_ANONYMOUS_ID", "DITKI_ANONYMOUS_user_ID");

$LOGO_URL = "images/logo.jpg";
$LOGIN_ID = "DITKI_user_ID";
$CUSTOMER_ID = CUSTOMER_ID;

$AXIAL_DEPTHS = array("" => "&lt; Select a Depth &gt;", 1 => 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23);
$CORONAL_DEPTHS = array("" => "&lt; Select a Depth &gt;", 1 => 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35);
$SAGITTAL_DEPTHS = array("" => "&lt; Select a Depth &gt;", 1 => 1, 2, 3, 4, 5, 6, 7, 8, 9);

$PERSPECTIVE_DEPTH_MAP = array(AXIAL => $AXIAL_DEPTHS, CORONAL => $CORONAL_DEPTHS, SAGITTAL => $SAGITTAL_DEPTHS);

define("RADIOGRAPH_BASIC",		"basic");
define("RADIOGRAPH_SUPERFICIAL","superficial");
define("RADIOGRAPH_DEEP",		"deep");
define("RADIOGRAPH_SPECIAL",	"special");
define("RADIOGRAPH_CSF",		"csf");

$RADIOGRAPH_MODES = array("" => "", RADIOGRAPH_BASIC => "Basic", RADIOGRAPH_SUPERFICIAL => "Superficial", RADIOGRAPH_DEEP => "Deep", RADIOGRAPH_SPECIAL => "Special", RADIOGRAPH_CSF => "CSF");

define("TERM_ANNOTATION",	0);
define("CATEGORY_ANNOTATION",	1);
$ANNOTATION_TYPES = array(TERM_ANNOTATION => "Term", CATEGORY_ANNOTATION => "Category");

define("PHYSICAL_PRODUCT",	1);
define("DOWNLOAD",		2);
define("SUBSCRIPTION",		3);
define("SUBSCRIPTION_RENEWAL",	4);
// define("INDIVIDUAL_RECORD",	5);
$PRODUCT_TYPES = array(
		"" => "&lt; Select a Product Type &gt;",
		PHYSICAL_PRODUCT => "Physical Product",
		DOWNLOAD => "Downloadable Product",
		SUBSCRIPTION => "Subscription",
		// SUBSCRIPTION_RENEWAL => "Subscription Renewal",
		// INDIVIDUAL_RECORD => "Individual Record"
	);


/* Subscription Types */
define("MATERIAL_GROUP",	0);
define("INDIVIDUAL_RECORD",	1);
$SUBSCRIPTION_TYPES = array(
			MATERIAL_GROUP => "All Records/Tutorials",
			INDIVIDUAL_RECORD => "Individual Lesson/Tutorial"
		);

define("SUBSCRIPTION_DAY",			86400);
define("SUBSCRIPTION_THREE_DAY",	259200);
define("SUBSCRIPTION_WEEK",			604800);
define("SUBSCRIPTION_TWO_WEEK",		1209600);
define("SUBSCRIPTION_TEN_DAY",		864000);
define("SUBSCRIPTION_MONTH",		2592000);
define("SUBSCRIPTION_THREE_MONTH",	7862400);
define("SUBSCRIPTION_SEMESTER",		10540800);
define("SUBSCRIPTION_YEAR",			31536000);
define("SUBSCRIPTION_FIVE_YEAR",	157680000);
define("SUBSCRIPTION_UNLIMITED",	-1);
$SUBSCRIPTION_DURATIONS = array(
			"" => "&lt; Select a Duration &gt;",
			SUBSCRIPTION_DAY => "24 Hour",
			SUBSCRIPTION_THREE_DAY => "Three Day",
			SUBSCRIPTION_WEEK => "One Week",
			SUBSCRIPTION_TWO_WEEK => "Two Weeks",
			SUBSCRIPTION_TEN_DAY => "10 Day",
			SUBSCRIPTION_MONTH => "30 Day",
			SUBSCRIPTION_THREE_MONTH => "3-Month",
			SUBSCRIPTION_SEMESTER => "4-Month",
			SUBSCRIPTION_YEAR => "One Year",
			SUBSCRIPTION_FIVE_YEAR => "Five Year",
			SUBSCRIPTION_UNLIMITED => "Lifetime"
		);

$SUBSCRIPTION_SECONDS = array(
			SUBSCRIPTION_DAY => 86400,
			SUBSCRIPTION_THREE_DAY => 259200,
			SUBSCRIPTION_WEEK => 604800,
			SUBSCRIPTION_TEN_DAY => 86400,
			SUBSCRIPTION_MONTH => 2592000,
			SUBSCRIPTION_SEMESTER => 10540800,
			SUBSCRIPTION_YEAR => 31536000
		);


define("DEBUG",		0);
define("LIVE",		1);
$DEBUGGING_OPTIONS = array(DEBUG => "Test Environment", LIVE => "Live");


define("MAXIMUM_ALLOWED_DRAWINGS_PER_LESSON", 4);

define("NARRATED_TUTORIALS",		1);
define("MUSCLE_NERVE_DIRECTORY",	2);
define("RADIOGRAPHIC_ATLAS",		3);
define("FLASH_CARD_LIBRARY",		4);
define("CLINICAL_VIGNETTES",		5);
define("DRAWING_TUTORIALS",		6);
define("IPHONE_IPAD_APP",		7);

$PREMIUM_MATERIALS = array(
			NARRATED_TUTORIALS => "Complete Library of Neuroanatomy Tutorials",
			MUSCLE_NERVE_DIRECTORY => "Muscle / Nerve Directory",
			RADIOGRAPHIC_ATLAS => "Brain Atlas",
			FLASH_CARD_LIBRARY => "Flash Card Library",
			CLINICAL_VIGNETTES => "Clinical Vignettes",
			IPHONE_IPAD_APP => "iPhone / iPad App"
		);

$PREMIUM_MATERIAL_TABLES = array(
			NARRATED_TUTORIALS => "lesson",
			MUSCLE_NERVE_DIRECTORY => "",
			RADIOGRAPHIC_ATLAS => "",
			FLASH_CARD_LIBRARY => "",
			CLINICAL_VIGNETTES => ""
		);


/* Cart Attributes */
define("RECORD_TYPE",	1);
define("RECORD_ID",	2);
define("QUANTITY",	3);


define("TOP_LEFT_CORNER",	1);
define("TOP_CENTER",		2);
define("TOP_RIGHT_CORNER",	3);
define("LEFT CENTER",		4);
define("CENTER",		5);
define("RIGHT_CENTER",		6);
define("BOTTOM_LEFT_CORNER",	7);
define("BOTTOM_CENTER",		8);
define("BOTTOM_RIGHT_CORNER",	9);

$INDICATOR_POSITIONS = array(
		"" => "&lt; Select a Position &gt;",
		TOP_LEFT_CORNER => "Top Left Corner",
		TOP_CENTER => "Top Center",
		TOP_RIGHT_CORNER => "Top Right Corner",
		LEFT_CENTER => "Left Center",
		CENTER => "Center",
		RIGHT_CENTER => "Right Center",
		BOTTOM_LEFT_CORNER => "Bottom Left Corner",
		BOTTOM_CENTER => "Bottom Center",
		BOTTOM_RIGHT_CORNER => "Bottom Right Corner"
	);

/******************/
/*  Credit Cards  */
/******************/

define("VISA",			1);
define("MASTERCARD",		2);
define("AMERICAN_EXPRESS",	3);
define("DISCOVER_NOVUS",	4);

$CREDIT_CARDS = array("" => "&lt; Select a Card Type &gt;", VISA => "Visa", MASTERCARD => "MasterCard", AMERICAN_EXPRESS => "American Express", DISCOVER_NOVUS => "Discover/Novus");

define("COUPON_CODE",	"code-based");
define("COUPON_COOKIE",	"cookie-based");
$COUPON_TYPES = array(COUPON_CODE => "Code-based", COUPON_COOKIE => "Cookie-based");

define("DISCOUNT_PERCENTAGE",	1);
define("DISCOUNT_FLAT_AMOUNT",	2);
define("DISCOUNT_BUY_N_GET_N",	3);
$COUPON_DISCOUNT_TYPES = array("" => "&lt; Select a Discount Type &gt;", DISCOUNT_PERCENTAGE => "Percentage", DISCOUNT_FLAT_AMOUNT => "Flat Amount");

define("DISCOUNT_CART",			1);
define("DISCOUNT_PRODUCT",		2);
define("DISCOUNT_PRODUCT_FAMILY",	3);
$COUPON_DISCOUNT_TARGETS = array("" => "&lt; Select a Discount Target &gt;", DISCOUNT_CART => "Discount Entire Cart", DISCOUNT_PRODUCT => "Discount Product");	// , DISCOUNT_PRODUCT_FAMILY => "Discount Product Family"); 

/*********************/
/*  Payment Gateway  */
/*********************/
define("TEST_GATEWAY",	0);
define("LIVE_GATEWAY",	1);

$GATEWAY_TEST_OPTIONS = array(TEST_GATEWAY => "Test Gateway", LIVE_GATEWAY => "Live Processing");

$MONTHS = array("01" => "January", "02" => "February", "03" => "March", "04" => "April", "05" => "May", "06" => "June", "07" => "July", "08" => "August", "09" => "Septempber", "10" => "October", "11" => "November", "12" => "December");
$MONTH_DAYS_31 = array("01" => "01", "02" => "02", "03" => "03", "04" => "04", "05" => "05", "06" => "06", "07" => "07", "08" => "08", "09" => "09", "10" => "10",
						"11" => "11", "12" => "12", "13" => "13", "14" => "14", "15" => "15", "16" => "16", "17" => "17", "18" => "18", "19" => "19", "20" => "20",
						"21" => "21", "22" => "22", "23" => "23", "24" => "24", "25" => "25", "26" => "26", "27" => "27", "28" => "28", "29" => "29", "30" => "30", "31" => "31"
					);
$MONTH_DAYS_30 = array("01" => "01", "02" => "02", "03" => "03", "04" => "04", "05" => "05", "06" => "06", "07" => "07", "08" => "08", "09" => "09", "10" => "10",
						"11" => "11", "12" => "12", "13" => "13", "14" => "14", "15" => "15", "16" => "16", "17" => "17", "18" => "18", "19" => "19", "20" => "20",
						"21" => "21", "22" => "22", "23" => "23", "24" => "24", "25" => "25", "26" => "26", "27" => "27", "28" => "28", "29" => "29", "30" => "30"
					);
$MONTH_DAYS_28 = array("01" => "01", "02" => "02", "03" => "03", "04" => "04", "05" => "05", "06" => "06", "07" => "07", "08" => "08", "09" => "09", "10" => "10",
						"11" => "11", "12" => "12", "13" => "13", "14" => "14", "15" => "15", "16" => "16", "17" => "17", "18" => "18", "19" => "19", "20" => "20",
						"21" => "21", "22" => "22", "23" => "23", "24" => "24", "25" => "25", "26" => "26", "27" => "27", "28" => "28"
					);
$MONTH_DAYS = array("01" => $MONTH_DAYS_31, "02" => $MONTH_DAYS_28, "03" => $MONTH_DAYS_31, "04" => $MONTH_DAYS_30, "05" => $MONTH_DAYS_31, "06" => $MONTH_DAYS_30,
					"07" => $MONTH_DAYS_31, "08" => $MONTH_DAYS_31, "09" => $MONTH_DAYS_30, "10" => $MONTH_DAYS_31, "11" => $MONTH_DAYS_30, "12" => $MONTH_DAYS_31);

//$STATES = array(0 => "< Select a State >", 1 => "Alabama", "Alaska", "Arizona", "Arkansas", "California", "Colorado", "Connecticut", "Delaware", "D.C.", "Florida", "Georgia", "Hawaii", "Idaho", "Illinois", "Indiana", "Iowa", "Kansas", "Kentucky", "Louisiana", "Maine", "Maryland", "Massachusetts", "Michigan", "Minnesota", "Mississippi", "Missouri", "Montana", "Nebraska", "Nevada", "New Hampshire", "New Jersey", "New Mexico", "New York", "North Carolina", "North Dakota", "Ohio", "Oklahoma", "Oregon", "Pennsylvania", "Puerto Rico", "Rhode Island", "South Carolina", "South Dakota", "Tennessee", "Texas", "Utah", "Vermont", "Virginia", "Virgin Islands", "Washington", "West Virginia", "Wisconsin", "Wyoming");

$STATES = array("" => "< Select a State >", "AL" => "Alabama", "AK" => "Alaska", "AZ" => "Arizona", "AR" => "Arkansas", "CA" => "California", "CO" => "Colorado", "CT" => "Connecticut", "DE" => "Delaware", "DC" => "D.C.", "FL" => "Florida", "GA" => "Georgia", "HI" => "Hawaii", "ID" => "Idaho", "IL" => "Illinois", "IN" => "Indiana", "IA" => "Iowa", "KS" => "Kansas", "KY" => "Kentucky", "LA" => "Louisiana", "ME" => "Maine", "MD" => "Maryland", "MA" => "Massachusetts", "MI" => "Michigan", "MN" => "Minnesota", "MS" => "Mississippi", "MO" => "Missouri", "MT" => "Montana", "NE" => "Nebraska", "NV" => "Nevada", "NH" => "New Hampshire", "NJ" => "New Jersey", "NM" => "New Mexico", "NY" => "New York", "NC" => "North Carolina", "ND" => "North Dakota", "OH" => "Ohio", "OK" => "Oklahoma", "OR" => "Oregon", "PA" => "Pennsylvania", "PR" => "Puerto Rico", "RI" => "Rhode Island", "SC" => "South Carolina", "SD" => "South Dakota", "TN" => "Tennessee", "TX" => "Texas", "UT" => "Utah", "VT" => "Vermont", "VA" => "Virginia", "VI" => "Virgin Islands", "WA" => "Washington", "WV" => "West Virginia", "WI" => "Wisconsin", "WY" => "Wyoming");

$COUNTRIES = array(
	"United States of America",
	"Afghanistan",
	"Albania",
	"Algeria",
	"Andorra",
	"Angola",
	"Antigua and Barbuda",
	"Argentina",
	"Armenia",
	"Australia",
	"Austria",
	"Azerbaijan",
	"Bahamas, The",
	"Bahrain",
	"Bangladesh",
	"Barbados",
	"Belarus",
	"Belgium",
	"Belize",
	"Benin",
	"Bhutan",
	"Bolivia",
	"Bosnia and Herzegovina",
	"Botswana",
	"Brazil",
	"Brunei",
	"Bulgaria",
	"Burkina Faso",
	"Burma",
	"Burundi",
	"Cambodia",
	"Cameroon",
	"Canada",
	"Cape Verde",
	"Central African Republic",
	"Chad",
	"Chile",
	"China",
	"Colombia",
	"Comoros",
	"Congo (Brazzaville)",
	"Congo (Kinshasa)",
	"Costa Rica",
	"Cote d'Ivoire",
	"Croatia",
	"Cuba",
	"Cyprus",
	"Czech Republic",
	"Denmark",
	"Djibouti",
	"Dominica",
	"Dominican Republic",
	"East Timor (see Timor-Leste)",
	"Ecuador",
	"Egypt",
	"El Salvador",
	"Equatorial Guinea",
	"Eritrea",
	"Estonia",
	"Ethiopia",
	"Fiji",
	"Finland",
	"France",
	"Gabon",
	"Gambia, The",
	"Georgia",
	"Germany",
	"Ghana",
	"Greece",
	"Grenada",
	"Guatemala",
	"Guinea",
	"Guinea-Bissau",
	"Guyana",
	"Haiti",
	"Holy See",
	"Honduras",
	"Hong Kong",
	"Hungary",
	"Iceland",
	"India",
	"Indonesia",
	"Iran",
	"Iraq",
	"Ireland",
	"Israel",
	"Italy",
	"Jamaica",
	"Japan",
	"Jordan",
	"Kazakhstan",
	"Kenya",
	"Kiribati",
	"Korea, North",
	"Korea, South",
	"Kosovo",
	"Kuwait",
	"Kyrgyzstan",
	"Laos",
	"Latvia",
	"Lebanon",
	"Lesotho",
	"Liberia",
	"Libya",
	"Liechtenstein",
	"Lithuania",
	"Luxembourg",
	"Macau",
	"Macedonia",
	"Madagascar",
	"Malawi",
	"Malaysia",
	"Maldives",
	"Mali",
	"Malta",
	"Marshall Islands",
	"Mauritania",
	"Mauritius",
	"Mexico",
	"Micronesia",
	"Moldova",
	"Monaco",
	"Mongolia",
	"Montenegro",
	"Morocco",
	"Mozambique",
	"Namibia",
	"Nauru",
	"Nepal",
	"Netherlands",
	"Netherlands Antilles",
	"New Zealand",
	"Nicaragua",
	"Niger",
	"Nigeria",
	"North Korea",
	"Norway",
	"Oman",
	"Pakistan",
	"Palau",
	"Palestinian Territories",
	"Panama",
	"Papua New Guinea",
	"Paraguay",
	"Peru",
	"Philippines",
	"Poland",
	"Portugal",
	"Qatar",
	"Romania",
	"Russia",
	"Rwanda",
	"Saint Kitts and Nevis",
	"Saint Lucia",
	"Saint Vincent and the Grenadines",
	"Samoa",
	"San Marino",
	"Sao Tome and Principe",
	"Saudi Arabia",
	"Senegal",
	"Serbia",
	"Seychelles",
	"Sierra Leone",
	"Singapore",
	"Slovakia",
	"Slovenia",
	"Solomon Islands",
	"Somalia",
	"South Africa",
	"South Korea",
	"Spain",
	"Sri Lanka",
	"Sudan",
	"Suriname",
	"Swaziland",
	"Sweden",
	"Switzerland",
	"Syria",
	"Taiwan",
	"Tajikistan",
	"Tanzania",
	"Thailand",
	"Timor-Leste",
	"Togo",
	"Tonga",
	"Trinidad and Tobago",
	"Tunisia",
	"Turkey",
	"Turkmenistan",
	"Tuvalu",
	"Uganda",
	"Ukraine",
	"United Arab Emirates",
	"United Kingdom",
	"Uruguay",
	"Uzbekistan",
	"Vanuatu",
	"Venezuela",
	"Vietnam",
	"Yemen",
	"Zambia",
	"Zimbabwe"
); 

?>
