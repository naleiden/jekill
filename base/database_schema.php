<?php

require_once("define.php");
require_once("schema_manager.php");

$_SETTINGS = array(TABLE_NAME => "settings", TABLE_PROCESSOR => "/schema/save_settings.php");
$COPY = array();
$SCHEMA = array();

$SCHEMA['reports'] = array(
	TABLE_LABEL => "Reports",
	TABLE_TYPE => TABLE_GROUP
);

$SCHEMA['materials'] = array(
	TABLE_LABEL => "Materials",
	TABLE_TYPE => TABLE_GROUP
);

$SCHEMA['dashboard'] = array(
	TABLE_LABEL => "Dashboard",
	TABLE_TYPE => CUSTOM_TABLE,
	TABLE_PARENT => "reports",
	TABLE_BROWSE => "reports/dashboard.php",
	TABLE_STYLE => "/css/reports.css"
);

$SCHEMA['group_usage'] = array(
	TABLE_LABEL => "Group Usage",
	TABLE_TYPE => CUSTOM_TABLE,
	TABLE_PARENT => "reports",
	TABLE_BROWSE => "reports/group_usage.php",
	TABLE_STYLE => "/css/reports.css"
);

$SCHEMA['institutional_usage'] = array(
	TABLE_LABEL => "Institutional Usage",
	TABLE_TYPE => CUSTOM_TABLE,
	TABLE_PARENT => "reports",
	TABLE_BROWSE => "reports/institutional_usage.php",
	TABLE_STYLE => "/css/reports.css"
);

$_SETTINGS['COMPANY_NAME']		= array("COMPANY_NAME", NAME, "Company Name");
$_SETTINGS['COMPANY_LEGAL_NAME']		= array("COMPANY_LEGAL_NAME", NAME, "Company Legal Name");
$_SETTINGS['COMPANY_ADDRESS_1']			= array("COMPANY_ADDRESS_1", NAME, "Address 1");
$_SETTINGS['COMPANY_ADDRESS_2']			= array("COMPANY_ADDRESS_2", NAME, "Address 2");
$_SETTINGS['COMPANY_CITY_STATE_ZIP']	= array("COMPANY_CITY_STATE_ZIP", SENTENCE, "City / State / Zip");
$_SETTINGS['COMPANY_TELEPHONE']			= array("COMPANY_TELEPHONE", TELEPHONE_NUMBER, "Telephone Number");
$_SETTINGS['COMPANY_SUPPORT_EMAIL']		= array("COMPANY_SUPPORT_EMAIL", EMAIL, "Support Email");
$_SETTINGS['COMPANY_DOMAIN']		= array("COMPANY_DOMAIN", URL, "Company Domain", FIELD_NOTES => "Your web domain without 'http://' or 'www' (e.g.: example.com)");
$_SETTINGS['COMPANY_URL']		= array("COMPANY_URL", URL, "Company URL");
$_SETTINGS['FREE_DEMO_PRODUCT']		= array("FREE_DEMO_PRODUCT", LINK, "Free Demo", LINK_TABLE => "product", LINK_LABEL => "title");
$_SETTINGS['EXTENDED_DEMO_PRODUCT']	= array("EXTENDED_DEMO_PRODUCT", LINK, "Extended Free Demo", LINK_TABLE => "product", LINK_LABEL => "title");
$_SETTINGS['SHARE_THIS_CODE']		= array("SHARE_THIS_CODE", COPY, "Share This Code", FIELD_GROUP => "Sharing");
$_SETTINGS['PEN_COLOR']			= array("PEN_COLOR", COLOR, "Drawing Pen Color", FIELD_GROUP => "Drawing Tutorials");
$_SETTINGS['PEN_DIAMETER']		= array("PEN_DIAMETER", NUMBER, "Drawing Pen Diameter", FIELD_GROUP => "Drawing Tutorials");
$_SETTINGS['DEFAULT_ANNOTATION_WIDTH']	= array("DEFAULT_ANNOTATION_WIDTH", NUMBER, "Default Annotation Width", FIELD_GROUP => "Annotations");
$_SETTINGS['DEFAULT_ANNOTATION_HEIGHT']	= array("DEFAULT_ANNOTATION_HEIGHT", NUMBER, "Default Annotation Height", FIELD_GROUP => "Annotations");
$_SETTINGS['DEFAULT_ANNOTATION_COLOR']	= array("DEFAULT_ANNOTATION_COLOR", COLOR, "Default Annotation Color", FIELD_GROUP => "Annotations");
$_SETTINGS['ANNOTATION_MARKER']		= array("ANNOTATION_MARKER", IMAGE, "Annotation Marker", FIELD_GROUP => "Annotations");
$_SETTINGS['ANNOTATION_MARKER_WIDTH']	= array("ANNOTATION_MARKER_WIDTH", NUMBER, "Annotation Marker Width", FIELD_NOTES => "(In Pixels)", FIELD_GROUP => "Annotations");
$_SETTINGS['COPY_ACCESS'] 	= array("COPY_ACCESS", ENUMERATION, "Copy Permissions", FIELD_OPTIONS => $PERMISSIONS, FIELD_GROUP => "Permissions");
$_SETTINGS['SETTINGS_ACCESS'] 	= array("SETTINGS_ACCESS", ENUMERATION, "Settings Permissions", FIELD_OPTIONS => $PERMISSIONS, FIELD_GROUP => "Permissions");

/*
$_SETTINGS['SHOW_CALLOUT']		= array("SHOW_CALLOUT", BOOL, "Show Callout", FIELD_GROUP => "Header Callout");
$_SETTINGS['CALLOUT_URL']		= array("CALLOUT_URL", URL, "Callout URL")
*/

$_SETTINGS['AMBASSADOR_TYPE'] = array("AMBASSADOR_TYPE", ENUMERATION, "Ambassador Type", FIELD_OPTIONS => $AMBASSADOR_TYPES, FIELD_GROUP => "Ambassadors");
// $_SETTINGS['COUPON_AMBASSADOR_HEADER'] = array("COUPON_AMBASSADOR_HEADER", FIELD_HEADER, "Coupon-based Ambassadors", FIELD_GROUP => "Ambassadors");
$_SETTINGS['AMBASSADOR_DISCOUNT']	= array("AMBASSADOR_DISCOUNT", NUMBER, "Ambassador Discount", FIELD_NOTES => "(e.g., 15 for 15%)", FIELD_ATTACHMENT => array("AMBASSADOR_TYPE" => AMBASSADOR_CODE), FIELD_GROUP => "Ambassadors");
$_SETTINGS['AMBASSADOR_SIGNUP_BONUS']	= array("AMBASSADOR_SIGNUP_BONUS", LINK, "Ambassador Signup Bonus", FIELD_NOTES => "(Subscription rewarded on signup)", LINK_TABLE => "product", LINK_LABEL => "title", FIELD_ATTACHMENT => array("AMBASSADOR_TYPE" => AMBASSADOR_CODE), FIELD_GROUP => "Ambassadors");
$_SETTINGS['AMBASSADOR_REFERRAL_COUPON'] = array("AMBASSADOR_REFERRAL_COUPON", LINK, "Referral Coupon", LINK_TABLE => "coupon", LINK_LABEL => "name", FIELD_ATTACHMENT => array("AMBASSADOR_TYPE" => AMBASSADOR_REFERRAL), FIELD_GROUP => "Ambassadors");
// $_SETTINGS['REFERRAL_AMBASSADOR_HEADER'] = array("REFERRAL_AMBASSADOR_HEADER", FIELD_HEADER, "Coupon-based Ambassadors", FIELD_GROUP => "Ambassadors");


$_SETTINGS['AUTH_NET_LOGIN']		= array("AUTH_NET_LOGIN", NAME, "Login ID", FIELD_GROUP => "Gateway");
$_SETTINGS['AUTH_NET_TRANS_KEY']	= array("AUTH_NET_TRANS_KEY", NAME, "Transaction Key", FIELD_GROUP => "Gateway");
$_SETTINGS['AUTH_NET_TESTING']		= array("AUTH_NET_TESTING", ENUMERATION, "Gateway Status", FIELD_OPTIONS => $GATEWAY_TEST_OPTIONS, FIELD_GROUP => "Gateway");

$_SETTINGS['MAX_QUIZ_QUESTIONS'] = array("MAX_QUIZ_QUESTIONS", NUMBER, "Max. Quiz Questions", FIELD_GROUP => "Quizzes/Exams");
$_SETTINGS['PASSING_GRADE']		= array("PASSING_GRADE", NUMBER, "Passing Grade", FIELD_NOTES => "%", FIELD_GROUP => "Quizzes/Exams");

$_SETTINGS['DEBUG']		= array("DEBUG", ENUMERATION, "Checkout Status", FIELD_OPTIONS => $DEBUGGING_OPTIONS, FIELD_GROUP => "Checkout");
$_SETTINGS['DEBUG_PAYMENT_USERNAME']	= array("DEBUG_PAYMENT_USERNAME", SENTENCE, "Debug Payment Username", FIELD_ATTACHMENT => "DEBUG", FIELD_ATTACHMENT_VALUE => DEBUG, FIELD_GROUP => "Paypal");
$_SETTINGS['DEBUG_PAYMENT_PASSWORD']	= array("DEBUG_PAYMENT_PASSWORD", NAME, "Debug Payment Password", FIELD_ATTACHMENT => "DEBUG", FIELD_ATTACHMENT_VALUE => DEBUG, FIELD_GROUP => "Paypal");
$_SETTINGS['DEBUG_PAYMENT_SIGNATURE']	= array("DEBUG_PAYMENT_SIGNATURE", SENTENCE, "Debug Payment Signature", FIELD_ATTACHMENT => "DEBUG", FIELD_ATTACHMENT_VALUE => DEBUG, FIELD_GROUP => "Paypal");
$_SETTINGS['PAYMENT_USERNAME']	= array("PAYMENT_USERNAME", SENTENCE, "Payment Username", FIELD_ATTACHMENT => "DEBUG", FIELD_ATTACHMENT_VALUE => LIVE, FIELD_GROUP => "Paypal");
$_SETTINGS['PAYMENT_PASSWORD']	= array("PAYMENT_PASSWORD", NAME, "Payment Password", FIELD_ATTACHMENT => "DEBUG", FIELD_ATTACHMENT_VALUE => LIVE, FIELD_GROUP => "Paypal");
$_SETTINGS['PAYMENT_SIGNATURE']	= array("PAYMENT_SIGNATURE", SENTENCE, "Payment Signature", FIELD_ATTACHMENT => "DEBUG", FIELD_ATTACHMENT_VALUE => LIVE, FIELD_GROUP => "Paypal");

$_SETTINGS['DEMO_TERM']		= array("DEMO_TERM", LINK, "Demo Term", LINK_TABLE => "term", LINK_LABEL => "term", LINK_WHERE => "WHERE free_demo = '1'", FIELD_GROUP => "Demo");

$_SETTINGS['ONE_MONTH_PRODUCT']		= array("ONE_MONTH_PRODUCT", LINK, "One Month Product", LINK_TABLE => "product", LINK_LABEL => "title", FIELD_GROUP => "Subscription Tiers");
$_SETTINGS['THREE_MONTH_PRODUCT']	= array("THREE_MONTH_PRODUCT", LINK, "Three Month Product", LINK_TABLE => "product", LINK_LABEL => "title", FIELD_GROUP => "Subscription Tiers");
$_SETTINGS['ONE_YEAR_PRODUCT']		= array("ONE_YEAR_PRODUCT", LINK, "One Year Product", LINK_TABLE => "product", LINK_LABEL => "title", FIELD_GROUP => "Subscription Tiers");
$_SETTINGS['LIFETIME_PRODUCT']		= array("LIFETIME_PRODUCT", LINK, "Lifetime Product", LINK_TABLE => "product", LINK_LABEL => "title", FIELD_GROUP => "Subscription Tiers");

