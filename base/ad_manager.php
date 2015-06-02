<?php

require_once("define.php");
require_once("mysql_connection.php");

class AdManager {

	function __construct () {
	}

	/* Returns URL, ID */
	function get_raw_ad ($type, $number=1, $omit_IDs="") {
		global $mysql_connection;

		$ad_query = "SELECT * FROM ad WHERE banner_type = '$type' AND active = '1'";

		if (is_array($omit_IDs)) {
			foreach ($omit_IDs AS $id)
				$ad_query .= " AND ad_ID != '{$id}'";
		}
		$ad_query .= " ORDER BY RAND() LIMIT {$number}";
		$ad_results = $mysql_connection->sql($ad_query);

		$ads = array();
		while ($ad_results->has_next()) {
			$ad = $ad_results->next();
			$URL = $ad['image'];
			if ($type == VIDEO_AD)
				$URL = $ad['video'];
			$ads[] = array("ID" => $ad['ad_ID'], "URL" => $URL);
		}

		return $ads;
	}

	function get_ad ($banner_type, $number=1, $ad_ID="") {
		global $html, $mysql_connection, $SETTINGS;

		$ad_query = "SELECT * FROM ad 
				WHERE banner_type = '{$banner_type}' AND active = '1'";
		if ($ad_ID != "") {
			if ($ad_ID[0] == "-") {
				$ad_query .= " AND ad_ID != '" . substr($ad_ID, 1) . "' ORDER BY RAND() LIMIT {$number}";
			}
			else $ad_query .= " AND ad_ID = '{$ad_ID}'";
		}
		else $ad_query .= " ORDER BY RAND() LIMIT {$number}";

// echo "$ad_query<BR>";
		$results = $mysql_connection->sql($ad_query);

		$ads = array();
		while ($results->has_next()) {
			$ad = $results->next();

			if ($ad['bannyer_type'] != VIDEO_AD)
				AdManager::view_ad($ad['ad_ID']);

			if ($ad['type'] == FLASH) {
				$ad_div_ID = "ad_{$ad['ad_ID']}";
				$embed_script = $html->script()->type("text/javascript")->content("flashembed('{$ad_div_ID}', { src: 'http://www.therisetothetop.com/" . $ad['image'] . "', width: {$ad['width']}, height: {$ad['height']}, loop: 'false', wmode: 'transparent' } );");
				$ad_content = $html->div()->id($ad_div_ID);
				$html->script->add($embed_script);
			}
			else {
				$ad_image = $ad['image'];
				if ($ad_image[0] != "/")
					$ad_image = "/" . $ad['image'];
				$ad_content = $html->img()->src($ad_image)->alt($ad['title']);
			}

			if ($ad['target_URL'] == "")
				$ads[] = $ad_content;
			else {
				$time = time();
				$ad_link = $html->a()->href("{$SETTINGS['JEKILL_ROOT']}/ad_redirect.php?id={$time}{$ad['ad_ID']}")/*->rel("nofollow")*/->add($ad_content)->target("_blank")->title($ad['title']);
				$ads[] = $ad_link;
			}
		}
		if (count($ads) == 0)
			return $html->div();
		else if (count($ads) == 1)
			return $ads[0];
		else return $ads;
	}

	function view_ad ($ad_ID) {
		global $mysql_connection;

		if (AdManager::is_robot())
			return;

		$update_query = "UPDATE ad SET impressions = impressions+1";
		if (AdManager::is_unique($ad_ID))
			$update_query .= ", uniques = uniques+1";

		$update_query .= " WHERE ad_ID = {$ad_ID}";

		$mysql_connection->query($update_query);

		$mysql_datetime = date("Y-m-d G:i:s");
		$URL = $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING'];
		$referer = $_SERVER['HTTP_REFERER'];
		$ip = $_SERVER['REMOTE_ADDR'];
		$agent = $_SERVER['HTTP_USER_AGENT'];

		$keyword = $_SESSION['keyword'];
		$network = $_SESSION['network'];
		$camp = $_SESSION['camp'];
		$source = $_SESSION['source'];
		$original_keyword = $_SESSION['original_keyword'];
		$partner = $_SESSION['partner'];
		$param1 = $_SESSION['param1'];

		$details_insert = "INSERT INTO ad_impression (ad, time, url, referer, ip, agent, keyword, network, camp, source, original_keyword, partner, param1) 
					VALUES ('{$ad_ID}', '{$mysql_datetime}', '{$URL}', '{$referer}', '{$ip}', '{$agent}', '{$keyword}', '{$network}', '{$camp}', '{$source}', '{$original_keyword}', '{$partner}', '{$param1}')";
		$mysql_connection->query($details_insert);
	}
	
