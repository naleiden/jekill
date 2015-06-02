<?php

require_once("HTML.php");
require_once("mysql_connection.php");

define("HOUR_SECONDS", 3600);
define("DAY_SECONDS", 86400);
define("WEEK_SECONDS", 604800);

$IE = (strpos($_SERVER['HTTP_USER_AGENT'], "MSIE") > 0) ? true : false;

function get_day_padding ($width) {
	return round($width * 0.01);
}

function get_day_dimension ($dimension) {
	global $IE;
	static $day_dimension;

	if ($day_dimension != "")
		return $day_dimension;

	$border_size = ($IE) ? 2 : 8;	// ~8 1 pixel lines: IE includes in width
	$usable_space = $dimension - $border_size;
	$day_dimension = floor($usable_space / 7);
	return $day_dimension;
}

function get_week_margin ($dimension) {
	global $IE;
	static $margin;

	if ($margin != "")
		return $margin;

	$border_size = ($IE) ? 2 : 8;
	$usable_space = $dimension - $border_size;
	$day_dimension = floor($usable_space / 7);
	$margin = ($usable_space - ($day_dimension * 7)) / 2;
	return $margin;
}


// V2: In progress
class Calendar {

	public $month, $year, $day_content;
	public $days, $day_keys, $behavior;
	public $week_start_offset;

	public $next_month, $prev_month;	// 01 - 12
	public $next_year, $prev_year;		// The year of the next / prev month

	/* $week_start_offset - 0 for Sunday, 1 for Monday as the left-most day in the calendar.
	   Day headers must be correctly specified externally, and are not affected by $week_start_offset.
	*/
	function __construct ($epoch_seconds=-1, $name="calendar", $week_start_offset=0) {
		$day_content = array();
		$days = array();
		$this->day_class = array();

		$this->week_start_offset = $week_start_offset;

		if ($epoch_seconds < 0)
			$epoch_seconds = time();

		$this->month = date("m", $epoch_seconds);
		$this->month_name = date("F", $epoch_seconds);
		$this->year = date("Y", $epoch_seconds);
		$first_day_epoch = mktime(0, 0, 0, $this->month, 1, $this->year);

		$this->next_month = $this->month+1;
		$this->next_year = $this->year;
		if ($this->next_month >= 13) {
			$this->next_month = 1;
			$this->next_year++;
		}

		$this->prev_month = $this->month-1;
		$this->prev_year = $this->year;
		if ($this->prev_month <= 0) {
			$this->prev_month = 12;
			$this->prev_year--;
		}
		if (strlen($this->prev_month) == 1)
			$this->prev_month = "0{$this->prev_month}";

		if (strlen($this->next_month) == 1)
			$this->next_month = "0{$this->next_month}";

// echo "{$this->prev_month} ({$this->prev_year}) {$this->month} ({$this->year}) {$this->next_month} ({$this->next_year})";

		$first_day_index = date("w", $first_day_epoch) - $this->week_start_offset;
		if ($first_day_index < 0)	// If $week_start_offset is set.
			$first_day_index = 6;

		$this->prev_month_days = date("t", $first_day_epoch - 1296000);	// -1296000: -15 days to put us in the middle of last month
		$this->num_days = date("t", $first_day_epoch);

		for ($i=1; $i<=$first_day_index; $i++) {
			$day_num = $this->prev_month_days - $first_day_index + $i;
			$this->days["-{$day_num}"] = "";
		}

		// The first day of last month that is displayed
		$this->prev_month_day = $this->prev_month_days - $first_day_index +1;
		if ($this->prev_month_day > $this->prev_month_days)
			$this->prev_month_day = "";

		for ($i=1; $i<=$this->num_days; $i++)
			$this->days[$i] = "";

		$total_days = $this->num_days + $first_day_index;

		$remaining_days = 7 - $total_days%7;

		for ($i=1; $i<=$remaining_days; $i++)
			$this->days["+{$i}"] = "";

		$this->next_month_day = $remaining_days;

		$this->behavior = array();
		$this->day_keys = array_keys($this->days);
	}