/*
$COPY['ACKNOWLEDGEMENTS']	= array(COPY_DESCRIPTION => "Acknowledgements", COPY_INCLUDE_PAGE => "acknowledgements.inc");
$COPY['ABOUT_DITKI']		= array(COPY_DESCRIPTION => "About DITKI", COPY_INCLUDE_PAGE => "about_ditki.inc");
$COPY['ABOUT_THE_SITE']		= array(COPY_DESCRIPTION => "About The Site", COPY_INCLUDE_PAGE => "about_the_author.inc");
$COPY['BRAIN_ATLAS_REFERENCES']	= array(COPY_DESCRIPTION => "Brain Atlas References", COPY_INCLUDE_PAGE => "brain_atlas_references.inc");
$COPY['CORRECTIONS']		= array(COPY_DESCRIPTION => "Corrections To The Book", COPY_INCLUDE_PAGE => "corrections.inc");
$COPY['EMAIL_THE_AUTHOR']	= array(COPY_DESCRIPTION => "Email The Author Copy", COPY_INCLUDE_PAGE => "email_the_author.inc");
$COPY['ABOUT_THE_BOOK']	= array(COPY_DESCRIPTION => "About The Book", COPY_INCLUDE_PAGE => "about_the_book.inc");
$COPY['FOREWORD_TO_THE_BOOK']	= array(COPY_DESCRIPTION => "Foreword To The Book", COPY_INCLUDE_PAGE => "foreword_to_the_book.inc");
$COPY['HOME_COPY']		= array(COPY_DESCRIPTION => "Home Copy", COPY_INCLUDE_PAGE => "home_copy.inc");
$COPY['MUSCLE_NERVE_DIRECTORY']	= array(COPY_DESCRIPTION => "Muscle/Nerve Directory", COPY_INCLUDE_PAGE => "muscle_nerve_directory.inc");
$COPY['MUSCLE_NERVEREFERENCES']	= array(COPY_DESCRIPTION => "Muscle/Nerve Directory References", COPY_INCLUDE_PAGE => "muscle_nerve_references.inc");
$COPY['PRIVACY_POLICY']		= array(COPY_DESCRIPTION => "Privacy Policy", COPY_INCLUDE_PAGE => "privacy_policy.inc");
$COPY['PURCHASE_EMAIL']		= array(COPY_DESCRIPTION => "Purchase Email", COPY_INCLUDE_PAGE => "subscription_email.inc");
$COPY['PURCHASE_THE_BOOK']	= array(COPY_DESCRIPTION => "Purchase The Book Copy", COPY_INCLUDE_PAGE => "purchase_the_book.inc");
$COPY['REGISTRATION_EMAIL']	= array(COPY_DESCRIPTION => "Reistration Email", COPY_INCLUDE_PAGE => "registration_email.inc");
$COPY['REFERENCES']		= array(COPY_DESCRIPTION => "References", COPY_INCLUDE_PAGE => "references.inc");
$COPY['SUBSCRIBER_TERMS_OF_USE']= array(COPY_DESCRIPTION => "Subscriber Terms & Conditions", COPY_INCLUDE_PAGE => "subscriber_terms_of_use.inc");
$COPY['TECHNICAL_REQUIREMENTS']	= array(COPY_DESCRIPTION => "Technical Requirements", COPY_INCLUDE_PAGE => "technical_requirements.inc");
$COPY['TERMS_OF_USE']		= array(COPY_DESCRIPTION => "Terms & Conditions", COPY_INCLUDE_PAGE => "terms_of_use.inc");
*/

$SCHEMA['alert'] = array(
	TABLE_LABEL => "Alerts",

	"headline"		=> array("headline", SENTENCE, "Headline", REQUIRED),
	"url_ID"		=> array("url_ID", RICH_URL_ID, "URL ID", OPTIONAL_HIDDEN, LINK_FIELD => "headline"),
	"date"			=> array("date", DATE, "Date"),
	"description"	=> array("description", HTML_COPY, "Description"),
    "image"			=> array("image", IMAGE, "Image"),
	"show_on"		=> array("show_on", DATETIME, "Display On", FIELD_GROUP => "Scheduling"),
	"show_until"	=> array("show_until", DATETIME, "Display Until", FIELD_GROUP => "Scheduling"),
	"visible"		=> array("visible", BOOL, "Visible")
);

$SCHEMA['ambassador'] = array(
	TABLE_LABEL => "Ambassadors",
	TABLE_PARENT => "customer",
	TABLE_ACCESS => ADMINISTRATOR,

	"type"			=> array("type", ENUMERATION, "Ambassador Type", FIELD_OPTIONS => $AMBASSADOR_TYPES),
	"customer"		=> array("customer", LINK, "Customer Account", LINK_TABLE => "customer", LINK_LABEL => "last_name, first_name", LINK_SORT => "last_name"),
	"registered_on"	=> array("registered_on", DATETIME, "Registered On"),
	"institution"	=> array("institution", NAME, "Institution", FIELD_GROUP => "Institution"),	// LINK_TABLE => "institution", LINK_LABEL => "name", LINK_SORT => "name"),
	"program"		=> array("program", NAME, "Program", FIELD_GROUP => "Institution"),
	"num_sales"		=> array("num_sales", NUMBER, "Num. Sales", FIELD_GROUP => "Sales"),
	"reward_points"	=> array("reward_points", NUMBER, "Reward Points"),
	"milestones"	=> array("milestones", LINK_ONE_TO_N, "Milestones", LINK_TABLE => "ambassador_milestone", LINK_FIELD => "ambassador", LINK_LABEL => "reward_points: date", FIELD_GROUP => "Sales"),
	"coupon"		=> array("coupon", LINK, "Coupon", LINK_TABLE => "coupon", LINK_LABEL => "name", LINK_SORT => "name")
);

// People an ambassador has signed up
$SCHEMA['ambassador_sale'] = array(
	TABLE_LABEL => "Ambassador Sales",
	TABLE_PARENT => "ambassador",
	TABLE_ACCESS => ADMINISTRATOR,

	"ambassador"	=> array("ambassador", LINK, "Ambassador", LINK_TABLE => "ambassador", LINK_LABEL => "customer"),
	"customer"		=> array("customer", LINK, "Purchaser", LINK_TABLE => "customer", LINK_LABEL => "last_name, first_name", LINK_SORT => "last_name, first_name"),
	"transaction"	=> array("transaction", LINK, "Transaction", LINK_TABLE => "transaction", LINK_LABEL => "\$order_total: purchase_date")
);

// Rewards that an ambassador has achieved.
$SCHEMA['ambassador_milestone'] = array(
	TABLE_LABEL => "Ambassador Milestones",
	TABLE_PARENT => "ambassador",
	TABLE_SORT => "date",
	TABLE_ACCESS => ADMINISTRATOR,

	"ambassador"	=> array("ambassador", LINK, "Ambassador", LINK_TABLE => "ambassador", LINK_LABEL => "customer"),
	"date"			=> array("date", DATETIME, "Date"),
	"reward_points"	=> array("reward_points", NUMBER, "Reward Points Reached"),
	"reward"		=> array("reward", LINK, "Reward", LINK_TABLE => "ambassador_reward", LINK_FIELD => "ambassador", LINK_LABEL => "product"),
	"purchase"		=> array("purchase", LINK, "Subscription Rewarded", LINK_TABLE => "purchase", LINK_LABEL => "purchase_date", LINK_SORT => "-purchase_date")
);

$SCHEMA['ambassador_reward'] = array(
	TABLE_LABEL => "Ambassador Rewards",
	TABLE_PARENT => "ambassador",
	TABLE_SORT => "points_required",
	TABLE_ACCESS => ADMINISTRATOR,

	"points_required"	=> array("points_required", NUMBER, "Points Required", FIELD_NOTES => "(Reward points required)"),
	"product"			=> array("product", LINK, "Product Earned", LINK_TABLE => "product", LINK_LABEL => "title")
);


$SCHEMA['anatomical_structure'] = array(TABLE_LABEL => "Anatomical Structures", TABLE_PARENT => "anatomical_component", TABLE_SORT => "name", TABLE_ACCESS => ADMINISTRATOR);
$SCHEMA['anatomical_structure']['name'] 	= array("name", NAME, "Name");
$SCHEMA['anatomical_structure']['url_ID']	= array("url_ID", RICH_URL_ID, "URL ID", OPTIONAL_HIDDEN, LINK_FIELD => "name");
$SCHEMA['anatomical_structure']['relevance']	= array("relevance", NUMBER, "Relevance", FIELD_NOTES => "(Number determining order of importance)");
// $SCHEMA['anatomical_structure']['sort_
$SCHEMA['anatomical_structure']['sort_parameter']	= array("sort_parameter", BOOL, "Sort Parameter");

/* $SCHEMA['anatomical_component'] = array(
	TABLE_LABEL => "Anatomical Components",
	TABLE_SORT => "name",
	TABLE_PARENT => "materials",
	RECORD_LABEL => "name",

	// "type"				=> array("type", ENUMERATION, "Type", FIELD_OPTIONS => $ANATOMICAL_COMPONENTS);
	"type" 			=> array("type", LINK, "Component Type", LINK_TABLE => "anatomical_structure", LINK_LABEL => "name"),
	"locus"			=> array("locus", ENUMERATION, "Anatomical Locus", SELECT_MULTIPLE => 1, SELECT_SIZE => 5, FIELD_OPTIONS => $ANATOMICAL_LOCI),
	"name"			=> array("name", NAME, "Name", REQUIRED),
	"url_ID"		=> array("url_ID", RICH_URL_ID, "URL ID", OPTIONAL_HIDDEN, LINK_FIELD => "name"),
	"video"			=> array("video", SERVER_FILE, "Video", FIELD_EXTENSIONS => array(".flv"), ROOT_DIRECTORY => "flash/muscle", FIELD_GROUP => "Video"),
	"vimeo_video"	=> array("vimeo_video", COPY, "Vimeo Embed Code", FIELD_GROUP => "Video"),
	"video_caption"	=> array("video_caption", SENTENCE, "Video Caption"),
	"clinically_relevant"	=> array("clinically_relevant", BOOL, "Clinically Relevant"),
	"free_demo"		=> array("free_demo", BOOL, "Demo / Free", FIELD_GROUP => "Subscription"),
	"images"		=> array("images", LINK_N_TO_N, "Image", LINK_TABLE => "image", LINK_LABEL => "caption", LINK_SORT => "caption", LINK_OPTIONS => LINK_EXPANDED, FIELD_GROUP => "Images"),	// |LINK_NEW_ONLY
	// "components"	=> array("components", LINK_MUTUAL, "Related Components", LINK_TABLE => "anatomical_component", LINK_LABEL => "name", LINK_SORT => "name", LINK_OPTIONS => LINK_EXPANDED, FIELD_GROUP => "Associated Components"), //|LINK_EXISTING_ONLY
	// "visible"	=> array("visible", BOOL, "Visible"),
); */

$SCHEMA['annotation'] = array(TABLE_LABEL => "Radiographic Annotation", TABLE_ACCESS => DIETY);
// $SCHEMA['annotation']['type']		= array("type",	ENUMERATION, "Type", FIELD_OPTIONS => $ANNOTATION_TYPES);
$SCHEMA['annotation']['category']	= array("category", LINK, "Category", LINK_TABLE => "category", LINK_LABEL => "name");	// , FIELD_ATTACHMENT => "type", FIELD_ATTACHMENT_VALUE => CATEGORY_ANNOTATION);
$SCHEMA['annotation']['term']		= array("term", LINK, "Term", LINK_TABLE => "term", LINK_LABEL => "term");	// , FIELD_ATTACHMENT => "type", FIELD_ATTACHMENT_VALUE => TERM_ANNOTATION);
$SCHEMA['annotation']['label']		= array("label", NAME, "Annotation Label");
$SCHEMA['annotation']['x']		= array("x", NUMBER, "X", OPTIONAL_HIDDEN);
$SCHEMA['annotation']['y']		= array("y", NUMBER, "Y", OPTIONAL_HIDDEN);
$SCHEMA['annotation']['width']		= array("width", NUMBER, "Width", OPTIONAL_HIDDEN);
$SCHEMA['annotation']['height']		= array("height", NUMBER, "Height", OPTIONAL_HIDDEN);
// $SCHEMA['annotation']['indicator']	= array("indicator", IMAGE, "Indicator");
// $SCHEMA['annotation']['indicator_position'] = array("indicator_position", ENUMERATION, "Indicator Position", FIELD_OPTIONS => $INDICATOR_POSITIONS);
// $SCHEMA['annotation']['color']		= array("color", COLOR, "Color", FIELD_DEFAULT => "FFFFFF");
// $SCHEMA['annotation']['font_size']	= array("font_size", NUMBER, "Font Size (Pt.)", FIELD_DEFAULT => "22pt");
$SCHEMA['annotation']['annotation']	= array("annotation", IMAGE_ANNOTATION, "&nbsp;", LINK_TABLE => "radiograph", LINK_FIELD => "outlined_radiograph");


$SCHEMA['api_credentials'] = array(
	TABLE_LABEL => "API Credentials",
	TABLE_PARENT => "customer",

	"username"	=> array("username", "CHAR(16)", "Username", REQUIRED),
	// NOTE: Make key unique!
	"api_key"		=> array("api_key", "CHAR(64)", "API Key", REQUIRED),
	// "permissions"	=> array("permissions", "", "Permissions", )
	"ip_address"	=> array("ip_address", "CHAR(16)", "IP Address", FIELD_NOTES => "(Restrict to IP)"),
	"granted_on"	=> array("granted_on", DATETIME, "Granted On"),
	"revoked"		=> array("revoked", BOOL, "Revoked On"),
	"revoked_on"	=> array("revoked_on", DATETIME, "Revoked On")
);


