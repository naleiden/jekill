
function toggleRichEditor(id) {
	if (!tinyMCE.get(id))
		tinyMCE.execCommand('mceAddControl', false, id);
	else
		tinyMCE.execCommand('mceRemoveControl', false, id);
}

/*******************/
/*  Control Panel  */
/*******************/

function ensureUnique (tableName, fieldName, entityID, suffix, originalValue) {
	var value = $("#" + fieldName + suffix).val();
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

	$.getJSON("/schema/ensure_unique_table_value.php", { table_name: tableName, field_name: fieldName, entity_ID: entityID, value: value }, function (response, textStatus) {
		if (!response.unique) {
			alert(response.message);
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
	var errorMessage = "The following errors occured when trying to submit:<UL>";
	for (var i in errors) {
		errorMessage += "<LI>" + errors[i];
	}
	errorMessage += "</UL>";
	$("#" + errorDivID).html(errorMessage).fadeIn("slow");
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
	openInnerWindow("date_chooser", "schema/load_date_chooser.php", 300, 350, "Date Chooser", params, "", "", 1, 1);
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