	function add_by_index ($index, $content) {
		$day = $this->day_keys[$index];
		$this->days[$day] .= $content;
	}

	function add_content ($day, $content) {
		$this->days[$day] .= $content;
	}

	// Returns a date that represents the first day displayed by this calander. (Not necessarily this month)
	function get_first_date () {
		if ($this->prev_month_day)
			return "{$this->prev_year}-{$this->prev_month}-$this->prev_month_day}";
		else return "{$this->year}-{$this->month}-01";
	}

	// Returns a date that represents the last day displayed by this calander. (Not necessarily this month)
	function get_last_date () {
		return "{$this->next_year}-{$this->next_month}-{$this->next_month_day}";
	}

	function get_day_by_index ($index) {
		if (isset($this->day_keys[$index]))
			return $this->day_keys[$index];
		else return false;
	}

	function set_behavior ($day, $event, $behavior) {
		if (!isset($this->behavior[$day]))
			$this->behavior[$day] = array();
		$this->behavior[$day][$event] = $behavior;
	}

	function set_content ($day, $content) {
	    $this->days[$day] = $content;
	}

	function get_first_displayed_day () {
		return $day_keys[0];
	}

	function get_last_displayed_day () {
		return $day_keys[count($day_keys)-1];
	}

	function set_day_class ($day, $class) {
		$this->day_class[$day] .= " {$class}";
	}

	function write_month_header ($epoch_variable) {
		global $MONTH_NAMES;

		$prev_month_name = $MONTH_NAMES[$this->prev_month];
		$next_month_name = $MONTH_NAMES[$this->next_month];
		if ($this->next_year != $this->year)
			$next_month_name .= " {$this->next_year}";

		if ($this->prev_year != $this->year)
			$prev_month_name .= " {$this->prev_year}";

		$prev_epoch = mktime(0, 0, 0, $this->prev_month, 1, $this->prev_year);
		$next_epoch = mktime(0, 0, 0, $this->next_month, 1, $this->next_year);

		$query_args = explode("&", $_SERVER['QUERY_STRING']);
		unset($query_args[$epoch_variable]);
		$query_args = implode("&", $query_args);

		$html = "<div>
	<div class=\"\" style=\"float: left; width: 24.9%\">
		<a href=\"?{$query_args}&amp;{$epoch_variable}={$prev_epoch}\">&laquo; {$prev_month_name}</a>
	</div>
	<div class=\"\" style=\"float: left; text-align: center; width: 50%;\">
		<div class=\"month_header\">" . $MONTH_NAMES[$this->month] . "</div>
		<div class=\"year_header\">{$this->year}</div>
	</div>
	<div class=\"\" style=\"float: left; text-align: right; width: 24.9%\">
		<a href=\"?{$query_args}&amp;{$epoch_variable}={$next_epoch}\">{$next_month_name} &raquo;</a>
	</div>
	<div style=\"clear: both;\"></div>";
		return $html;
	}

	function write_header ($width, $day_headers="l") {
		$html = new HTML();

		$header_table = $html->table(7)->class("calendar_header")->cellpadding("0")->cellspacing("0")->style("width: 100%");
		$day_width = floor($width/7);

		if ($day_headers != "") {
			$day_abbr = array(0 => "Su", 1 => "M", 2 => "Tu", 3 => "W", 4 => "Th", 5 => "F", 6 => "Sa");
			$day_names = array(0 => "Sunday", 1 => "Monday", 2 => "Tuesday", 3 => "Wednesday", 4 => "Thursday", 5 => "Friday", 6 => "Saturday");

			$header_array = $day_names;
			if (is_array($day_headers))
				$header_array = $day_headers;
			else if ($day_headers == "D")
				$header_array = $day_abbr;

			for ($i=0; $i<7; $i++) {
				$day_header = "<div class=\"day_header\" style=\"width: {$day_width}px\">{$header_array[$i]}</div>";
				$header_table->add_datum($day_header);
			}
		}
		return $header_table->html();
	}

	function write_week ($week_num, $width, $height, $day_headers="l") {
		if ($day_headers)
			$week_headers = $this->write_headers($width, $day_headers);
	}