$SCHEMA['category'] = array(TABLE_LABEL => "Categories", TABLE_PARENT => "radiograph", TABLE_ACCESS => ADMINISTRATOR);
$SCHEMA['category']['name']	= array("name", NAME, "Name", REQUIRED);
$SCHEMA['category']['url_ID']	= array("url_ID", RICH_URL_ID, "URL ID", OPTIONAL_HIDDEN, LINK_FIELD => "name");
$SCHEMA['category']['abbreviation']	= array("abbreviation", NAME, "Abbreviation");
$SCHEMA['category']['free_demo']	= array("free_demo", BOOL, "Demo / Free", FIELD_GROUP => "Subscription");
// $SCHEMA['category']['radiographs']	= array("radiographs", LINK_N_TO_N, "Radiograph", LINK_TABLE => "radiograph", LINK_LABEL => "name");
// $SCHEMA['category']['category_terms']	= array("category_terms", LINK_N_TO_N, "Term", LINK_TABLE => "term", LINK_LABEL => "term");


$SCHEMA['coupon'] = array(
	TABLE_LABEL => "Coupons",
	TABLE_PARENT => "customer",
	TABLE_ACCESS => ADMINISTRATOR,

	"name"			=> array("name", NAME, "Coupon Name", REQUIRED),
    "type"          => array("type", ENUMERATION, "Coupon Type", FIELD_OPTIONS => $COUPON_TYPES),
	"code"			=> array("code", NAME, "Coupon Code", REQUIRED, FIELD_ATTACHMENT => array("type" => CODE_BASED_COUPON)),
    // "cookie"        => array("cookie", NAME, "Cookie Name"),
	"issued"		=> array("issued", DATE, "Issue Date"),
	"expires"		=> array("expires", DATE, "Expiration Date"),
	"discount_type"	=> array("discount_type", ENUMERATION, "Discount Type", REQUIRED, FIELD_OPTIONS => $COUPON_DISCOUNT_TYPES),
	"discount"		=> array("discount", MONEY, "Discount", REQUIRED, FIELD_NOTES => "(e.g., 30 for %, 5.00 for $)", FIELD_ATTACHMENT => "discount_type", FIELD_ATTACHMENT_VALUE => "!" . DISCOUNT_BUY_N_GET_N),
	"exclusive"		=> array("exclusive", BOOL, "Exclusive", FIELD_NOTES => "(This coupon may not be used with any other coupon)"),
	"buy_n"			=> array("buy_n", NUMBER, "Buy", REQUIRED, FIELD_ATTACHMENT => "discount_type", FIELD_ATTACHMENT_VALUE => DISCOUNT_BUY_N_GET_N),
	"get_n"			=> array("get_n", NUMBER, "Get", REQUIRED, FIELD_ATTACHMENT => "discount_type", FIELD_ATTACHMENT_VALUE => DISCOUNT_BUY_N_GET_N),
	"apply_to_type"	=> array("apply_to_type", ENUMERATION, "Apply To", REQUIRED, FIELD_OPTIONS => $COUPON_DISCOUNT_TARGETS),
	"apply_to"		=> array("apply_to", LINK, "Product", REQUIRED, LINK_TABLE => "product", LINK_LABEL => "title", FIELD_ATTACHMENT => "apply_to_type", FIELD_ATTACHMENT_VALUE => DISCOUNT_PRODUCT),
	"email_restriction"	=> array("email_restriction", NAME, "Email Restriction", FIELD_NOTES => "(User's email must end in)")
);

// Referral coupons must be associated with a bank of products, so we
// can appropriately guide the referred customer to a purchase.
$SCHEMA['referral_coupon'] = array(
    TABLE_LABEL => "Referral Coupons",
    TABLE_PARENT => "coupon",

    "coupon"    => array("coupon", LINK, "Coupon", REQUIRED, LINK_TABLE => "coupon", LINK_LABEL => "name"),
    "products"  => array("products", LINK_N_TO_N, "Products", LINK_TABLE => "product", LINK_LABEL => "title")
);

$SCHEMA['course'] = array(
    TABLE_PARENT => "institution",
    TABLE_LABEL => "Courses",

    "institution"   => array("institution", LINK, "Institution", REQUIRED, LINK_TABLE => "institution", LINK_LABEL => "name", LINK_SORT => "name"),
    "name"      => array("name", NAME, "Course Name", REQUIRED),
	"url_ID"	=> array("url_ID", RICH_URL_ID, "URL ID", OPTIONAL_HIDDEN, LINK_FIELD => "name"),
	"abbreviation" => array("abbreviation", NAME, "Abbreviation", FIELD_HELP => "e.g., AP101"),
	"discipline"	=> array("discipline", SET, "Discipline", FIELD_OPTIONS => $MEDICAL_SCIENCE_DISCIPLINES),
	"term"		=> array("term", NAME, "Term", FIELD_HELP => "e.g., Spring 2015"),
    "directors" => array("directors", LINK_N_TO_N, "Directors", LINK_TABLE => "course_director", LINK_LABEL => "customer", LINK_MAP_TABLE => "course_director_course", LINK_LOCAL_KEY => "course", LINK_FOREIGN_KEY => "director", LINK_WHERE => "WHERE institution = <%institution%>"),
	"curriculum"	=> array("curriculum", LINK_N_TO_N, "Curriculum", LINK_TABLE => "study_plan", LINK_LABEL => "name")
);

$SCHEMA['course_director'] = array(
    TABLE_LABEL => "Course Directors",
    TABLE_PARENT => "institution",

    "institution"   => array("institution", LINK, "Institution", REQUIRED, LINK_TABLE => "institution", LINK_LABEL => "name", LINK_SORT => "name"),
    "customer"      => array("customer", LINK, "Customer", REQUIRED, LINK_TABLE => "customer", LINK_LABEL => "last_name, first_name (email)", LINK_SORT => "last_name, first_name"),
	// "courses"		=> array("courses", LINK_N_TO_N, "Courses", LINK_TABLE => "course", LINK_LABEL => "name", LINK_WHERE => "WHERstitution = <%institution% >"),
	"administrator"	=> array("administrator", BOOL, "Administrator"),
	"sales_partner" => array("sales_partner", BOOL, "Sales Partner"),
	"added_on"		=> array("added_on", DATETIME, "Added On", FIELD_GROUP => "Status"),
	"added_by"		=> array("added_by", LINK, "Added By", REQUIRED, LINK_TABLE => "customer", LINK_LABEL => "last_name, first_name (email)", LINK_SORT => "last_name, first_name", FIELD_GROUP => "Status"),
    "removed"       => array("removed", BOOL, "Removed", FIELD_GROUP => "Status"),
    "removed_by"    => array("removed_by", LINK, "Deleted By", LINK_TABLE => "customer", LINK_LABEL => "last_name, first_name", FIELD_GROUP => "Status"),
    "removed_on"    => array("removed_on", DATETIME, "Removed On", FIELD_GROUP => "Status")
);

$SCHEMA['course_director_course'] = array(
	TABLE_PARENT => "course_director",
	TABLE_LABEL => "Course Director Courses",

	"course"	=> array("course", LINK, "Course", LINK_TABLE => "course", LINK_LABEL => "name"),
	"director"	=> array("director", LINK, "Course Director", LINK_TABLE => "course_director", LINK_LABEL => "name"),
	"added_on"		=> array("added_on", DATETIME, "Added On"),
	"added_by"		=> array("added_by", LINK, "Added by", LINK_TABLE => "customer", LINK_LABEL => "first_name last_name"),
	"removed"		=> array("removed", BOOL, "Removed"),
	"removed_on"	=> array("removed_on", DATETIME, "Removed On", FIELD_ATTACHMENT => array("removed" => 1)),
	"removed_by"	=> array("removed_by", LINK, "Removed By", FIELD_ATTACHMENT => array("removed" => 1)),
);

$SCHEMA['course_study_plan'] = array(
	TABLE_PARENT => "course",
	TABLE_LABEL => "Course Study Plans",

	"course" => array("course", LINK, "Course", LINK_TABLE => "course", LINK_LABEL => "name"),
	"study_plan" => array("study_plan", LINK, "Study Plan", LINK_TABLE => "study_plan", LINK_LABEL => "name"),
	"added_on"		=> array("added_on", DATETIME, "Added On"),
	"added_by"		=> array("added_by", LINK, "Added By", LINK_TABLE => "customer", LINK_LABEL => "last_name, first_name"),
	"removed"		=> array("removed", BOOL, "Removed"),
	"removed_on"	=> array("removed_on", DATETIME, "Removed On", FIELD_ATTACHMENT => array("removed" => 1)),
	"removed_by"	=> array("removed_by", LINK, "Removed By", LINK_TABLE => "customer", LINK_LABEL => "last_name, first_name", FIELD_ATTACHMENT => array("removed" => 1))
);

$SCHEMA['curriculum'] = array(


);

$SCHEMA['customer'] = array(
    TABLE_LABEL => "Customers",
    TABLE_POSTPROCESSOR => "register_email.php",
    TABLE_BROWSE_LIMIT => 30,

    "email" 		=> array("email", EMAIL, "E-mail", REQUIRED, FIELD_UNIQUE => 1),
    "first_name"	=> array("first_name", NAME, "First Name", REQUIRED),
    "last_name"	    => array("last_name", NAME, "Last Name", REQUIRED),
    "password"		=> array("password", PASSWORD, "Password", REQUIRED),
    // "serial_num"	=> array("serial_num", NAME, "<i>Optional</i>", FIELD_NOTES => "<span class='isbn_hint'>(enter your book's <a href='#' class='hoverable'>ISBN<img src='/images/isbn.jpg' class='hover-state' /></a> to receive free access to the 'tutorials without narration')</span>"),
    "registered_on"	=> array("registered_on", DATETIME, "Registered On", FIELD_ACCESS => ADMINISTRATOR),
    // "terms_of_use"	=> array("terms_of_use", BOOL, "I am of at least 13 years of age and have read and agree to the <a href=\"/terms-of-use.php\" target=\"_blank\">Terms & Conditions</a>.", REQUIRED, FIELD_ERROR_MESSAGE => "You must agree to the <a href=\"/terms-of-use.php\" target=\"_blank\">Terms & Conditions</a> to proceed.", FIELD_PREVIEW => 0, FIELD_ATTACHMENT => "terms_of_use", FIELD_ATTACHMENT_VALUE => "!1"),
    "occupation"	=> array("occupation", ENUMERATION, "Occupation", REQUIRED, FIELD_OPTIONS => $OCCUPATIONS, FIELD_ACCESS => ADMINISTRATOR),
    "lead_source"	=> array("lead_source", ENUMERATION, "Lead Source", REQUIRED, FIELD_OPTIONS => $LEAD_SOURCES, FIELD_ACCESS => ADMINISTRATOR),
    "purchases"	    => array("purchases", LINK_ONE_TO_N, "Purchase", LINK_TABLE => "purchase", LINK_LABEL => "purchase_date $purchase_price", LINK_FIELD => "customer", LINK_OPTIONS => LINK_NEW_ONLY|LINK_EXPANDED, FIELD_ACCESS => ADMINISTRATOR),
    "study_plan"	=> array("study_plan", LINK, "Study Plan", FIELD_ACCESS => ADMINISTRATOR, LINK_TABLE => "study_plan", LINK_LABEL => "name", LINK_SORT => "name")
);

$SCHEMA['customer_course'] = array(
	TABLE_PARENT => "course",
	TABLE_LABEL => "Course Students",

	"customer"	=> array("customer", LINK, "Customer", LINK_TABLE => "customer", LINK_LABEL => "last_name, first_name"),
	"course"	=> array("course", LINK, "Course", LINK_TABLE => "course", LINK_LABEL => "name"),
	"added_on"	=> array("added_on", DATETIME, "Added On"),
	"removed"	=> array("removed", BOOL, "Removed"),
	"removed_on"=> array("removed_on", DATETIME, "Removed On")
);

$SCHEMA['customer_login'] = array(
	TABLE_LABEL => "Customer Logins",
	TABLE_PARENT => "customer",
	TABLE_BROWSE_LIMIT => 30,
	TABLE_ACCESS => ADMINISTRATOR,
	
	"guid" => array("guid", NAME, "Unique ID"),
	"login_type" => array("login_type", ENUMERATION, "Login Type", FIELD_OPTIONS => array("customer" => "Customer", "site_license" => "Site License")),
	"customer" => array("customer", LINK, "Customer", LINK_ATTACHMENT => "login_type", LINK_TABLE => array("customer" => "customer", "site_license" => "site_license"), LINK_LABEL => array("customer" => "last_name, first_name", "site_license" => "institution")),
	"concurrent_logins" => array("concurrent_logins", NUMBER, "Concurrent Logins"),
	"time" => array("time", DATETIME, "Time"),
	"ip"		=> array("ip", NAME, "IP Address"),
	"user_agent" => array("user_agent", NAME, "Browser"),
	"page_view"	=> array("page_view", LINK, "Current Pageview", LINK_TABLE => "page_view", LINK_LABEL => "viewed_until"),
	"logged_out" => array("logged_out", DATETIME, "logged_out")
);

