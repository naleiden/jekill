<?php

include("header.php");

require_once("php/referral.php");

$html->import_style("/css/v3/landing-page.css");
$html->import_style("/css/v3/home.css");
$html->import_style("//fonts.googleapis.com/css?family=Raleway");

// Has the customer made a purchase yet?
$home_module = "modules/default_home.php";

$home_variables = array("customer" => $customer, "html" => $html);
if ($customer['purchase_ID']) {
    // If the user has an individual subscription, not a group subscription, site license, etc. AND doesn't already have a lifetime subscription.
    if ($customer['purchase_type'] == INDIVIDUAL_SUBSCRIPTION && $customer['duration'] > 0) {
        if (!$customer['ambassador_ID']) {
			$customer_obj = Customer::from_array($customer);
            $ambassador = $customer['ambassador_ID'] = Ambassador::from_customer($customer_obj);
			$ambassador->persist();
        }

		$html->import("/js/referral.js");
		$html->import_style("css/v3/referral.css");
        $home_module = "modules/subscriber_home.php";
        $home_variables['referral'] = Referral::from_ambassador($customer['ambassador_ID']);

        if (isset($_GET['thanks'])) {
            $thanks_dropdown = include_capture("modules/subscription-thanks.php");
            $html->add($thanks_dropdown);
        }
    } else {
        // Group subscription home page - maybe show what others are viewing?
        // Special home page for course director?
    }
}

$home_content = include_capture($home_module, "", $home_variables);

$content->add($home_content);

include("footer.php");

?>