	function is_robot () {
		$USER_AGENT = $_SERVER['HTTP_USER_AGENT'];
		$crawlers = array(
			array('Google', 'Google'),
			array('msnbot', 'MSN'),
			array('Rambler', 'Rambler'),
			array('Yahoo', 'Yahoo'),
			array('AbachoBOT', 'AbachoBOT'),
			array('accoona', 'Accoona'),
			array('AcoiRobot', 'AcoiRobot'),
			array('ASPSeek', 'ASPSeek'),
			array('CrocCrawler', 'CrocCrawler'),
			array('Dumbot', 'Dumbot'),
			array('FAST-WebCrawler', 'FAST-WebCrawler'),
			array('GeonaBot', 'GeonaBot'),
			array('Gigabot', 'Gigabot'),
			array("Java", 'Java Application'),
			array('Lycos', 'Lycos spider'),
			array('MSRBOT', 'MSRBOT'),
			array('Scooter', 'Altavista robot'),
			array('AltaVista', 'Altavista robot'),
			array('IDBot', 'ID-Search Bot'),
			array('eStyle', 'eStyle Bot'),
			array("Spider", 'General Spider'),
			array('Scrubby', 'Scrubby robot')
		);

		foreach ($crawlers as $c)
			if (stristr($USER_AGENT, $c[0])) {
				return true;	// ($c[1]);
		}
		return false;
	}

	function is_unique ($ad_ID) {
		$ad_tracker_ID = "EDR_adtracker";
		$viewed_ads = $_COOKIE[$ad_tracker_ID];
		$unique = true;
		if ($viewed_ads != "") {
			$unique = (strpos($viewed_ads, ":{$ad_ID}:") === false);
		}
		else $viewed_ads = ":";
		
		if ($unique) {
			$viewed_ads .= "{$ad_ID}:";
			setcookie($ad_tracker_ID, $viewed_ads);
		}

		return $unique;
	}

	function redirect ($ad_ID, $increment=true) {
		global $mysql_connection;

		if ($increment && !AdManager::is_robot()) {
			$update_query = "UPDATE ad SET clicks = clicks+1
						WHERE ad_ID = '{$ad_ID}'";
			$mysql_connection->query($update_query);

			$mysql_datetime = date("Y-m-d G:i:s");
			$URL = $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING'];
			$ip = $_SERVER['REMOTE_ADDR'];
			$agent = $_SERVER['HTTP_USER_AGENT'];
			/* Grab the referer from the impression. */
			$referer = $mysql_connection->get_field("ad_impression", "referer", "WHERE ip = '{$ip}' ORDER BY time DESC");

			$keyword = $_SESSION['keyword'];
			$network = $_SESSION['network'];
			$camp = $_SESSION['camp'];
			$source = $_SESSION['source'];
			$original_keyword = $_SESSION['original_keyword'];
			$partner = $_SESSION['partner'];
			$param1 = $_SESSION['param1'];

			$details_insert = "INSERT INTO ad_click (ad, time, url, referer, ip, agent, keyword, network, camp, source, original_keyword, partner, param1)
						 VALUES ('{$ad_ID}', '{$mysql_datetime}', '{$URL}', '{$referer}', '{$ip}', '{$agent}', '{$keyword}', '{$network}', '{$camp}', '{$source}', '{$original_keyword}', '{$partner}', '{$param1}')";
			$mysql_connection->query($details_insert);
		}
		$target = $mysql_connection->get_field("ad", "target_URL", "WHERE ad_ID = '{$ad_ID}'");

		// echo $details_insert; exit;
		header("Location: {$target}");
		exit;
	}

}

?>