$SCHEMA['device'] = array(
	TABLE_LABEL => "Registered Devices",
	TABLE_PARENT => "purchase",
	TABLE_ACCESS => ADMINISTRATOR,

	"name"		=> array("name", NAME, "Name"),
	"identifier"	=> array("identifier", NAME, "Identifier"),
	"subscription"	=> array("subscription", LINK, "Subscription", LINK_TABLE => "purchase", LINK_LABEL => "purchase_date"),
	"added_on"	=> array("added_on", DATETIME, "Added On"),
	"removed"	=> array("removed", BOOL, "Removed"),
	"removed_on"	=> array("removed_on", DATETIME, "Removed On", FIELD_ATTACHMENT => "removed", FIELD_ATTACHMENT_VALUE => "1")
);

$SCHEMA['drawing'] = array(
	TABLE_LABEL => "Saved Drawings",
	TABLE_PARENT => "lesson",

	"customer"	=> array("customer", LINK, "Customer", LINK_TABLE => "customer", LINK_LABEL => "last_name, first_name"),
	"lesson"	=> array("lesson", LINK, "lesson", LINK_TABLE => "lesson", LINK_LABEL => "title"),
	"drawn_on"	=> array("drawn_on", DATETIME, "Drawn On"),
	"drawing"	=> array("drawing", IMAGE, "Drawing"),
	"deleted"	=> array("deleted", BOOL, "Deleted On"),
	"deleted_on"	=> array("deleted_on", DATETIME, "Deleted")
);

$SCHEMA['faq_category'] = array(
	TABLE_LABEL => "F.A.Q. Category",
	TABLE_PARENT => "faq",

	"category"	=> array("category", NAME, "Category", REQUIRED),
	"url_ID" 	=> array("url_ID", RICH_URL_ID, "URL ID", OPTIONAL_HIDDEN, LINK_FIELD => "category"),
	"rank"		=> array("rank", NUMBER, "Rank"),
	"questions" => array("questions", LINK_ONE_TO_N, "Questions", LINK_TABLE => "faq", LINK_LABEL => "question")
);

$SCHEMA['faq'] = array(
	TABLE_LABEL => "F.A.Q.",
	TABLE_PARENT => "supplement",
	TABLE_SORT => "rank",
	TABLE_ACCESS => ADMINISTRATOR,

	"category" => array("category", LINK, "Category", LINK_TABLE => "faq_category", LINK_LABEL => "category"),
	"question" => array("question", SENTENCE, "Question", REQUIRED),
	"url_ID" => array("url_ID", RICH_URL_ID, "URL ID", OPTIONAL_HIDDEN, LINK_FIELD => "question", FIELD_GROUP => "Advanced"),
	"answer" => array("answer", COPY, "Answer", REQUIRED),
	"rank"		=> array("rank", NUMBER, "Rank"),
	"visible" => array("visible", BOOL, "Visible")
);

$SCHEMA['institution'] = array(
    TABLE_LABEL => "Institutions",
    TABLE_PARENT => "customer",
    TABLE_SORT => "name",

    "name" => array("name", NAME, "Name"),
    "url_ID" => array("url_ID", RICH_URL_ID, "URL ID", OPTIONAL_HIDDEN, LINK_FIELD => "name"),
    "logo" => array("logo", IMAGE, "Logo")
);

$SCHEMA['director_invitation'] = array(
	TABLE_LABEL => "Director Invitations",
	TABLE_PARENT => "institution",

	"institution" => array("institution", LINK, "Institution", LINK_TABLE => "institution", LINK_LABEL => "name"),
	"name"	=> array("name", NAME, "Name"),
	"email" => array("email", EMAIL, "Email"),
	"type" => array("type", ENUMERATION, "Type", FIELD_OPTIONS => $INSTITUTIONAL_USER_TYPES),
	"course" => array("course", LINK, "Course", LINK_TABLE => "course", LINK_LABEL => "name"),
	"invitee" => array("invitee", LINK, "Invitee", LINK_TABLE => "customer", LINK_LABEL => "last_name, first_name"),
	"ignored" => array("ignored", BOOL, "Ignored"),
	"inviter" => array("inviter", LINK, "Inviter", LINK_TABLE => "customer", LINK_LABEL => "last_name, first_name"),
	"invited_on" => array("invited_on", DATETIME, "Invited On"),
	"viewed_on" => array("viewed_on", DATETIME, "Viewed On"),
	"accepted_on" => array("accepted_on", DATETIME, "Accepted On"),
	"ignored_on" => array("ignored_on", DATETIME, "Ignored On"),
	"resent_on" => array("resent_on", DATETIME, "Resent On")
);

$SCHEMA['link_group'] = array(
	TABLE_LABEL => "Link Groups",
	TABLE_PARENT => "link",

	"name"	=> array("name", SENTENCE, "Name", REQUIRED),
	"rank"		=> array("rank", NUMBER, "Rank"),
	"visible"	=> array("visible", BOOL, "Visible")
);

$SCHEMA['link'] = array(
	TABLE_LABEL => "Links",
	TABLE_PARENT => "supplement",
	TABLE_SORT => "text",

	"link_group"	=> array("link_group", LINK, "Group",  LINK_TABLE => "link_group", LINK_LABEL => "name"),
	"URL"			=> array("URL", URL, "URL", REQUIRED, FIELD_NOTES => "(http://www.example.com)"),
	"text"			=> array("text", SENTENCE, "Link Text"),
	"description"	=> array("description", COPY, "Description", FIELD_GROUP => "Details"),
	"rank"			=> array("rank", NUMBER, "Rank"),
	"visible"		=> array("visible", BOOL, "Visible")
);

$SCHEMA['page'] = array(
	TABLE_LABEL => "Pages",
	TABLE_PARENT => "",

	"URL"	=> array("URL", URL, "URL", REQUIRED)
);

$SCHEMA['page_view'] = array(
	TABLE_LABEL => "Page Views",
	TABLE_PARENT => "page",

	"page"		=> array("page", LINK, "Page", LINK_TABLE => "page", LINK_LABEL => "URL"),
	"query_string"	=> array("query_string", NAME, "Query String"),
	"customer"	=> array("customer", LINK, "Customer", LINK_TABLE => "customer", LINK_LABEL => "last_name, first_name"),
	"ip_address"	=> array("ip_address", NAME, "IP Address"),
	"viewed_on"	=> array("viewed_on", DATETIME, "Viewed On"),
	"site_license"	=> array("site_license", LINK, "Site License"),
	"viewed_until"	=> array("viewed_until", DATETIME, "Viewed Until", FIELD_NOTES => "(Updated with login-ping)")
);

$SCHEMA['product'] = array(
	TABLE_LABEL => "Products",
	TABLE_PARENT => "customer",
	TABLE_ACCESS => ADMINISTRATOR,

	"title"			=> array("title", SENTENCE, "Title", REQUIRED, FIELD_UNIQUE => 1),
	"url_ID"		=> array("url_ID", RICH_URL_ID, "URL ID", OPTIONAL_HIDDEN, LINK_FIELD => "title", OPTIONAL_HIDDEN),
	"type"			=> array("type", ENUMERATION, "Type", REQUIRED, FIELD_OPTIONS => $PRODUCT_TYPES),
	"discipline"	=> array("discipline", SET, "Discipline", REQUIRED, SET_OPTIONS => $MEDICAL_SCIENCE_DISCIPLINES),
	"subscription_type"	=> array("subscription_type", ENUMERATION, "Subscription Type", FIELD_OPTIONS => $SUBSCRIPTION_TYPES, FIELD_ATTACHMENT => "type", FIELD_ATTACHMENT_VALUE => SUBSCRIPTION),
	"record_type"	=> array("record_type", ENUMERATION, "Record Type", REQUIRED, FIELD_OPTIONS => $PREMIUM_MATERIALS, FIELD_ATTACHMENT => "subscription_type", FIELD_ATTACHMENT_VALUE => INDIVIDUAL_RECORD),
	"permissions"	=> array("permissions", ENUMERATION, "Permissions", FIELD_OPTIONS => $PREMIUM_MATERIALS, SELECT_MULTIPLE => 1, SELECT_SIZE => 5, FIELD_NOTES => "<i>(Hold Shift / Ctrl to Select Multiple)</i>", FIELD_ATTACHMENT => "subscription_type", FIELD_ATTACHMENT_VALUE => "!" . INDIVIDUAL_RECORD),
	"description"	=> array("description", HTML_COPY, "Description", NOT_REQUIRED),
	"image"			=> array("image", IMAGE, "Image"),
	"download"		=> array("download", FILE, "Product Download", /* FIELD_NOTES => "(PDF file)", */ FIELD_ATTACHMENT => "type", FIELD_ATTACHMENT_VALUE => DOWNLOADABLE_PRODUCT),
	"duration"		=> array("duration", ENUMERATION, "Subscription Duration", REQURED, FIELD_OPTIONS => $SUBSCRIPTION_DURATIONS, FIELD_ATTACHMENT => "type", FIELD_ATTACHMENT_VALUE => SUBSCRIPTION),
	"price"			=> array("price", KMONEY, "Price", REQUIRED),	// FIELD_NOTES => "(0 if Free)"),
	"reward_points"	=> array("reward_points", NUMBER, "Ambassador Rewards", FIELD_DEFAULT => 1),
	// "renew_price"	=> array("renew_price", KMONEY, "Renewal Price", FIELD_ATTACHMENT => "type", FIELD_ATTACHMENT_VALUE => SUBSCRIPTION),
	// "upgradable_to"	=> array("upgradable_to", LINK, "Upgradable To", LINK_TABLE => "product", LINK_LABEL => "title", FIELD_ATTACHMENT => "type", FIELD_ATTACHMENT_VALUE => SUBSCRIPTION),
	// "upgrade_price"	=> array("upgrade_price", KMONEY, "Upgrade Price", REQUIRED, FIELD_ATTACHMENT => "upgradable_to", FIELD_ATTACHMENT_VALUE => ">0"),
	"visible"		=> array("visible", BOOL, "Visible")
);


/*
$SCHEMA['product_view'] = array(TABLE_LABEL => "Product Views", TABLE_PARENT => "customer");
$SCHEMA['product_view']['customer']	= array("customer", LINK, "Customer", LINK_TABLE => "customer", LINK_LABEL => "last_name, first_name", FIELD_STATUS => READ_ONLY)
$SCHEMA['product_view']['product']	= array("product", LINK, "Product", LINK_TABLE => "product", LINK_LABEL => "title", FIELD_STATUS => READ_ONLY);
$SCHEMA['product_view']['viewed_on']	= array("viewed_on", DATETIME, "Viewed On", FIELD_STATUS => READ_ONLY);
$SCHEMA['product_view']['IP']		= array("IP", NAME, "IP Address", FIELD_STATUS => READ_ONLY);
*/