	function write_calendar ($width, $height, $day_headers="l") {
		$html = new HTML();
		$month_table = $html->table(7)->class("calendar_body")->cellpadding("0")->cellspacing("0")->style("height: 100%; width: 100%");

		$calendar_header = $this->write_header($width, $day_headers);

		$i = 0;
		foreach ($this->days AS $day_num => $day_content) {
			$day_class = "day" . $this->day_class[$day_num];

			$day_class .= " " . strtolower($day_headers[$i%7]);

			$month = $this->month;
			$year = $this->year;
			if ($day_num < 0 || $day_num[0] == "+") {
				if ($day_num < 0) {
					$month = $this->prev_month;
					$year = $this->prev_year;
				}
				else {
					$month = $this->next_month;
					$year = $this->next_year;
				}

				if ($day_num < 0)
					$day_num = abs($day_num);
				else $day_num = substr($day_num, 1);
				$day_class = "nonmonth_day";
			}
			$day_formatted = $day_num;
			if ($day_num < 10)
				$day_formatted = "0{$day_num}";
			$day_epoch = mktime(0, 0, 0, $month, $day_num, $year);

			$day = "
	<div id=\"day_{$year}{$month}{$day_formatted}\" class=\"{$day_class}\">
		<div class=\"day_label\">{$day_num}</div>
		<div class=\"day_content\">
			{$day_content}
		</div>
	</div>";

			$day_datum = $html->td()->class($day_class)->add($day)->add("<input type=\"hidden\" id=\"\" class=\"day_epoch\" value=\"{$day_epoch}\" />");
			if (isset($this->behavior[$day_num])) {
				foreach ($this->behavior[$day_num] AS $event => $behavior) {
					$day_datum->$event($behavior);
					$day_datum->content("");
				}
			}
			else if (isset($this->behavior[""])) {
				foreach ($this->behavior[""] AS $event => $behavior) {
					$day_datum->$event($behavior);
				}
			}

			// $this->day_containers[$day_num] = $day_datum;
			$month_table->add($day_datum);
			$i++;
		}
		$calendar_div = "<div style=\"width: {$width}px;\">{$calendar_header}<div class=\"calendar\" style=\"height: {$height}px; width: {$width}px\">" . $month_table->html() . "</div></div>";
		

		return $calendar_div;
	}

}

class Month {

	public $epoch_seconds, $num_days, $month_num, $show_year, $show_day_header;
	public $weeks, $day, $name, $month_name, $year, $onClick, $day_header_format;

	function __construct ($name, $epoch_seconds=-1, $onClick="") {
		if ($epoch_seconds == -1)
			$epoch_seconds = time();

		$this->name = $name;
		$weeks = array();
		$this->num_days = date("t", $epoch_seconds);
		$this->month_name = date("F", $epoch_seconds);
		$this->month_num = date("n", $epoch_seconds);
		$this->day = date("j", $epoch_seconds);
		$this->epoch_seconds = $epoch_seconds;
		$this->year = date("o", $epoch_seconds);
		$this->onClick = $onClick;
		$this->show_year = false;
		$this->show_day_header = true;
		$this->init_month();
	}

	function init_month ($weeks="12345", $current=true) {
		$epoch_seconds = mktime(0, 0, 0, $this->month_num, 1, $this->year);
		$day_in_week = date("w", $epoch_seconds);

		$day = 1;
		for ($i=1; $day <= $this->num_days; $i++) {
			$this->weeks[] = new Week($this->name, $i, $epoch_seconds, $this->onClick);
			$next_week_step = 7;
			if ($i == 1) {
				$next_week_step = 7-($day_in_week);
			}
			$day += $next_week_step;
			$next_week_epoch_seconds = $epoch_seconds + DAY_SECONDS * $next_week_step;

			/* Account for daylight savings time. */
			if (!date("I", $next_week_epoch_seconds) && date("I", $epoch_seconds)) {
				$next_week_epoch_seconds += HOUR_SECONDS;
			}
			else if (date("I", $next_week_epoch_seconds) && !date("I", $epoch_seconds)) {
				$next_week_epoch_seconds -= HOUR_SECONDS;
			}
			$epoch_seconds = $next_week_epoch_seconds;
		}
	}

