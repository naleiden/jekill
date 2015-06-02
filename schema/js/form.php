<?php

require_once("../../base/settings.php");

header("Content-type: text/javascript");

include("jquery-ui/js/jquery-ui.min.js");

?>

var JEKILL_ROOT = "<?php echo $SETTINGS['JEKILL_ROOT']; ?>";

function toggleRichEditor(id) {
	if (!tinyMCE.get(id))
		tinyMCE.execCommand('mceAddControl', false, id);
	else
		tinyMCE.execCommand('mceRemoveControl', false, id);
}

/*******************/
/*  Control Panel  */
/*******************/

function ensureUnique (tableName, fieldName, entityID, suffix, originalValue, messageCallback) {
	var value = $("#" + fieldName + suffix).val();
	if (trim(value) == "")
		return;

/*
	var arguments = "table_name=" + tableName + "&field_name=" + fieldName + "&entity_ID=" + entityID + "&value=" + value;
	$.ajax({
		url: "/schema/ensure_unique_table_value.php",
		dataType: "json",
		data: arguments,
		error: function () { alert("!"); },
		response: function (response) {
			if (!response.unique) {
				alert(response.message);
				$("#" + fieldName + suffix).val(originalValue);
			}
		}
	});
*/

	$.getJSON(JEKILL_ROOT + "/schema/ensure_unique_table_value.php", { table_name: tableName, field_name: fieldName, entity_ID: entityID, value: value }, function (response, textStatus) {
		if (!response.unique) {
			if (typeof(messageCallback) == "function")
				messageCallback(response.message);
			else if (typeof(messageCallback) == "string" && window[messageCallback])
				window[messageCallback](value);
			else alert(response.message);
			$("#" + fieldName + suffix).val(originalValue);
		}
	});

}

function valueChanged (inputID) {
	$("#" + inputID + "_changed").val(1);
}

function saveEntity (formID) {
	$("#formID").submit();
}

/*******************/
/*  PASSWORD HASH  */
/*******************/

function hashPassword (fieldName, suffix, hashFunction) {
	var plaintext = $("#" + fieldName + "_plaintext" + suffix).val();
	var hashed = hashFunction.call(this, plaintext);
// alert(hashed);
	$("#" + fieldName + suffix).val(hashed);
	$("#" + fieldName + suffix + "_changed").val(1);
}

/******************/
/*  FIELD GROUPS  */
/******************/
function activateFieldGroup (fieldGroupID, suffix) {
	$(".field_group" + suffix).addClass("hidden");
	$(".submenu .menu_tab").removeClass("menu_tab_selected");
	$("#" + fieldGroupID).removeClass("hidden");
	$("#" + fieldGroupID + "_tab").addClass("menu_tab_selected");
}

function getNumFieldGroups (tableName) {
	return $("#" + tableName + "_form .field_group").size();
}

function getCurrentFieldGroupIndex (tableName) {
	var index = $("#" + tableName + "_form .field_groups .field_group:not(.hidden)").index();
	return index;
}

function previousFieldGroup (tableName) {
	changeFieldGroup(tableName, -1);
}

function nextFieldGroup (tableName) {
	changeFieldGroup(tableName, 1);
}

function changeFieldGroup (tableName, direction) {
	var currentIndex = getCurrentFieldGroupIndex(tableName);
	var destIndex = currentIndex + direction;

	setFieldGroup(tableName, destIndex);
}

function setFieldGroup (tableName, destIndex) {
	var numGroups = getNumFieldGroups(tableName);
	var formType = $("#" + tableName + "_form_type").val();
	var alterNavigation = (formType == "sequential");

	if (destIndex < 0 || destIndex >= numGroups)
		return;

	if (alterNavigation) {
		if (destIndex == 0)
			$("#_previous_container").addClass("hidden");
		else $("#_previous_container").removeClass("hidden");

		if (destIndex == numGroups-1) {
			$("#_next_container").addClass("hidden");
			$("#_save").removeClass("hidden");
		}
		else {
			$("#_next_container").removeClass("hidden");
			$("#_save").addClass("hidden");
		}
	}

	$("#" + tableName + "_form .field_group").addClass("hidden");
	$(".submenu .menu_tab").removeClass("menu_tab_selected");
	$("#" + tableName + "_form .field_group:eq(" + destIndex + ")").removeClass("hidden");
	$(".submenu .menu_tab:eq(" + destIndex + ")").addClass("menu_tab_selected");
}

/******************/
/*  Image Select  */
/******************/

function loadImageDropdown (fieldName, suffix) {
	$("#" + fieldName + suffix + "_select").slideDown("slow", function () {
		$(document).click(function () { hideImageDropdown(fieldName, suffix); });
	});
}

function hideImageDropdown (fieldName, suffix) {
	$(document).unbind("click");
	$("#" + fieldName + suffix + "_select").slideUp("fast");
}

function selectDropdownImage (fieldName, suffix, URL, value) {
	$("#" + fieldName + suffix).val(value);
	$("#" + fieldName + suffix + "_changed").val(1);
	$("#" + fieldName + suffix + "_preview").attr("src", JEKILL_ROOT + "/image_excerpt.php?w=100&h=100&z=FIT&c=FFFFFF&url=" + URL);
	$("#" + fieldName + suffix + "_select").slideUp("fast");
}

/*******************/
/*  Color Chooser  */
/*******************/