$SCHEMA['purchase'] = array(
	TABLE_LABEL => "Subscriptions",
	TABLE_PARENT => "customer",
	TABLE_SORT => "customer",
	// TABLE_ACCESS => ADMINISTRATOR,

	"type"				=> array("type", ENUMERATION, "Subscription Type", FIELD_OPTIONS => $SUBSCRIPTION_PURCHASE_TYPES),
	"parent_type"		=> array("parent_type", ENUMERATION, "Parent Type", FIELD_OPTIONS => $GROUP_SUBSCRIPTION_TYPES, FIELD_ATTACHMENT => "type", FIELD_ATTACHMENT_VALUE => GROUP_MEMBER_SUBSCRIPTION),
	"parent"			=> array("parent", LINK, "Governing License", LINK_ATTACHMENT =>"parent_type", LINK_TABLE => array(GROUP_SUBSCRIPTION => "purchase", SITE_LICENSE => "site_license"), LINK_LABEL => array(GROUP_SUBSCRIPTION => "type institution", SITE_LICENSE => "institution"), FIELD_ATTACHMENT => "type", FIELD_ATTACHMENT_VALUE => GROUP_MEMBER_SUBSCRIPTION),
	"customer"			=> array("customer", LINK, "Customer", LINK_TABLE => "customer", LINK_LABEL => "last_name, first_name", LINK_SORT => "last_name", SUBTABLE_DEFAULT => "customer_ID", FIELD_ATTACHMENT => "type", FIELD_ATTACHMENT_VALUE => "!" . GROUP_SUBSCRIPTION),
	"institution"		=> array("institution", LINK, "Group", LINK_TABLE => "institution", LINK_LABEL => "name", LINK_SORT => "name", FIELD_ATTACHMENT => "type", FIELD_ATTACHMENT_VALUE => "!" . INDIVIDUAL_SUBSCRIPTION),
	"product"			=> array("product", LINK, "Product", LINK_TABLE => "product", LINK_LABEL => "title"),
	// Participation in studies.
	"participation"		=> array("participation", LINK_N_TO_N, "Study Participation", LINK_TABLE => "study", LINK_LABEL => "name", FIELD_GROUP => "Study Participation", LINK_OPTIONS => LINK_EXISTING_ONLY|LINK_EXPANDED),
	"reward"			=> array("reward", BOOL, "Ambassador Reward", FIELD_NOTES => "(This subscription was created as a reward)", FIELD_GROUP => "Ambassador Program"),
	"purchase_date"		=> array("purchase_date", DATETIME, "Purchase Date", GENERATED),
	"purchase_price"	=> array("purchase_price", KMONEY, "Purchase Price"),
	"record_type"		=> array("record_type", ENUMERATION, "Record Type", FIELD_OPTIONS => $PREMIUM_MATERIALS, FIELD_GROUP => "Deprecated"),	// , FIELD_STATUS => READ_ONLY),
	"record_ID"			=> array("record_ID", NUMBER, "Record ID", FIELD_GROUP => "Deprecated"),	// , FIELD_STATUS => READ_ONLY),
	"transaction_ID"	=> array("transaction_ID", SENTENCE, "Transaction ID", FIELD_ACCESS => DIETY, FIELD_STATUS => READ_ONLY),
	"signup_pin"		=> array("signup_pin", RANDOM_PIN, "Signup Code", FIELD_MAX_LENGTH => 8, FIELD_ATTACHMENT => "type", FIELD_ATTACHMENT_VALUE => GROUP_SUBSCRIPTION, FIELD_GROUP => "Group Settings"),
	"signup_duration"	=> array("signup_duration", ENUMERATION, "Signup Period Length", FIELD_OPTIONS => $SUBSCRIPTION_DURATIONS, FIELD_ATTACHMENT => "type", FIELD_ATTACHMENT_VALUE => GROUP_SUBSCRIPTION, FIELD_GROUP => "Group Settings"),
	"member_limit"		=> array("member_limit", NUMBER, "Member Limit", FIELD_ATTACHMENT => "type", FIELD_ATTACHMENT_VALUE => GROUP_SUBSCRIPTION, FIELD_GROUP => "Group Settings"),
	"device_limit"		=> array("device_limit", NUMBER, "Device Limit", FIELD_HELP => "The maximum number of devices that can be registered to this subscription", FIELD_DEFAULT => 5, FIELD_GROUP => "App. Settings"),
	"members"			=> array("members", NUMBER, "Members Subscribed", FIELD_STATUS => READ_ONLY, FIELD_ATTACHMENT => "type", FIELD_ATTACHMENT_VALUE => GROUP_SUBSCRIPTION, FIELD_GROUP => "Group Settings"),
);

//
$SCHEMA['referral'] = array(
    TABLE_LABEL => "Referrals",
    TABLE_PARENT => "ambassador",

    "ambassador"    => array("ambassador", LINK, "Ambassador", LINK_TABLE => "ambassador", LINK_LABEL => "customer"),
    "customer"      => array("customer", LINK, "Referred Customer", LINK_TABLE => "customer", LINK_LABEL => "last_name, first_name"),
    "coupon"        => array("coupon", LINK, "Referral Coupon", LINK_TABLE => "coupon", LINK_LABEL => "name"),
    "source"        => array("source", ENUMERATION, "Referral Source", FIELD_OPTIONS => $REFERRAL_SOURCES),
    "email"         => array("email", EMAIL, "E-mail"),
    "referred_on"   => array("referred_on", DATETIME, "Referred On"),
    "opened_on"     => array("opened_on", DATETIME, "Opened On"),
    "visited_on"    => array("visited_on", DATETIME, "Visited On"),
    "registered_on" => array("registered_on", DATETIME, "Registered On"),
    "purchased_on"  => array("purchased_on", DATETIME, "Purchased On"),
    "purchase"      => array("purchase", LINK, "Purchase", LINK_TABLE => "product", LINK_LABEL => "title")
);

$SCHEMA['testimonial'] = array(
	TABLE_LABEL => "Testimonials",
	TABLE_PARENT => "customer",
	TABLE_ACCESS => ADMINISTRATOR,

	"copy"		=> array("copy", COPY, "Testimonial", REQUIRED),
	"author"	=> array("author", NAME, "Author", REQUIRED),
	"credentials"	=> array("credentials",	NAME, "Author Credentials"),
	"visible"	=> array("visible", BOOL, "Visible")
);

$SCHEMA['transaction'] = array(
	TABLE_LABEL => "Transactions",
	TABLE_PARENT => "customer",
	TABLE_ACCESS => ADMINISTRATOR,

	"customer"		=> array("customer", LINK, "Customer", LINK_TABLE => "customer", LINK_LABEL => "last_name, first_name", SUBTABLE_DEFAULT => "customer_ID"),
	"purchase_date"	=> array("purchase_date", DATETIME, "Purchased On"),
	"order_subtotal"=> array("order_subtotal", MONEY, "Subtotal"),
	"order_taxes"	=> array("order_taxes", MONEY, "Taxes"),
	"order_shipping"=> array("order_shipping", MONEY, "Shipping"),
	"order_total"	=> array("order_total", MONEY, "Order Total"),
	"first_name"	=> array("first_name", NAME, "First Name", FIELD_GROUP => "Billing Info"),
	"last_name"	=> array("last_name", NAME, "Last Name", FIELD_GROUP => "Billing Info"),
	"card_type"	=> array("card_type", NAME, "Card Type", /*FIELD_OPTIONS => $CREDIT_CARDS,*/ FIELD_GROUP => "Billing Info"),
	"card_last_four"	=> array("card_last_four", NAME, "Card Last 4-Digits", FIELD_GROUP => "Billing Info"),
	"address"		=> array("address", SENTENCE, "Address", FIELD_GROUP => "Billing Info"),
	"city"		=> array("city", NAME, "City", FIELD_GROUP => "Billing Info"),
	"state"		=> array("state", NAME, "State", /*FIELD_OPTIONS => $STATES,*/ FIELD_GROUP => "Billing Info"),
	"postal_code"	=> array("postal_code", NAME, "Postal Code", FIELD_GROUP => "Billing Info"),
	"country"	=> array("country", NAME, "Country", FIELD_GROUP => "Billing Info"),
	"telephone"	=> array("telephone", TELEPHONE_NUMBER, "Telephone", FIELD_GROUP => "Billing Info"),
	"items"		=> array("items", LINK_N_TO_N, "Item", LINK_TABLE => "purchase", LINK_LABEL => "purchase_price", LINK_OPTIONS => LINK_EXPANDED|LINK_NEW_ONLY, FIELD_GROUP => "Items"),
	"coupons"	=> array("coupons", LINK_N_TO_N, "Coupon", LINK_TABLE => "coupon", LINK_LABEL => "name", LINK_OPTIONS => LINK_EXPANDED|LINK_EXISTING_ONLY, FIELD_GROUP => "Coupons"),
	"response"	=> array("response", COPY, "Transaction Response", FIELD_GROUP => "Response")
);


$SCHEMA['term'] = array(
	TABLE_LABEL => "Terms",
	TABLE_PARENT => "radiograph",
	TABLE_ACCESS => ADMINISTRATOR,

	"term"					=> array("term", NAME, "Term", REQUIRED),
	"url_ID"				=> array("url_ID", RICH_URL_ID, "URL ID", OPTIONAL_HIDDEN, LINK_FIELD => "term"),
	"abbreviation"			=> array("abbreviation",	NAME, "Abbreviation"),
	// "radiographs"		=> array("radiographs", LINK_N_TO_N, "Radiograph", LINK_TABLE => "radiograph", LINK_LABEL => "name"),
	"category_terms"		=> array("category_terms", LINK_N_TO_N, "Category", LINK_TABLE => "category", LINK_LABEL => "name", LINK_OPTIONS => LINK_EXPANDED|LINK_EXISTING_ONLY),
	"aliases"				=> array("aliases", LINK_ONE_TO_N, "Aliases", LINK_TABLE => "term_alias", LINK_LABEL => "alias", LINK_FIELD => "term", LINK_OPTIONS => LINK_EXPANDED|LINK_NEW_ONLY),
	"free_demo"				=> array("free_demo", BOOL, "Demo / Free", FIELD_GROUP => "Subscription"),
	"pronunciation"			=> array("pronunciation", NAME, "Phonetic Pronunciation", FIELD_NOTES => "(e.g., pro - nun - cee - ā - shun) āēīōū", FIELD_GROUP => "Pronunciation"),
	"pronunciation_audio"	=> array("pronunciation_audio", FILE, "Pronunciation Audio", FIELD_GROUP => "Pronunciation")
);


$SCHEMA['term_alias'] = array(
	TABLE_LABEL => "Term Aliases",
	TABLE_PARENT => "term",
	TABLE_ACCESS => ADMINISTRATOR,

	"term"	=> array("term", LINK, "Term", REQUIRED, LINK_TABLE => "term", LINK_LABEL => "term", LINK_SORT => "term", SUBTABLE_DEFAULT => "term_ID"),
	"alias"	=> array("alias", NAME, "Alias", REQUIRED)
);


$SCHEMA['radiograph'] = array(
	TABLE_LABEL => "Radiographs",
	RECORD_LABEL => "name",
	TABLE_PARENT => "materials",
	TABLE_SORT => "perspective, depth",
	TABLE_ACCESS => ADMINISTRATOR,

	"name"						=> array("name", NAME, "Name", REQUIRED),
	"url_ID"					=> array("url_ID", RICH_URL_ID, "URL ID", OPTIONAL_HIDDEN, LINK_FIELD => "name"),
	"perspective"				=> array("perspective", ENUMERATION, "Perspective", REQUIRED, FIELD_OPTIONS => $PERSPECTIVES),
	"depth"						=> array("depth", ENUMERATION, "Depth", REQUIRED, FIELD_OPTIONS => $CORONAL_DEPTHS), // $AXIAL_DEPTHS, OPTION_ATTACHMENT => "perspective", OPTION_ATTACHMENT_SOURCES => $PERSPECTIVE_DEPTH_MAP),
	"free_demo"					=> array("free_demo", BOOL, "Free Demo"),
	// "type"			=> array("type", ENUMERATION, "Type", FIELD_OPTIONS => $RADIOGRAPH_TYPES),
	/* General Radiograph Images */
	"radiograph"				=> array("radiograph", IMAGE, "Radiograph"),
	"outlined_radiograph"		=> array("outlined_radiograph", IMAGE, "Radiograph (Outlined)"),
	"annotations"				=> array("annotations", LINK_N_TO_N, "Annotation", LINK_TABLE => "annotation", LINK_LABEL => "label", LINK_FIELD => "outlined_radiograph", LINK_SORT => "label", LINK_OPTIONS => LINK_EXPANDED|LINK_NEW_ONLY),
	/* Superficial */
	"superficial"				=> array("superficial", IMAGE, "Superficial", FIELD_GROUP => "Superficial"),
	"superficial_outlined"		=> array("superficial_outlined", IMAGE, "Superficial (Outlined)", FIELD_GROUP => "Superficial"),
	"superficial_annotations"	=> array("superficial_annotations", LINK_N_TO_N,  "Annotation", LINK_TABLE => "annotation", LINK_LABEL => "label", LINK_FIELD => "superficial_outlined", LINK_SORT => "label", LINK_OPTIONS => LINK_EXPANDED|LINK_NEW_ONLY, FIELD_GROUP => "Superficial"),
	/* Deep */
	"deep"						=> array("deep", IMAGE, "Deep", FIELD_GROUP => "Deep"),
	"deep_outlined"				=> array("deep_outlined", IMAGE, "Deep (Outlined)", FIELD_GROUP => "Deep"),
	"deep_annotations"			=> array("deep_annotations", LINK_N_TO_N,  "Annotation", LINK_TABLE => "annotation", LINK_LABEL => "label", LINK_FIELD => "deep_outlined", LINK_SORT => "label", LINK_OPTIONS => LINK_EXPANDED|LINK_NEW_ONLY, FIELD_GROUP => "Deep"),
	/* CSF */
	"csf"						=> array("csf", IMAGE, "CSF", FIELD_GROUP => "CSF"),
	"csf_outlined"				=> array("csf_outlined", IMAGE, "CSF (Outlined)", FIELD_GROUP => "CSF"),
	"csf_annotations"			=> array("csf_annotations", LINK_N_TO_N,  "Annotation", LINK_TABLE => "annotation", LINK_LABEL => "label", LINK_FIELD => "csf_outlined", LINK_SORT => "label", LINK_OPTIONS => LINK_EXPANDED|LINK_NEW_ONLY, FIELD_GROUP => "CSF"),
	/* Special */
	"special"					=> array("special", IMAGE, "Special", FIELD_GROUP => "Special"),
	"special_outlined"			=> array("special_outlined", IMAGE, "Special (Outlined)", FIELD_GROUP => "Special"),
	"special_annotations" 		=> array("special_annotations", LINK_N_TO_N,  "Annotation", LINK_TABLE => "annotation", LINK_LABEL => "label", LINK_FIELD => "special_outlined", LINK_SORT => "label", LINK_OPTIONS => LINK_EXPANDED|LINK_NEW_ONLY, FIELD_GROUP => "Special"),
);