	function display_year ($display) {
		$this->show_year = $display;
		$this->year = date("o", $this->epoch_seconds);
	}

	function display_day_header ($display) {
		$this->show_day_header = $display;
	}

	function get_day ($day_num) {
		foreach ($this->weeks as $week) {
			$day = $week->get_day($day_num);
			if ($day != "")
				return $day;
		}
		return "";
	}

	function get_today () {
		$day_num = date("j", time());
		return $this->get_day($day_num);
	}

	function get_month_events () {
		global $mysql_connection;

		$events = array();
		for ($i=1; $i<=$this->num_days; $i++)
			$events[$i] = array();
		$query = "SELECT event_ID, day_from, title FROM events WHERE day_to LIKE '$this->year-$this->month_num%'";

		$results = $mysql_connection->sql($query);
		while ($results->has_next()) {
			$event = new Event();
			$event_data = $results->next();
			$event->mset($event_data);
			$start = Date::from_mysql_datetime($event->day_from);
			$day_num = $start->day +0;
			$events[$day_num][] = $event;
		}
		return $events;
	}

	function get_month_divide ($width, $height, $weeks="12345", $current=true) {
		global $html;

		$month_prefix = $this->name . "_month_$this->month_num";
		$month_class = ($current) ? "current_month" : "noncurrent_month";
		$month_container = $html->div()->style("width: {$width}; height: {$height}")->class("month_container {$month_class}")->id($month_prefix);
		$month = $html->div()->class("month");
		$day = $this->day;
		$month_name = $html->div()->class("{$month_class}_name")->id("{$month_prefix}_name")->content(date("F", $this->epoch_seconds));
		if ($this->show_year)
			$month_name->add_content("$this->year");

		$month_container->add($month_name);
		if ($this->show_day_header) {
			$day_width = get_day_dimension($width);
			$day_height = round($height/(count($this->weeks) +1));	// +1: Leave space for header / footer
			$day_padding = get_day_padding($width);
			$day_header = $html->div()->style("width: {$width}px")->class("day_header_row");
			// $day_header->clear_left();
			if ($this->day_header_format == "l")
				$days = array("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");
			// $days = array("Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat");
			else $days = array("S", "M", "T", "W", "T", "F", "S");

			foreach ($days as $day) {
				$day_div = $html->div()->style("width: {$day_width}px; $padding: {$day_padding}px;")->class("day_column_header")->content($day);
				$day_header->add($day_div);
			}
			$month_container->add($day_header);
		}
		if ($current)
			$events = $this->get_month_events();

		foreach ($this->weeks as $week) {
			$week_div = $week->get_week_divide($width, $day_height, $events);
			$month->add($week_div);
		}
		$month_footer = $html->div()->class("month_footer");
		$month_container->add($month)->add($month_footer);

		return $month_container;
	}

	function write_month ($width, $height) {
		$month_div = $this->get_month_divide($width, $height);
		return $month_div->html();
	}

}

class Week {

	public $name, $days, $epoch_seconds, $week_num;
	public $today, $onClick;
	
	function __construct ($name, $week_num, $epoch_seconds, $onClick) {
		$this->name = $name;
		$this->epoch_seconds = $epoch_seconds;
		$this->week_num = $week_num;
		$this->onClick = $onClick;
		$this->days = array();
		$this->init_week();
	}

