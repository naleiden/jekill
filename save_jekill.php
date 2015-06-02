<?php

$content_page = $_POST['jContentPage'];
$content_page_path = $_POST['jContentPath'];

if ($content_page_path != "")
	$content_page_path = "{$content_page_path}/";

$include_file = "{$_SERVER['DOCUMENT_ROOT']}/{$content_page_path}{$content_page}.inc";

include($include_file);

$page_handle = fopen($include_file, "w");

unset($_POST['jContentPage']);
unset($_POST['jContentPath']);

$encoded = array(
	"&amp;",
	"%u00A9",	// copy
	"%u2013",	// mdash
	"%u2018",	// lsquo
	"%u2019",	// rsquo
	"%u201C",	// ldquo
	"%u201D",	// rdquo
	"%u203A",	// raquo
	"%u2022",	// &bull;
	"%u2122"
);

$html_char = array(
	"&",
	"&copy;",
	"&mdash;",
	"&lsquo;",
	"&rsquo;",
	"&ldquo;",
	"&rdquo;",
	"&raquo;",
	"&bull;",
	"&trade;"
);

foreach ($_POST AS $key => $value) {
	$encoded_value = urldecode($value);
	$encoded_value = str_replace($encoded, $html_char, $encoded_value);
	$encoded_value = stripslashes($encoded_value);
	$encoded_value = trim($encoded_value);
	$_JCONTENT["{$content_page}:{$key}"] = $encoded_value;
}

$page_content = "";
foreach ($_JCONTENT AS $key => $value) {
	$encoded_value = str_replace("\"", "\\\"", $value);
	$page_content .= "\n\$_JCONTENT['{$key}']\t= \"{$encoded_value}\";";
}
// echo $page_content;

fwrite($page_handle, "<?php\n{$page_content}\n\n?>");
fclose($page_handle);

echo "Saved {$content_page_path}{$content_page}.inc";

?>