$SCHEMA['flash_card'] = array(TABLE_LABEL => "Flash Cards", TABLE_PARENT => "materials", TABLE_ACCESS => ADMINISTRATOR);
$SCHEMA['flash_card']['subject']	= array("subject", LINK, "Subject", REQUIRED, LINK_TABLE => "subject", LINK_LABEL => "subject");
$SCHEMA['flash_card']['image']		= array("image", IMAGE, "Question Image");
$SCHEMA['flash_card']['answer_image']	= array("answer_image", IMAGE, "Answer Image");
$SCHEMA['flash_card']['question']	= array("question", COPY, "Question", REQUIRED);
$SCHEMA['flash_card']['answer']		= array("answer", COPY, "Answer");
$SCHEMA['flash_card']['visible']	= array("visible", BOOL, "Visible");


$SCHEMA['image'] = array(
	TABLE_LABEL => "Images",
	TABLE_PARENT => "materials",
	TABLE_ACCESS => ADMINISTRATOR,
// $SCHEMA['image']['name']	= array("name", SENTENCE, "Name", FIELD_NOTES => "(How this image is identified internally)");
	"image"		=> array("image", IMAGE, "Image"),
	"caption"	=> array("caption", SENTENCE, "Caption")
);


$SCHEMA['lesson'] = array(
	TABLE_LABEL => "Tutorials",
	TABLE_PREPROCESSOR => "tag_lesson_terms.php",
	TABLE_PARENT => "materials",
	RECORD_LABEL => "title",

	"title"		=> array("title", SENTENCE, "Lesson Title", REQUIRED),
	// "tier"		=> array("tier", ENUMERATION, "Tier", REQUIRED, FIELD_OPTIONS => $LESSON_TIERS, FIELD_DEFAULT => LESSON_STANDARD),
	// "high_yield"	=> array("high_yield", BOOL, "High Yield"),
	"url_ID"		=> array("url_ID", RICH_URL_ID, "URL ID", OPTIONAL_HIDDEN, LINK_FIELD => "title"),
	"created_on"	=> array("created_on", DATE, "Posted On"),
	"overview"	=> array("overview", HTML_COPY, "Overview", FIELD_NOTES => "(Brief overview of tutorial)"),
	"introduction"=> array("introduction", HTML_COPY, "Tutorial Text"),
	// "discipline"	=> array("discipline", SET, "Discipline", REQUIRED, SET_OPTIONS => $MEDICAL_SCIENCE_DISCIPLINES),
	"subject"	=> array("subject", LINK, "Subject", LINK_TABLE => "subject", LINK_LABEL => "subject", FIELD_GROUP => "Deprecated"),
	"subject_order"	=> array("subject_order", NUMBER, "Subject Order", FIELD_GROUP => "Deprecated"),
// 	"introduction2"=> array("introduction2", SENTENCE, "Introduction2"),
// 	"video"	=> array("video", SERVER_FILE, "Video"),
	"web_video"	=> array("web_video", SERVER_FILE, "Video", FIELD_GROUP => "Deprecated"),	// , FIELD_EXTENSIONS => "flv"),
	"vimeo_video"	=> array("vimeo_video", COPY, "Vimeo Embed Code", FIELD_GROUP => "Video"),
	"images"	=> array("images", IMAGES, "Slides", ROOT_DIRECTORY => "/images/tutorials/", FIELD_GROUP => "Slides"),
	"visible"	=> array("visible", BOOL, "Visible"),
	"free_demo"	=> array("free_demo", BOOL, "Demo / Free", FIELD_GROUP => "Subscription"),
	"terms"		=> array("terms", LINK_N_TO_N, "Terms", LINK_TABLE => "term", LINK_LABEL => "term", LINK_OPTIONS => LINK_EXISTING_ONLY|LINK_EXPANDED|LINK_NO_FORM, FIELD_GROUP => "Terms"),
	"essential_advanced" => array("essential_advanced", MILITIME, "Essential / Advanced Breakpoint", FIELD_GROUP => "Breakpoints"),
	"breakpoints"	=> array("breakpoints", LINK_ONE_TO_N, "Smart Pause Breakpoints", LINK_TABLE => "breakpoint", LINK_FIELD => "lesson", LINK_LABEL => "time", FIELD_GROUP => "Breakpoints", LINK_OPTIONS => LINK_NEW_ONLY|LINK_EXPANDED),
	"questions" => array("questions", LINK_N_TO_N, "Quiz Questions", LINK_TABLE => "question", LINK_LABEL => "question", FIELD_GROUP => "Quiz", LINK_OPTIONS => LINK_EXISTING_ONLY|LINK_NO_FORM|LINK_EXPANDED)

// 	"comments']		=> array("comments", LINK_ONE_TO_N, "Lesson Comment", LINK_TABLE => "lesson_comment", LINK_FIELD => "lesson");
);

$SCHEMA['breakpoint'] = array(
	TABLE_LABEL => "Breakpoints",
	TABLE_ACCESS => DIETY,

	"lesson"	=> array("lesson", LINK, "Lesson", LINK_TABLE => "lesson", LINK_LABEL => "title", SUBTABLE_DEFAULT => "lesson_ID"),
	"time"		=> array("time", MILITIME, "Time"),
	"active"	=> array("active", BOOL, "Active", FIELD_DEFAULT_VALUE => 1)
);

$SCHEMA['lesson_view'] = array(
	TABLE_LABEL => "Lesson Views",
	TABLE_PARENT => "lesson",
	TABLE_ACCESS => ADMINISTRATOR,

	"customer"	=> array("customer", LINK, "Customer", LINK_TABLE => "customer", LINK_LABEL => "last_name, first_name"),
	"site_license" => array("site_license", LINK, "Site License", LINK_TABLE => "site_license", LINK_LABEL => "institution"),
	"anonymous_guid" => array("anonymous_guid", NAME, "Anonymous ID", FIELD_NOTES => "(For non-logged in users)"),
	"lesson"	=> array("lesson", LINK, "Lesson", LINK_TABLE => "lesson", LINK_LABEL => "title"),
	// "num_views" => array("num_views", NUMBER, "Num. Views"),
	"viewed_on"	=> array("viewed_on", DATETIME, "Viewed On"),
	"user_marked"	=> array("user_viewed", BOOL, "Marked as Viewed by User", FIELD_GROUP => "User Settings"),
	"user_cleared"	=> array("user_cleared", BOOL, "Cleared by User", FIELD_GROUP => "User Settings"),
	"cleared_on"	=> array("cleared_on", DATETIME, "Cleared On", FIELD_GROUP => "User Settings")
);

/*
$SCHEMA['lesson_comment'] = array(TABLE_LABEL => "Lesson Comments", TABLE_PARENT => "lesson");
$SCHEMA['lesson_comment']['lesson']		= array("lesson", LINK, "Lesson", LINK_TABLE => "lesson", LINK_LABEL => "title");
$SCHEMA['lesson_comment']['name']		= array("name", NAME, "Name", REQUIRED);
$SCHEMA['lesson_comment']['time']		= array("time", DATETIME, "Time", REQUIRED);
$SCHEMA['lesson_comment']['email']		= array("email", EMAIL, "E-mail", REQUIRED);
$SCHEMA['lesson_comment']['website']		= array("website", URL, "Website");
$SCHEMA['lesson_comment']['comment']		= array("comment", COPY, "Comment", REQUIRED);
$SCHEMA['lesson_comment']['approved']		= array("approved", BOOL, "Approved");

$SCHEMA['drawing_tutorial'] = array(TABLE_LABEL => "Drawing Tutorials", TABLE_PARENT => "lesson");
$SCHEMA['drawing_tutorial']['type']		= array("type", ENUMERATION, "Tutorial Type", FIELD_OPTIONS => $TUTORIAL_TYPES);
$SCHEMA['drawing_tutorial']['title']		= array("title", SENTENCE, "Title", REQUIRED);
$SCHEMA['drawing_tutorial']['created_on']		= array("created_on", DATE, "Posted On");
$SCHEMA['drawing_tutorial']['url_ID']		= array("url_ID", RICH_URL_ID, "URL ID", OPTIONAL_HIDDEN, LINK_FIELD => "title");
$SCHEMA['drawing_tutorial']['subject']		= array("subject", LINK, "Subject", LINK_TABLE => "subject", LINK_LABEL => "subject");
$SCHEMA['drawing_tutorial']['description']	= array("description", HTML_COPY, "Decription");
// $SCHEMA['drawing_tutorial']['slides']	= array("slides", LINK_ONE_TO_N, "Slide", LINK_TABLE => "slide", LINK_LABEL => "name", LINK_FIELD => "tutorial");
$SCHEMA['drawing_tutorial']['images']	= array("images", IMAGES, "Slides", ROOT_DIRECTORY => "/images/tutorials/");
$SCHEMA['drawing_tutorial']['free_demo']= array("free_demo", BOOL, "Demo / Free", FIELD_GROUP => "Subscription");
$SCHEMA['drawing_tutorial']['visible']	= array("visible", BOOL, "Visible");
*/

$SCHEMA['question'] = array(
	TABLE_LABEL => "Questions",
	TABLE_PARENT => "test",
	TABLE_ACCESS => ADMINISTRATOR,

	"type"		=> array("type", ENUMERATION, "Question Format", REQUIRED, FIELD_DEFAULT => MULTIPLE_CHOICE, FIELD_OPTIONS => $QUESTION_FORMATS),
	"question"	=> array("question", COPY, "Question", REQUIRED),
	"image"		=> array("image", IMAGE, "Image"),
	"a"		=> array("a", SENTENCE, "a.)", REQUIRED, FIELD_ATTACHMENT => "type", FIELD_ATTACHMENT_VALUE => MULTIPLE_CHOICE),
	"b"		=> array("b", SENTENCE, "b.)", REQUIRED, FIELD_ATTACHMENT => "type", FIELD_ATTACHMENT_VALUE => MULTIPLE_CHOICE),
	"c"		=> array("c", SENTENCE, "c.)", FIELD_ATTACHMENT => "type", FIELD_ATTACHMENT_VALUE => MULTIPLE_CHOICE),
	"d"		=> array("d", SENTENCE, "d.)", FIELD_ATTACHMENT => "type", FIELD_ATTACHMENT_VALUE => MULTIPLE_CHOICE),
	"e"		=> array("e", SENTENCE, "e.)", FIELD_ATTACHMENT => "type", FIELD_ATTACHMENT_VALUE => MULTIPLE_CHOICE),
	"f"		=> array("f", SENTENCE, "f.)", FIELD_ATTACHMENT => "type", FIELD_ATTACHMENT_VALUE => MULTIPLE_CHOICE),
	"answer"	=> array("answer", ENUMERATION, "Answer", REQUIRED, FIELD_OPTIONS => $TRUE_FALSE_ANSWERS, OPTION_ATTACHMENT => "type", OPTION_ATTACHMENT_SOURCES => array(TRUE_OR_FALSE => $TRUE_FALSE_ANSWERS, MULTIPLE_CHOICE => $MULTIPLE_CHOICE_ANSWERS)),
	"difficulty"	=> array("difficulty", ENUMERATION, "Difficulty", FIELD_OPTIONS => array(1, 2, 3, 4, 5)),
	"lesson"		=> array("lesson", LINK, "Relevant Tutorial", LINK_TABLE => "lesson", LINK_LABEL => "title", LINK_SORT => "title", FIELD_GROUP => "Deprecated"),
	"terms"			=> array("terms", LINK_N_TO_N, "Related Terms", LINK_TABLE => "term", LINK_LABEL => "term", LINK_OPTIONS => LINK_EXPANDED|LINK_EXISTING_ONLY, FIELD_GROUP => "Review"),
	"explaination"	=> array("explaination", COPY, "Explaination", FIELD_GROUP => "Review")
);

$SCHEMA['study_plan'] = array(
	TABLE_LABEL => "Study Plans",
	TABLE_PARENT => "lesson",
	TABLE_ACCESS => ADMINISTRATOR,

	"name"		=> array("name", NAME, "Plan Name", REQUIRED),
	"url_ID"	=> array("url_ID", RICH_URL_ID, "URL ID", OPTIONAL_HIDDEN, LINK_FIELD => "name"),
	"institution" => array("institution", LINK, "Institution", LINK_TABLE => "institution", LINK_LABEL => "name"),
	"student_type" => array("student_type", ENUMERATION, "Student Type", REQUIRED, FIELD_OPTIONS => $STUDENT_TYPES),
	"type"		=> array("type", ENUMERATION, "Type", REQUIRED, FIELD_OPTIONS => $STUDY_PLAN_TYPES),
	"discipline"	=> array("discipline", SET, "Discipline", REQUIRED, SET_OPTIONS => $MEDICAL_SCIENCE_DISCIPLINES),
	"study_time"	=> array("study_time", NAME, "Video / Study Time"),
	"lessons"	=> array("lessons", LINK_N_TO_N, "Lessons", LINK_TABLE => "lesson", LINK_LABEL => "title", LINK_OPTIONS => LINK_EXPANDED|LINK_NO_FORM|LINK_EXISTING_ONLY, FIELD_GROUP => "Lessons"),
	"visible"	=> array("visible", BOOL, "Visible")
);