	function init_week () {
		$max_day = date("t", $this->epoch_seconds);
		$day_in_week = date("w", $this->epoch_seconds);
		// $epoch_seconds = $this->epoch_seconds - ($day_in_week * DAY_SECONDS);
		$day_num = date("j", $this->epoch_seconds);
		$start_day_in_week = 0;

		if ($day_num < 7) { // Leading non-month days
			for ($i=0; $i<$day_in_week; $i++) {
				$day = new Day($this->name, $this->epoch_seconds, 0, ""); // , $this->onClick);
				$this->days[] = $day;
			}
			$start_day_in_week = $day_in_week;
		}

		$epoch_seconds = $this->epoch_seconds;
		for ($i=$start_day_in_week; $i<7; $i++) {
			if ($day_num > $max_day)
				$day = new Day($this->name, $this->epoch_seconds, 0, ""); // , $this->onClick);
			else $day = new Day($this->name, $epoch_seconds, $day_num, $this->onClick);

	// $day->add_content(date("d-m-y h:i:s a", $epoch_seconds));
			$epoch_seconds += DAY_SECONDS;
/*
			if (!date("I", $epoch_seconds))
	$epoch_seconds += HOUR_SECONDS;
*/
			$this->days[] = $day;
			$day_num++;
		}
	}

	function get_day ($day_num) {
		$num_days = count($this->days);
		$i = 0;
		while ($i < count($this->days) && $this->days[$i]->day_num == 0)
			$i++;
		$num_days -= $i;

		$first_day = $this->days[$i];
		$first_day_num = $first_day->day_num;
		$last_day = $first_day_num + $num_days-1;

		if ($day_num >= $first_day_num && $day_num <= $last_day) {
			$day_index = $day_num - $first_day_num + $i;
			return $this->days[$day_index];
		}
		else return "";
	}

	function get_week_divide ($width, $height, $events="") {
		global $html;

		$day_num = date("j", $this->epoch_seconds);
		$week_margin = get_week_margin($width);
		$week = $html->div()->style("margin-left: {$week_margin}px;")->class("week")->id($this->name);
		// $week->style("width: {$width}; height: {$height}");	// Let days control this.

		$num_days = count($this->days);
		for ($i=0; $i<$num_days; $i++) {
			$day = $this->days[$i];
			$day_div = $day->get_day_divide($width, $height, $events);

			if ($this->week_num == 1)
				$day_div->class .= " day_top_row";

			if ($i == 0) {
				$day_div->class .= " day_first_column";
			}

			// $day_div->set_border_right(1);
			// $day_div->set_border_bottom(1);
			$week->add($day_div);
		}
		return $week;
	}

}

class Day {

	public $name, $epoch_seconds, $month_num, $day_num, $events, $onClick;
	public $header_content, $content;

	function __construct ($name, $epoch_seconds, $day_num="", $onClick="") {
		global $html;

		$this->name = $name;
		$this->epoch_seconds = $epoch_seconds;
		$this->day_num = $day_num;
		$this->events = array();
		$this->month_num = date("n", $epoch_seconds);
		$this->year = date("o", $epoch_seconds);
		$this->onClick = $onClick;
		if ($day_num > 0)
			$this->header_content = $day_num;
		$this->content = $content;

		$day_class = "day";

		if ($this->day_num == 0)
			$day_class = "nonmonth_day";
		$this->day_divide = $html->div()->class($day_class)->id("{$this->name}_{$this->day_num}");
	}

	function add_event ($event) {
		$this->events[] = $event;
	}

