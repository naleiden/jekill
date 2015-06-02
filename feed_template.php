<?php

require_once("base/mysql_connection.php");
require_once("base/util.php");

function get_excerpt ($html) {
	$copy = strip_tags_and_nbsp ($html);
	$copy = html_excerpt($copy, 200);
	return $copy;
}

function strip_tags_and_nbsp ($html) {
	$copy = strip_tags($html);
	return strip_special($copy);
}

function strip_special ($html) {
	$copy = str_replace("&nbsp;", "", $copy);
	$copy = str_replace("&rsquo;", "'", $copy);
	$copy = str_replace("&rdquo;", "\"", $copy);
	$copy = str_replace("&lsquo;", "'", $copy);
	$copy = str_replace("&ldquo;", "\"", $copy);
	$copy = str_replace("&ndash;", "-", $copy);
	$copy = str_replace("&mdash;", "-", $copy);
	$copy = str_replace("&", "&amp;", $html);
	return $copy;
}

function get_mime_type ($filename) {
	$extension = strrchr($filename, ".");
	$extension = strtolower($extension);
	switch ($extension) {
		case ".flv":	$mime = "video/flv";	break;
		case ".mp3":	$mime = "audio/mpeg";	break;
		case ".mpg":
		case ".mpeg":	$mime = "video/mpeg";	break;
		case ".mov":	$mime = "video/quicktime"; break;
		case ".swf":	$mime = "application/x-shockwave-flash"; break;
		case ".wav":	$mime = "audio/x-wav";	break;
	}
	return $mime;
}

/*********************/
/*  REQUIRED FIELDS  */
/*********************/
/*
 * $ITEM_UNIQUE_ID:	The unique identifier for each resulting record 
 * $FEED_CONTENT_URL:	URL where this content can be located (non-feed), containing the 'ITEM_UNIQUE_ID' where the record's ID should be
 * $FEED_DESCRIPTION:
 * $FEED_TITLE:
 */

/*********************/
/*  OPTIONAL FIELDS  */
/*********************/
/*
 * $FEED_SUBTITLE:	
 * $FEED_URL:		The URL to this feed
 * $FEED_LANGUAGE:	Defaults to 'en-us'
 * $FEED_TIMEZONE:	Defaults to '-0400'
 * $BASE_URL:		The base URL of all files
 * $FEED_REQUIRE_MEDIA	Whether to skip records that do not have audio or video
 * 
 * $FEED_ITUNES:	Is this an iTunes feed? 0 or 1
 * $ITUNES_AUTHOR:	The author of the iTunes Feed ($ITUNES_OWNER is used if this is not set)
 * $ITUNES_CATEGORY	The category of the iTunes feed, defaults to 'Business'
 * $ITUNES_IMAGE:	The image for the feed used by iTunes
 * $ITUNES_OWNER:	The owner of the iTunes Feed
 */

if (!isset($BASE_URL))
	$BASE_URL = $_SERVER['REMOTE_HOST'];
if (!isset($FEED_URL))
	$FEED_URL = $_SERVER['REMOTE_HOST'];
if (!isset($FEED_TIMEZONE))
	$FEED_TIMEZONE = "-0400";
if (!isset($FEED_LANGUAGE))
	$FEED_TIMEZONE = "en-us";

if (!isset($ITUNES_CATEGORY))
	$ITUNES_CATEGORY = "Business";

/******************************/
/*  DATABASE TABLE FIELD MAP  */
/******************************/
/*
 * Override these fields to specify the correct fields in your database table.
 * 
 * $TITLE_FIELD:		The field that specifies the title
 * $DESCRIPTION_FIELD:	The field that specifies the description
 * $DURATION_FIELD:	The duration of the media, if there is any
 * $AUDIO_FIELD:
 * $VIDEO_FIELD:
 */

if (!isset($TITLE_FIELD))
	$TITLE_FIELD = "title";
if (!isset($DATE_FIELD))
	$DATE_FIELD = "date";
if (!isset($DESCRIPTION_FIELD))
	$DESCRIPTION_FIELD = "description";
if (!isset($DURATION_FIELD))
	$DURATION_FIELD = "duration";
if (!isset($KEYWORDS_FIELD))
	$KEYWORDS_FIELD = "keywords";
if (!isset($AUDIO_FIELD))
	$AUDIO_FIELD = "audio";
if (!isset($VIDEO_FIELD))
	$VIDEO_FIELD = "video";

if (!isset($FEED_CONTENT_URL) || !isset($FEED_DESCRIPTION) || !isset($FEED_TITLE) || !isset($FEED_QUERY) || !isset($ITEM_UNIQUE_ID))
	die("You must specify a \$FEED_CONTENT_URL, a \$FEED_DESCRIPTION, a \$FEED_QUERY, a \$FEED_TITLE, and a \$ITEM_UNIQUE_ID.");