$SCHEMA['survey'] = array(
	TABLE_LABEL => "Surveys",
	TABLE_PARENT => "materials",

	"name"				=> array("name", NAME, "Name"),
	"url_ID"			=> array("url_ID", RICH_URL_ID, "URL ID", OPTIONAL_HIDDEN, LINK_FIELD => "name"),
	"introduction"		=> array("introduction", HTML_COPY, "Survey Introduction"),
	"post_survey"		=> array("post_survey", HTML_COPY, "Post Survey Copy", FIELD_NOTES => "(Displayed after survey is taken)"),
	"questions"			=> array("questions", LINK_ONE_TO_N, "Questions", LINK_TABLE => "survey_question", LINK_LABEL => "question", LINK_OPTIONS => LINK_EXPANDED|LINK_NEW_ONLY, LINK_FIELD => "survey", FIELD_GROUP => "Questions"),
	"account_required"	=> array("account_required", BOOL, "Account Required", FIELD_NOTES => "(A user must be logged in to take survey)"),
	"resubmissions_allowed" => array("resubmissions_allowed", BOOL, "Resubmissions Allowed", FIELD_NOTES => "(A user may re-take the survey)", FIELD_GROUP => "Resubmission"),
	"max_submissions"	=> array("max_submissions", NUMBER, "Max. Submissions", FIELD_NOTES => "(Maximum number of submissions)", FIELD_GROUP => "Resubmission", FIELD_ATTACHMENT => array("resubmissions_allowed" => 1)),
	"resubmission_timeout"	=> array("resubmission_timeout", ENUMERATION, "Resubmission Timeout", FIELD_OPTIONS => $SUBSCRIPTION_DURATIONS, FIELD_NOTES => "(The amount of time a user must wait to re-take the survey)", FIELD_ATTACHMENT => array("resubmissions_allowed" => 1), FIELD_GROUP => "Resubmission"),
	"closed"			=> array("closed", BOOL, "Closed", FIELD_NOTES => "(No longer available to be taken)")
);

// A study is a set of one or more surveys that typically apply to one or more group subscriptions.
$SCHEMA['study'] = array(
	TABLE_LABEL => "Studies",
	TABLE_PARENT => "survey",
	TABLE_SORT => "",

	"name"				=> array("name", NAME, "Study Name", REQUIRED),
	"url_ID"			=> array("url_ID", RICH_URL_ID, "URL ID", OPTIONAL_HIDDEN, LINK_FIELD => "name"),
	// TODO: Need to fix recursion problem before including this.
	// "participation"		=> array("participation", LINK_N_TO_N, "Participants", LINK_TABLE => "purchase", LINK_LABEL => "purchased_on", LINK_OPTIONS => LINK_EXISTING_ONLY|LINK_EXPANDED, FIELD_GROUP => "Participants"),
	"opt_in_agreement"	=> array("opt_in_agreement", HTML_COPY, "Intro/Opt-in Agreement"),
	"opt_in_text"		=> array("opt_in_text", SENTENCE, "Opt-in Text", FIELD_NOTES => "(<i>Optional</i> e.g., I agree)"),
	"opt_out_text"		=> array("opt_out_text", SENTENCE, "Opt-out Text", FIELD_NOTES => "(<i>Optional</i> e.g., I do NOT agree)"),
	"surveys"			=> array("surveys", LINK_ONE_TO_N, "Surveys", LINK_TABLE => "scheduled_survey", LINK_LABEL => "name", LINK_FIELD => "study", LINK_OPTIONS => LINK_NEW_ONLY|LINK_EXPANDED, FIELD_GROUP => "Surveys"),
	"opt_in_required"	=> array("opt_in_required", BOOL, "Opt-in required", FIELD_NOTES => "(User must opt in - if unchecked, participation is required)"),
	"closed"			=> array("closed", BOOL, "Closed", FIELD_NOTES => "(Is this study over?)")
);

// Study consent is a user opting into or out of an optional study.
$SCHEMA['study_consent'] = array(
	TABLE_LABEL => "Study Consent",
	TABLE_PARENT => "study",

	"study"		=> array("study", LINK, "Study", REQUIRED, LINK_TABLE => "study", LINK_LABEL => "name"),
	"customer"	=> array("customer", LINK, "Customer", REQUIRED, LINK_TABLE => "customer", LINK_LABEL => "last_name, first_name"),
	"date"		=> array("date", DATETIME, "Date", REQUIRED),
	"opted_in"	=> array("opted_in", BOOL, "Opted in", FIELD_NOTES => "(Whether or not the user has consented to participating in the study)"),
	"current"	=> array("current", BOOL, "Current", FIELD_NOTES => "(Is this the most current consent record?)")
);

// A survey that is presented to the user at a given time.
$SCHEMA['scheduled_survey'] = array(
	TABLE_LABEL => "Scheduled Survey",
	TABLE_PARENT => "study",
	TABLE_SORT => "",

	"name"				=> array("name", NAME, "Scheduled Survey Name", REQUIRED),
	"study"				=> array("study", LINK, "Study", REQUIRED, LINK_TABLE => "study", LINK_LABEL => "name", SUBTABLE_DEFAULT => "study_ID"),
	"survey"			=> array("survey", LINK, "Survey", REQUIRED, LINK_TABLE => "survey", LINK_LABEL => "name"),
	"schedule_type"		=> array("schedule_type", ENUMERATION, "Schedule Type", FIELD_OPTIONS => $SCHEDULE_TYPES),
	"schedule_date"		=> array("schedule_date", DATE, "Specific Date", FIELD_ATTACHMENT => array("schedule_type" => SCHEDULE_DATE)),
	"schedule_month"	=> array("schedule_month", ENUMERATION, "Annual Date", FIELD_OPTIONS => $MONTHS, FIELD_NO_CLEAR => 1, FIELD_ATTACHMENT => array("schedule_type" => SCHEDULE_ANNUAL_DATE)),
	"schedule_day"		=> array("schedule_day", ENUMERATION, FIELD_OPTIONS => $MONTH_DAYS_31, FIELD_ATTACHMENT => array("schedule_type" => SCHEDULE_ANNUAL_DATE),
								 OPTION_ATTACHMENT => "schedule_month", OPTION_ATTACHMENT_SOURCES => $MONTH_DAYS, FIELD_NO_LABEL => 1),
	"signup_offset"		=> array("signup_offset", ENUMERATION, "Signup Offset", REQUIRED, FIELD_OPTIONS => $SCHEDULE_OFFSETS, FIELD_ATTACHMENT => array("schedule_type" => SCHEDULE_SIGNUP_OFFSET)),
	"force_response"		=> array("force_response", BOOL, "Force Response", FIELD_NOTES => "(Force user to take the survey before continuing usage - respects grace-period, below)"),
	"grace_period"		=> array("grace_period", ENUMERATION, "Grace Period", FIELD_NOTES => "(Allow regular use until this amount of time transpires)", FIELD_OPTIONS => $SURVEY_GRACE_PERIODS, FIELD_ATTACHMENT => array("force_response" => 1))
);

$SCHEMA['survey_question'] = array(
	TABLE_LABEL => "Survey Questions",
	TABLE_PARENT => "survey",

	"survey"	=> array("survey", LINK, "Survey", REQUIRED, LINK_TABLE => "survey", LINK_LABEL => "name", SUBTABLE_DEFAULT => "survey_ID"),
	"format"	=> array("format", ENUMERATION, "Format", REQUIRED, FIELD_OPTIONS => $QUESTION_FORMATS),
	"question"	=> array("question", SENTENCE, "Question", REQUIRED),
	"type"		=> array("type", ENUMERATION, "Option type", FIELD_OPTIONS => $RATE_FORMATS, FIELD_ATTACHMENT => array("format" => RATE_1_5)),
	"display"	=> array("display", ENUMERATION, "Display Style", FIELD_OPTIONS => $OPTION_DISPLAY_TYPES, FIELD_ATTACHMENT => array("format" => RATE_1_5)), // FIELD_GROUP => "Display Options"),
	"placeholder" => array("placeholder", NAME, "Placeholder Text", FIELD_ATTACHMENT => array("format" => array(SHORT_ANSWER, FREE_RESPONSE))), // FIELD_GROUP => "Display Options"),
	"options"	=> array("options", LINK, "Options", LINK_TABLE => "survey_option_set", LINK_LABEL => "name", LINK_SORT => "name", FIELD_ATTACHMENT => array("format" => RATE_1_5, "type" => RATE_CUSTOM)),

);

$SCHEMA['survey_option_set'] = array(
	TABLE_LABEL => "Survey Options",
	TABLE_PARENT => "survey",

	"name"		=> array("name", NAME, "Name"),
	"options"	=> array("options", LINK_ONE_TO_N, "Options", LINK_TABLE => "survey_option", LINK_LABEL => "option_text", LINK_FIELD => "option_set", LINK_OPTIONS => /*LINK_INLINE|*/ LINK_EXPANDED|LINK_NEW_ONLY)
);

$SCHEMA['survey_option'] = array(
	TABLE_LABEL => "Survey Options",
	TABLE_PARENT => "survey_option_set",
	TABLE_ACCESS => DIETY,

	"option_set"	=> array("option_set", LINK, "Option Set", LINK_TABLE => "option_set", LINK_LABEL => "name", SUBTABLE_DEFAULT => "survey_option_set_ID"),
	"option_value"	=> array("option_value", NAME, "Option Value"),
	"option_text"	=> array("option_text", SENTENCE, "Option Text", REQUIRED)
);

$SCHEMA['survey_response'] = array(
	TABLE_LABEL => "Survey Responses",
	TABLE_PARENT => "survey",

	"customer"	=> array("customer", LINK, "Customer", LINK_TABLE => "customer", LINK_LABEL => "last_name, first_name"),
	"survey"	=> array("survey", LINK, "Survey", LINK_TABLE => "survey", LINK_LABEL => "name"),
	"ip_address"=> array("ip_address", NAME, "IP Address", FIELD_NOTES => "For non-user responses"),
	"taken_on"	=> array("taken_on", DATETIME, "Taken on"),
	"answers"	=> array("answers", LINK_ONE_TO_N, "Answers", LINK_TABLE => "survey_answer", LINK_FIELD => "response", LINK_LABEL => "question answer", LINK_OPTIONS => LINK_FULLY_EXPANDED, FIELD_GROUP => "Answers")
);

$SCHEMA['survey_answer'] = array(
	TABLE_LABEL => "Survey Answers",
	TABLE_PARENT => "survey_response",
	TABLE_ACCESS => DIETY,

	"response"	=> array("response", LINK, "Survey Response", LINK_TABLE => "survey_response", LINK_LABEL => "customer taken_on", SUBTABLE_DEFAULT => "survey_response_ID", LINK_OPTIONS => LINK_READ_ONLY),
	"question"	=> array("question", LINK, "Question", LINK_TABLE => "survey_question", LINK_LABEL => "question"),
	"answer"	=> array("answer", SENTENCE, "Answer")
);

$SCHEMA['test'] = array(
	TABLE_LABEL => "Exams",
	TABLE_ACCESS => ADMINISTRATOR,
	TABLE_PARENT => "materials",

	"discipline"	=> array("discipline", SET, "Discipline", REQUIRED, SET_OPTIONS => $MEDICAL_SCIENCE_DISCIPLINES),
	"type"		=> array("type", ENUMERATION, "Type", FIELD_OPTIONS => $TEST_TYPES),
	"subject_group"	=> array("subject_group", LINK, "Subject Group", LINK_TABLE => "subject_group", LINK_LABEL => "name", FIELD_ATTACHMENT => "type", FIELD_ATTACHMENT_VALUE => SUBJECT_SELF_ASSESSMENT),
	"title"		=> array("title", NAME, "Title", REQUIRED, FIELD_ATTACHMENT => "type", FIELD_ATTACHMENT_VALUE => "!" . SUBJECT_SELF_ASSESSMENT),
	"url_ID"	=> array("url_ID", RICH_URL_ID, "URL ID", OPTIONAL_HIDDEN, LINK_FIELD => "title"),
	"questions"	=> array("questions", LINK_N_TO_N, "Questions", LINK_TABLE => "question", LINK_LABEL => "question", LINK_OPTIONS => LINK_EXPANDED|LINK_NO_FORM),
	"free_demo"	=> array("free_demo", BOOL, "Free / Demo"),
	"visible"	=> array("visible", BOOL, "Visible")
);