function loadColorChooser (id, directory, callback) {
	// directory: hack to be able to call loadColorChooser from inside of schema directory
	if (typeof(directory) == "undefined")
		directory = "schema/";
	var params = "act_on=" + id;
	if (typeof(callback) != "undefined")
		params += "&action=" + callback;
	else params += "&action=selectColor";

	if (typeof(directory) != "undefined")
		params += "&directory=" + directory
	openInnerWindow("color_chooser", directory + "color_selector.php", 360, 300, "Select Color", params);
}

function selectColor (id, color) {
	$("#" + id).val(color);
	$("#" + id + "_swatch").css("background-color", "#" + color);
	$("#" + id + "_changed").val(1);
	closeInnerWindow("color_chooser");
}

/********************/
/*  Display Errors  */
/********************/

function registerErrors (errorDivID, errors) {
	var errorMessage = "The following errors occured when trying to submit:<ul>";
	for (var i in errors) {
		errorMessage += "<li>" + errors[i] + "</li>";
	}
	errorMessage += "</ul>";
	$("#" + errorDivID).html(errorMessage).fadeIn("slow");
}

/******************/
/*  Link Suggest  */
/******************/

function selectSuggestion (tablename, fieldname, suffix) {
	// $(".link_suggestion_results").removeClass("");
}

function loadLinkSuggestions (tableName, fieldName, suffix) {
	var keywords = $("#" + fieldName + "_suggest" + suffix).val();
	if (keywords == "")
		return;

	// $("#" + fieldName + suffix + "_selected").html("");
	$.post(JEKILL_ROOT + "/schema/get_link_suggestions.php", { table_name: tableName, field_name: fieldName, suffix: suffix, keywords: keywords }, function (responseXML) {
		var resultsContainer = $("#" + fieldName + suffix + "_results");
		resultsContainer.empty().show();
		// resultsContainer.append("<div class=\"link_suggestion_results\"><div>");

		var results = $(responseXML).find("Response Result");
		var numResults = results.size();
		if (numResults) {
			results.each(function (index, element) {
	var label = $(this).children("Label").text();
	var value = $(this).children("Value").text();
	var linkClass = "";
	if (index == 0) {
		cueSelectLinkSuggestion(null, tableName, fieldName, suffix, label, value);
		linkClass = "link_suggestion_selected";
	}

	resultsContainer.append("<div class=\"link_suggestion " + linkClass + "\" onmouseover=\"cueSelectLinkSuggestion(this, '" + tableName + "', '" + fieldName + "', '" + suffix + "', '" + label + "', '" + value + "')\"><a href=\"javascript: selectLinkSuggestion('" + tableName + "', '" + fieldName + "', '" + suffix + "', '" + label + "', '" + value + "')\">" + label + "</a></div>");
});
		}
		else {
			resultsContainer.append("<div class=\"link_suggestion\">No Results Were Found</div>");
			cueSelectLinkSuggestion(null, tableName, fieldName, suffix, "", "");
		}

		$(document).click(function () {
			// var selected = $("#");
			$("#" + fieldName + suffix + "_results").fadeOut("fast");
			$(document).unbind("click", this);
		});
	});
}

function cueSelectLinkSuggestion (target, tableName, fieldName, suffix, label, value) {
	$(".link_suggestion_selected").removeClass("link_suggestion_selected");
	if (target) {
		$(target).addClass("link_suggestion_selected");
	}


	$(document).unbind("click");
	$(document).click(function () {
		selectLinkSuggestion(tableName, fieldName, suffix, label, value);
		$(document).unbind("click", this);
	});
}

function selectLinkSuggestion (tableName, fieldName, suffix, label, value) {
	$("#" + fieldName + suffix).val(value).change();
	$("#" + fieldName + "_suggest" + suffix).val(label);
	$("#" + fieldName + suffix + "_changed").val(1);
	$("#" + fieldName + suffix + "_results").hide().empty();
}

/******************/
/*  Date Chooser  */
/******************/

function loadDateChooser (inputID, month, year, time) {
	// closeInnerWindow("date_chooser");
	if (typeof(month) == "undefined")
		month = "";

	if (typeof(year) == "undefined")
		year = "";

	if (typeof(time) == "undefined")
		time = 0;

	var params = "input_ID=" + inputID + "&month=" + month + "&year=" + year + "&time=" + time;
	closeScreen("date_chooser");
	openInnerWindow("date_chooser", JEKILL_ROOT + "/schema/load_date_chooser.php", 300, 350, "Date Chooser", params, "", "", 1, 1);
}

function chooseDate (calendarName, dayNum, dayEpoch) {
	var date = new Date();
	date.setTime(dayEpoch*1000);	// Convert to milis
	$("#chooser_month").val(date.getMonth()+1);	// getMonth returns 0-11
	$("#chooser_day").val(date.getDate());
	$("#chooser_year").val(date.getFullYear());
	$(".day").removeClass("selected_day");
	$("#" + calendarName + "_" + dayNum).addClass("selected_day");
}

function selectDate (inputID) {
	var month = $("#chooser_month").val();
	var day = $("#chooser_day").val();
	var year = $("#chooser_year").val();
	var hour = $("#chooser_hour").val();

	$("#" + inputID + "_changed").val(1);

	if (day == "") {
		$("#chooser_day").addClass("error_input");
		return;
	}

	var inputValue = month + "/" + day + "/" + year;
	if (typeof(hour) != "undefined") {
		var minute = $("#chooser_minute").val();
		var second = $("#chooser_second").val();
		var amPm = $("#chooser_am_pm").val();
		if (Number(hour) != 12)
			hour = Number(hour) + Number(amPm);
		inputValue += " " + hour + ":" + minute + ":" + second;
	}
	$("#" + inputID).val(inputValue);
	closeInnerWindow("date_chooser");
}