	function get_day_planner_divide ($min_time=6, $max_time=8) {
		global $html, $mysql_connection;

		$day_header = $html->div()->class("planner_header")->content("<FONT style=\"font-size: 1.3em;\"><B>$this->month_num / $this->day_num / $this->year</B></FONT><BR>" . date("l", $this->epoch_seconds));
		$planner_div = $html->div()->class("planner")->id("{$this->name}_planner");
		$planner_div->add($day_header);
		$hours = array();
		for ($i=$min_time; $i<=12; $i++) {
			$hours[$i] = $html->div()->class("planner_hour")->content($i)->id("{$this->name}_planner_{$i}");
		}
		for ($i=1; $i<=$max_time; $i++) {
			$hour = 12+$i;
			$hours[$hour] = $html->div()->class("planner_hour")->content($i)->id("{$this->name}_planner_{$hour}");
		}
		$mysql_date = "$this->year-$this->month_num-$this->day_num";
		$army_max_time = 12 + $max_time;
		$query = "SELECT * FROM events WHERE (day_from > '$mysql_date $min_time:00:00' AND day_from < '$mysql_date $army_max_time:00:00') OR (day_to > '$mysql_date $min_time:00:00' AND day_to < '$mysql_date $army_max_time:00:00')";

		$results = $mysql_connection->sql($query);

		while ($results->has_next()) {
			$result = $results->next();
			$start_time = Date::from_mysql_datetime($result[day_from]);
			$end_time = Date::from_mysql_datetime($result[day_to]);
			$end_hour = $end_time->hour + 0;	// +0: Treat 'hour' as a number, remove leading 0's
			$start_hour = $start_time->hour + 0;

			if ($start_time->AM_PM)
				$start_hour += 12;

			/* This event began on another day. */
			if ($start_time->day != $this->day_num)
				$start_hour = $min_hour;

			/* This event ends on another day. */
			if ($end_time->day > $start_time->day)
				$end_hour = $max_hour;

			$event_div = $html->div()->class("planner_event")->content("<U>{$result['title']}</U><FONT style=\"font-size: 0.8em;\"><I>through " . $end_time->write_date_and_time() . "</I></FONT>")->id("planner_event_{$result['event_ID']}")->onClick("viewEvent({$result['event_ID']})");
			$hours[$start_hour]->add($event_div);
/*
			if ($start_hour <= 12) {
				for ($i=$start_hour; $i<=12; $i++) {
					$hours[$i]->add($event_div);
				}
			}
			if ($start_hour > 12) {
				$start_hour = 13;
				if ($start_time->hour > 13)
					$start_hour = $start_time->hour;
				for ($i=$start_hour; $i<$max_time; $i++) {
					$hours[$i]->add($event_div);
				}
			}
*/
		}
		$num_hours = (12 - $min_time) + ($max_time);
		$height /= $num_hours;
		$i=0;
		foreach ($hours as $hour => $hour_div) {
			$hour_epoch_seconds = mktime($hour, 0, 0, $this->month_num, $this->day_num, $this->year);
			$hour_div->dblClick = "addEvent($hour_epoch_seconds)";
			$hour_div->set_border(1);
			if ($i++ != count($hours)-1)
				$hour_div->set_border_bottom(0);

			$hour_div->set_padding(5);
			$planner_div->add($hour_div);
		}
		return $planner_div;
	}

	function add_content ($content) {
		$this->content .= $content;
	}

	function get_divide () {
		return $this->day_divide;
	}

	function get_day_divide ($width, $height, $events="") {
		global $html;

		$padding = get_day_padding($width);
		$day_width = get_day_dimension($width);
		$day_prefix = $this->name . "_" . $this->day_num;
		$day_num = $this->day_num;
		if ($this->day_num == 0) {
			$day_num = "";
		}
		$day = $this->day_divide;
		$day->style("width: {$day_width}px; height: {$height}px;");
		
		$events_div = $html->div();
		if ($this->day_num && $events != "") {
			$day_events = $events[$this->day_num];
			$num_events = count($day_events);
			foreach ($day_events as $event) {
				$event_div = $html->div()->content($event->title);
				$event_div->dblClick = "viewEvent($event->event_ID)";
				$events_div->add($event_div);
			}
			if ($num_events > 3) {
				$day->mouseOver = "expandDay('$day_prefix')";
				$day->mouseOut = "contractDay('$day_prefix')";
			}
		}

		$day_epoch = mktime(12, 0, 0, $this->month_num, $this->day_num, $this->year);

		if ($this->onClick != "" && $this->day_num != 0)
			$day->onClick($this->onClick . "('$this->name', $day_num, '$day_epoch')");

		$day_header = $html->div()->style("padding: {$padding}")->class("day_header")->id("{$day_prefix}_header")->add($this->header_content);
		$content_div = $html->div()->content($this->content);
		$day_contents = $html->div()->class("day_contents")->id("{$day_prefix}_contents");
		foreach ($this->events as $event) {
			$event_div = $html->div()->class("day_event")->id("{$day_prefix}_event_$i")->content($event);
		}
		$day->add($day_header);
		$day->add($content_div);
		$day->add($events_div);
		$day->add($day_contents);
		return $day;
	}

}