$SCHEMA['test_result'] = array(
	TABLE_LABEL => "Test Result",
	TABLE_PARENT => "test",
	TABLE_ACCESS => ADMINISTRATOR,

	"customer"	=> array("customer", LINK, "Customer", LINK_TABLE => "customer", LINK_LABEL => "first_name last_name"),
	"started_on"	=> array("started_on", DATETIME, "Started On"),
	"submitted_on"	=> array("submitted_on", DATETIME, "Submitted On"),
	"unique_ID"		=> array("unique_ID", "CHAR(32)", "Unique ID"),
	"type"		=> array("type", ENUMERATION, "Test Type", FIELD_OPTIONS => $TEST_TYPES),
	"test"		=> array("test", LINK, "Test", LINK_TABLE => "test", LINK_LABEL =>"title", FIELD_ATTACHMENT => "type", FIELD_ATTACHMENT_VALUE => "!" . POST_TUTORIAL_QUIZ),
	// Lesson quizzes are ad-hoc.
	"lesson"	=> array("lesson", LINK, "Lesson", LINK_TABLE => "lesson", LINK_LABEL => "title", FIELD_ATTACHMENT => "type", FIELD_ATTACHMENT_VALUE => POST_TUTORIAL_QUIZ),
    "pre_test"  => array("pre_test", BOOL, "Pre-test"),
	"grade"		=> array("grade", "DECIMAL(3, 1)", "Grade"),
	"answers"	=> array("answers", LINK_ONE_TO_N, "Answers", LINK_TABLE => "question_answer", LINK_LABEL => "answer"),
	"anonymous_guid" => array("anonymous_guid", NAME, "Anonymous ID", FIELD_NOTES => "(For non-logged in users)")
);

$SCHEMA['test_result_permission'] = array(
	TABLE_LABEL => "Test Result Permissions",
	TABLE_PARENT => "test_result",
	TABLE_ACCESS => ADMINISTRATOR,

	"test_result"	=> array("test_result", LINK, "Test Result", LINK_TABLE => "test_result", LINK_LABEL => "customer (submitted_on)"),
	"email"		    => array("email", EMAIL, "Recipient Email"),
    // "course_director"   => array("course_director", LINK_N_TO_N, "Course Director", LINK_TABLE => "course_director", LINK_LABEL => "customer")
);

$SCHEMA['question_answer'] = array(
	TABLE_LABEL => "Question Answers",
	TABLE_PARENT => "subject",
	TABLE_ACCESS => DIETY,

	"test_result"	=> array("test_result", LINK, "Test Result", LINK_TABLE => "test_result", LINK_LABEL => "submitted_on"),
	"question"	=> array("question", LINK, "Question", LINK_TABLE => "question", LINK_LABEL => "question"),
	"answer"	=> array("answer", NAME, "Answer"),
	"correct"	=> array("correct", BOOL, "Correct")
);


$SCHEMA['site_license'] = array(
	TABLE_LABEL => "Site Licenses",
	TABLE_PARENT => "purchase",
	TABLE_ACCESS => ADMINISTRATOR,

	"institution_name"	=> array("institution_name", NAME, "Institution"),
	"institution"	=> array("institution", LINK, "Institution", LINK_TABLE => "institution", LINK_LABEL => "name", LINK_SORT => "name"),
	"product"	=> array("product", LINK, "Product", LINK_TABLE => "product", LINK_LABEL => "title", LINK_SORT => "title"),
	"type"		=> array("type", ENUMERATION, "License Type", FIELD_OPTIONS => $SITE_LICENSE_TYPES),
	"passphrase"	=> array("passphrase", MD5_PASSWORD, "Passphrase", FIELD_ATTACHMENT => "type", FIELD_ATTACHMENT_VALUE => INTRANET_REFERRAL),
	"access_URL"	=> array("access_URL", HTML, "<div class=\"field_label\">Access URL</div><div class=\"field_input\">http://www.drawittoknowit.com/site-license.php?code=<%passphrase%></div><div class=\"clear\"></div>", FIELD_ATTACHMENT => "type", FIELD_ATTACHMENT_VALUE => INTRANET_REFERRAL),
	"referer_URL"	=> array("referer_URL", URL, "Referring Page URL", FIELD_ATTACHMENT => "type", FIELD_ATTACHMENT_VALUE => INTRANET_REFERRAL),
	"granted_on"	=> array("granted_on", DATETIME, "Granted On"),
	"duration"	=> array("duration", ENUMERATION, "License Duration", FIELD_OPTIONS => $SUBSCRIPTION_DURATIONS),
	"ip_ranges"	=> array("ip_ranges", LINK_N_TO_N, "IP Ranges", LINK_TABLE => "ip_range", LINK_LABEL => "label", LINK_OPTIONS => LINK_EXPANDED|LINK_NEW_ONLY, FIELD_ATTACHMENT => "type", FIELD_ATTACHMENT_VALUE => IP_RANGE)
);

$SCHEMA['ip_range'] = array(
	TABLE_LABEL => "IP Ranges",
	TABLE_PARENT => "site_license",
	TABLE_ACCESS => DIETY,

	"label"		=> array("label", NAME, "Label", FIELD_NOTES => "(For internal identification only.)"),
	"type"		=> array("type", ENUMERATION, "Range Type", FIELD_OPTIONS => $IP_RANGE_TYPES),
	"ip_start"	=> array("ip_start", NAME, "IP", FIELD_NOTES => "(Omit '*' placeholders, e.g., 128.154.17.)"),
	"ip_end"	=> array("ip_end", NAME, "IP End", FIELD_ATTACHMENT => "type", FIELD_ATTACHMENT_VALUE => IP_RANGE)
);

/* $SCHEMA['gross_anatomy'] = array(
	TABLE_NAME => "subject",
	TABLE_LABEL => "Gross Anatomy Units",
	TABLE_PARENT => "subject_group",
	RECORD_WHERE => "WHERE group = 1"
); */

$SCHEMA['subject'] = array(
	TABLE_LABEL => "Units",
	TABLE_PARENT => "curriculum",
	TABLE_ACCESS => ADMINISTRATOR,
	// RECORD_WHERE => "WHERE curriculum IS NOT NULL",
	RECORD_LABEL => "subject",

	"subject_group"		=> array("subject_group", LINK, "Subject", LINK_TABLE => "subject_group", LINK_LABEL => "name", SUBTABLE_DEFAULT => "subject_group_ID"),
	"subject"		=> array("subject", SENTENCE, "Unit Name", REQUIRED),

	// "chapter"		=> array("chapter", NAME, "Chapter Num.");
	"visible"		=> array("visible", BOOL, "Visible"),
	"lessons"		=> array("lessons", LINK_N_TO_N, "Tutorials", LINK_TABLE => "lesson", LINK_LABEL => "title", LINK_MAP_TABLE => "subject_lesson", LINK_MAP_SORT => "sort_order", LINK_LOCAL_KEY => "subject", LINK_FOREIGN_KEY => "lesson", LINK_OPTIONS => LINK_NO_FORM|LINK_EXPANDED|LINK_EXISTING_ONLY, LINK_SORT => "title", FIELD_GROUP => "Tutorials"),
	// "lesson"		=> array("lesson", LINK_ONE_TO_N, "Lesson", LINK_TABLE => "lesson", LINK_FIELD => "subject", LINK_LABEL => "title", LINK_OPTIONS => LINK_EXISTING_ONLY|LINK_EXPANDED, FIELD_GROUP => "Deprecated")
);

// $SCHEMA[''] =

$SCHEMA['curriculum'] = array(
	TABLE_LABEL => "Courses",
	TABLE_PARENT => "materials",
	TABLE_ACCESS => ADMINISTRATOR,
	RECORD_LABEL => "name",

	// "division"	=> array("division", SET, "Division", REQUIRED, FIELD_OPTIONS => $DIVISIONS),
	"name"	=> array("name", NAME, "Course Name", REQUIRED),
	// "subtitle"	=> array("subtitle", NAME, "Subtitle", FIELD_NOTES => "(Optional)"),
	"url_ID"	=> array("url_ID", RICH_URL_ID, "URL ID", OPTIONAL_HIDDEN, LINK_FIELD => "name"),
	"image"	=> array("image", IMAGE, "Thumbnail Image", FIELD_GROUP => "Images"),
	"description" => array("description", HTML_COPY, "Course Overview")
	// "subjects"	=> array("subjects", LINK_N_TO_N, "Subjects", LINK_TABLE => "subject", LINK_LABEL => "subject - internal_name", LINK_OPTIONS => LINK_EXISTING_ONLY|LINK_EXPANDED),
	// "sort_order"	=> array("sort_order", NUMBER, "Sort Order", FIELD_DEFAULT => 999)
);

$SCHEMA['subject_group'] = array(
	TABLE_LABEL => "Subjects",
	TABLE_PARENT => "curriculum",
	RECORD_LABEL => "name",
	TABLE_SORT => "sort_order",

	//"division"	=> array("division", ENUMERATION, "Division", REQUIRED, FIELD_OPTIONS => $DIVISIONS),
	"curriculum"	=> array("curriculum", LINK, "Course", REQUIRED, LINK_TABLE => "curriculum", LINK_LABEL => "name"),
	"name"	=> array("name", NAME, "Subject Name"),
	"subtitle"	=> array("subtitle", NAME, "Subtitle"),
	"url_ID"	=> array("url_ID", RICH_URL_ID, "URL ID", OPTIONAL_HIDDEN, LINK_FIELD => "name"),
	"image"	=> array("image", IMAGE, "Thumbnail Image", FIELD_GROUP => "Images"),
	"svg_image"	=> array("svg_image", IMAGE, "Mobile Image", FIELD_GROUP => "Images"),
	"subjects"	=> array("subjects", LINK_ONE_TO_N, "Subjects", LINK_TABLE => "subject", LINK_LABEL => "subject", LINK_FIELD => "subject_group", LINK_SORT => "", LINK_OPTIONS => LINK_EXISTING_ONLY|LINK_EXPANDED),
	/* "highlights"	=> array("highlights", COPY, "Know-It Bulletpoints", FIELD_NOTES => "(Separate points on new lines)"), */
	"sort_order"	=> array("sort_order", NUMBER, "Sort Order", FIELD_DEFAULT => 999)
);

$SCHEMA['subject_lesson'] = array(
	TABLE_LABEL => "Tutorial Subjects",
	TABLE_ACCESS => DIETY,
	TABLE_SORT => "",

	"lesson"	=> array("lesson", LINK, "Lesson", LINK_TABLE => "lesson", LINK_LABEL => "title"),
	"subject"	=> array("subject", LINK, "Subject", LINK_TABLE => "subject", LINK_LABEL => "subject"),
	"sort_order"	=> array("sort_order", NUMBER, "Sort Order", FIELD_DEFAULT => 999)
);

$SCHEMA['supplement'] = array(TABLE_LABEL => "Supplements", TABLE_ACCESS => ADMINISTRATOR);
$SCHEMA['supplement']['subject']	= array("subject", LINK, "Subject", REQUIRED, LINK_TABLE => "subject", LINK_LABEL => "subject");
// $SCHEMA['supplement']['type']		= array("type", ENUMERATION, "Type", FIELD_OPTIONS => $SUPPLEMENT_TYPES);
$SCHEMA['supplement']['title']		= array("title", SENTENCE, "Title");
$SCHEMA['supplement']['supplement']	= array("supplement", FILE, "Supplement", FIELD_NOTES => "(PDF File)");		// , FIELD_EXTENSIONS => "pdf");
$SCHEMA['supplement']['visible']	= array("visible", BOOL, "Visible");

$SCHEMA['user_session'] = array(
	TABLE_LABEL => "User Sessions",
	TABLE_PARENT => "customer",

	"customer"	=> array("customer", LINK, "Customer", REQUIRED, LINK_TABLE => "customer", LINK_LABEL => "last_name, first_name"),
	"session_ID"	=> array("session_ID", "CHAR(40)", "Session ID"),
	"ip_address"	=> array("ip_address", "CHAR(20)", "IP Address"),
	"login_date"	=> array("login_date", DATETIME, "Login Date"),
	"logged_out"	=> array("logged_out", BOOL, "Logged Out"),
	"logout_date"	=> array("logout_date", DATETIME, "Logout Date")
);

//
$SCHEMA['user'] = array(TABLE_LABEL => "Users", TABLE_ACCESS => ADMINISTRATOR);
$SCHEMA['user']['first_name']		= array("first_name", NAME, "First Name", REQUIRED);
$SCHEMA['user']['last_name']		= array("last_name", NAME, "Last Name", REQUIRED);
$SCHEMA['user']['email']		= array("email", EMAIL, "E-mail", REQUIRED);
$SCHEMA['user']['username']		= array("username", NAME, "Username", FIELD_ACCESS => ADMINISTRATOR);
$SCHEMA['user']['password']		= array("password", MD5_PASSWORD, "Password", REQUIRED, FIELD_ACCESS => ADMINISTRATOR);
$SCHEMA['user']['permissions']		= array("permissions", TINY_NUMBER, "Permissions");

?>