if (!isset($ITUNES_EXPLICIT))
	$ITUNES_EXPLICIT = "No";

$xml = "<?xml version='1.0' encoding='UTF-8'?>
<rss xmlns:content='http://purl.org/rss/1.0/modules/content/' xmlns:itunes='http://www.itunes.com/dtds/podcast-1.0.dtd' xmlns:atom='http://www.w3.org/2005/Atom' version='2.0'>
	<channel>
		<atom:link href='{$FEED_URL}' rel='self' type='application/rss+xml' />
		<title>{$FEED_TITLE}</title>
		<description>{$FEED_DESCRIPTION}</description>
		<link>{$FEED_CONTENT_URL}</link>
		<language>{$FEED_LANGUAGE}</language>";

		

if ($FEED_ITUNES) {
	$xml .= "
		<itunes:author>{$ITUNES_AUTHOR}</itunes:author>
		<itunes:subtitle>{$FEED_SUBTITLE}</itunes:subtitle>
		<itunes:summary>{$FEED_DESCRIPTION}</itunes:summary>
		<itunes:owner>
			<itunes:name>{$ITUNES_OWNER}</itunes:name>
			<itunes:email>{$ITUNES_OWNER_EMAIL}</itunes:email>
		</itunes:owner>
		<itunes:explicit>{$ITUNES_EXPLICIT}</itunes:explicit>
		<itunes:image href='{$ITUNES_IMAGE}'/>
		<itunes:category text='{$ITUNES_CATEGORY}'/>";
}

$results = $mysql_connection->sql($FEED_QUERY);


$VIDEO_FIELD = "video";
$VIDEO_TYPE = "flv";
if ($_GET['itunes'] != "") {
	$VIDEO_FIELD = "feed_video";
	$VIDEO_TYPE = "mov";
}

while ($results->has_next()) {
	$row = $results->next();

	$duration = ($row[$DURATION_FIELD] == "") ? "00:01:00" : $row['duration'];
	$item_URL = str_replace("ITEM_UNIQUE_ID", $row[$ITEM_UNIQUE_ID], $FEED_CONTENT_URL);
	$item_URL = str_replace("RECORD_TYPE", $row[$ITEM_TYPE], $item_URL);
	$item_URL = str_replace("ITEM_TITLE_KEYWORDS", url_namify($row[$TITLE_FIELD]), $item_URL);
	$audio_URL = $row[$AUDIO_FIELD];
	$video_URL = $row[$VIDEO_FIELD];
	$audio_type = get_mime_type($audio_URL);
	$video_type = get_mime_type($video_URL);
	$encoded_description = strip_special($row[$DESCRIPTION_FIELD]);

	if ($FEED_REQUIRE_MEDIA && $video_URL == "" && $audio_URL == "")
		continue;

	$GUID = "";
	if ($video_URL != "")
		$GUID = "{$BASE_URL}/{$video_URL}";
	else if ($audio_URL != "")
		$GUID = "{$BASE_URL}/{$audio_URL}";

	$xml .= "	<item>
		<title>{$row[$TITLE_FIELD]}</title>
		<link>{$item_URL}</link>
		<guid>{$GUID}</guid>
		<description>" . strip_tags_and_nbsp($row[$DESCRIPTION_FIELD]) . "</description>
		<content:encoded><![CDATA[{$encoded_description}]]></content:encoded>";
	if (is_file($video_URL)) {
		$xml .= "
		<enclosure url='{$BASE_URL}/{$video_URL}' length='" .  filesize($video_URL) . "' type='{$audio_type}'/>";
	}
	if (is_file($video_URL)) {
		$xml .= "
		<enclosure url='{$BASE_URL}/{$audio_URL}' length='" .  filesize($audio_URL) . "' type='{$audio_type}'/>";
	}
	$xml .= "
		<category>Podcasts</category>
		<pubDate>" . date("D, d M Y H:i:s", strtotime($row[$DATE_FIELD])) . " {$FEED_TIMEZONE}</pubDate>";

if ($FEED_ITUNES) {
	$xml .= "
		<itunes:explicit>{$ITUNES_EXPLICIT}</itunes:explicit>
		<itunes:subtitle>{$row[$TITLE_FIELD]}</itunes:subtitle>
		<itunes:summary>" . strip_tags_and_nbsp($row[$DESCRIPTION_FIELD]) . "</itunes:summary>
		<itunes:duration>{$duration}</itunes:duration>
		<itunes:keywords>" . implode(", ", explode(" ", $row[$KEYWORDS_FIELD])) . "</itunes:keywords>";
}

	$xml .= "
		</item>";
}

$xml .= "	</channel>
</rss>";

header("Content-Type: text/xml");
echo $xml;

?>