/*
include_once("settable.php");

class Event extends Persistable {

	function __construct () {
		parent::__construct("events");
	}

	function get_database_schema () {
		$schema = array();
		$schema[event_ID] = "SERIAL";
		$schema[posted_by] = "BIGINT";
		$schema[title] = "CHAR(64)";
		$schema[day_from] = "DATETIME";
		$schema[day_to] = "DATETIME";
		$schema[notes] = "TINYTEXT";
		return $schema;
	}

	function get_event_divide () {
		global $html;

		$event_div = $html->div();

		$from = Date::from_mysql_datetime($this->day_from);
		$to = Date::from_mysql_datetime($this->day_to);

		$to_range = $to->write_date_and_time();
		if ($to->write_date() == $from->write_date())
			$to_range = $to->write_time();

		$range_div = $html->div()->content($from->write_date_and_time() . " - " . $to_range);
		$title_div = $html->div()->content("<B>$this->title</B>");
		$notes_div = $html->div()->content("<B>Notes</B>:<BR>$this->notes");

		$title_div->style("border-bottom: solid #000000 1px; border-top: solid #000000 1px;");
		$notes_div->style("margin-top: 10px;");

		$event_div->add($title_div);
		$event_div->add($range_div);
		$event_div->add($notes_div);

		return $event_div;
	}

	function get_event_form () {
		global $EVENT_VISIBILITY, $AM_PM;
		
		$event_form = new Form("event_form", 3);

		$from = Date::now();
		$to = Date::from_unix_time(time() + 3600);

		if ($this->from != "0000-00-00 00:00:00")
			$from = Date::from_mysql_datetime($this->day_from);

		$start_date = $from->write_date();
		$start_time = $from->hour . ":" . $from->minute;
		$start_am_pm = $from->AM_PM;

		if ($this->to != "0000-00-00 00:00:00")
			$to = Date::from_mysql_datetime($this->day_to);

		$end_date = $to->write_date();
		$end_time = $to->hour . ":" . $to->minute;
		$end_am_pm = $to->AM_PM;

		$event_ID = new HiddenInput("event_ID", $this->event_ID);
		$title = new Text("title", 60, $this->title);
		$start_date = new Text("start_date", 10, $start_date);
		$start_time = new Text("start_time", 10, $start_time);
		$start_am_pm = new Select("start_am_pm", $start_am_pm, NOT_REQUIRED, $AM_PM);
		$end_date = new Text("end_date", 10, $end_date);
		$end_time = new Text("end_time", 10, $end_time);
		$end_am_pm = new Select("end_am_pm", $end_am_pm, NOT_REQUIRED, $AM_PM);
		$notes = new TextArea("notes", 30, 4, $this->notes);

		$save = new Submit("Save", "saveEvent($this->event_ID)");
		$cancel = new Submit("Cancel", "closeInnerWindow('event')");
		$delete = new Submit("Delete", "deleteEvent($this->event_ID)");

		$visibility = new Select("visibility", $this->visibility, NOT_REQUIRED, $EVENT_VISIBILITY);

		$event_form->add_input("", $event_ID);
		$event_form->add_input("Event Title:", $title, 3);
		$event_form->add_input("Start Time:", $start_date);
		$event_form->add_input("", $start_time);
		$event_form->add_input("", $start_am_pm);
		$event_form->add_input("End Time:", $end_date);
		$event_form->add_input("", $end_time);
		$event_form->add_input("", $end_am_pm);
		$event_form->add_input("Notes:", $notes, 3);
		$event_form->add_input("", $save);
		$event_form->add_input("", $cancel);
		$event_form->add_input("", $delete);

		$event_div = new Divide("", $event_form->write_form());
		return $event_div;
	}

	function query ($event_ID, $fields="*") {
		global $mysql_connection;

		$event = new Event();
		$event_details = $mysql_connection->get("events", "WHERE event_ID = '$event_ID'");
		$event->mset($event_details);
		return $event;
	}

	function where () {
		return " event_ID = '$this->event_ID'";
	}

}
*/

?>