<?php

include_once("../base/calendar.php");
include_once("../base/HTML.php");

$html = new HTML();

$input_ID = $_REQUEST['input_ID'];
$month = $_REQUEST['month'];
$year = $_REQUEST['year'];
$time = $_REQUEST['time'];

if ($month == "")
	$month = date("m");
if ($year == "")
	$year = date("Y");

$epoch_seconds = mktime(0, 0, 0, $month, 15, $year);	

$on_click = "chooseDate";

$calendar = new Month("chooser", $epoch_seconds, $on_click);
$calendar->display_year(true);

$date = new DateTime();
$date->setDate($year, $month, 15);	// 15: Middle of the month to make manipulation safe (28 / 31)
$date->modify("-1 month");
$prev_month = $date->format("m");
$prev_month_text = $date->format("F");
$prev_year = $date->format("Y");

$date->modify("+2 months");
$next_month = $date->format("m");
$next_month_text = $date->format("F");
$next_year = $date->format("Y");

$prev_month_link = $html->a()->href("javascript: loadDateChooser('{$input_ID}', '$prev_month', $prev_year, '{$time}')")->content("&lt; {$prev_month_text}");
$next_month_link = $html->a()->href("javascript: loadDateChooser('{$input_ID}', '$next_month', $next_year, '{$time}')")->content("{$next_month_text} &gt;");
$prev_month_link_div = $html->div()->class("half_column")->add($prev_month_link);
$next_month_link_div = $html->div()->class("right_column")->add($next_month_link);

$month_div = $calendar->get_month_divide(280, 250);

$month_input_div = $html->text()->id("chooser_month")->class("date_input")->value($month);
$day_input_div = $html->text()->id("chooser_day")->class("date_input")->value($day);
$year_input_div = $html->text()->id("chooser_year")->class("date_input_extended")->value($year);

$control_div = $html->div()->add($month_input_div)->content(" / ")->add($day_input_div)->content(" / ")->add($year_input_div);

if ($time) {
	$AM_PM = array(0 => "AM", 12 => "PM");
	$hour_input = $html->text()->id("chooser_hour")->class("date_input")->value("00");
	$minute_input = $html->text()->id("chooser_minute")->class("date_input")->value("00");
	$second_input = $html->text()->id("chooser_second")->class("date_input")->value("00");
	$am_pm = $html->select($AM_PM)->id("chooser_am_pm");
	$control_div->add("&nbsp;")->add($hour_input)->content(":")->add($minute_input)->content(":")->add($second_input)->content(" ")->add($am_pm);
}
$submit = $html->button()->value("Submit")->onClick("selectDate('{$input_ID}')");

$control_div->add($submit);

$header_div = $html->div()->add($prev_month_link_div)->add($next_month_link_div)->add( $html->div()->class("clear") );
$chooser_div = $html->div()->add($header_div)->add($month_div)->add($control_div);

echo $chooser_div->html();

?>