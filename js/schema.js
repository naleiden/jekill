

/*******************/
/*  Control Panel  */
/*******************/

function valueChanged (inputID) {
	$("#" + inputID + "_changed").val(1);
}

function saveEntity (formID) {
	$("#formID").submit();
}

/************/
/*  Search  */
/************/

function search (tableName) {
	var searchIn = $("#search_in").val();
	var operator = $("#operator").val();
	var keywords = $("#keywords").val();
	if (keywords == "")
		return;

	window.location = "control_panel.php?table=" + tableName + "&search_in=" + searchIn + "&op=" + escape(operator) + "&search_for=" + escape(keywords);
}

/*****************************/
/*  Delete Top-Level Record  */
/*****************************/

function deleteEntity (tableName, entityID, subrecordDivID) {
	var confirmation = confirm("Are you sure you want to delete this record? This operation cannot be undone.");
	if (!confirmation)
		return;
	else {
		$.post("schema/delete_entity.php", { table: tableName, entity_ID: entityID }, function () { deleteEntityCallback(tableName, subrecordDivID); });
	}
}

function deleteEntityCallback (tableName, subrecordDivID) {
	if (typeof(subrecordDivID) != "undefined") {
		$("#" + subrecordDivID).slideUp("slow", function () { $("#" + subrecordDivID).remove() });
	}
	else window.location = "control_panel.php?func=browse&table=" + tableName;
}

/**************************/
/*  Delete Uploaded File  */
/**************************/

function deleteUploadedFile (tableName, fieldName, entityID, filename) {
	var confirmation = confirm("Are you sure you want to delete this file? This operation cannot be undone.");
	if (!confirmation)
		return;
	else {
		$.post("schema/delete_uploaded_file.php", { table_name: tableName, field_name: fieldName, entity_ID: entityID, filename: filename }, function () { deleteUploadedFileCallback(fieldName); });
	}
}

function deleteUploadedFileCallback (fieldName) {
	$("#preview_" + fieldName).fadeOut("slow");
	$("#delete_" + fieldName).fadeOut("slow");
}

/****************************/
/*  Preview Uploaded Image  */
/****************************/

function imagePreview (event, imageURL) {
	var x = event.clientX + 10;
	var y = event.clientY + 10;
	var imageHTML = "<IMG src='" + imageURL + "' style='width: 150px;'>";

	if ($("#image_preview").is("DIV"))
		$("#image_preview").stop().html(imageHTML).css("left", x + "px").css("top", y + "px").fadeIn();
	else $("<DIV id=\"image_preview\">" + imageHTML + "</DIV>").css("position", "absolute").css("left", x + "px").css("top", y + "px").appendTo("body");
	// $.post("ajax/preview_image.php", { image_URL: imageURL }, function (response) { imagePreviewCallback(response, x, y) });
}

function closeImagePreview () {
	$("#image_preview").stop().fadeOut();
	// $("body").remove($("#image_preview"));
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

/***********************************/
/*  Sub-table Record Manipulation  */
/***********************************/

function showSubrecords (fieldName, tableName) {
	$("#show_" + fieldName + "_" + tableName).slideUp("slow");
	$("#" + fieldName + "_" + tableName + "_container").slideDown("slow");
}

function hideSubrecords (fieldName, tableName) {

}

function incrementNumSubtableRecords (fieldName) {
	return alterNumSubtableRecords(fieldName, 1);
}

function decrementNumSubtableRecords (fieldName) {
	/* Do not change the number of records - that way, if one is deleted from the middle, we know the range of
	   valid records to update. */
	return alterNumSubtableRecords(fieldName, 0); // -1);
}

function alterNumSubtableRecords (fieldName, delta) {
	var numRecords = $("#num_" + fieldName + "s").val();
	numRecords = Number(numRecords) + delta;
	$("#num_" + fieldName + "s").val(numRecords);
	return numRecords;
}

function addSubtableRecord (fieldName, tableName) {
	showSubrecords(fieldName, tableName);
	var numRecords = incrementNumSubtableRecords(fieldName);
	$.getJSON("schema/add_subtable_record.php", { table_name: tableName, record_num: numRecords }, function (json) { addSubtableRecordCallback(fieldName, tableName, numRecords, json); });
}

function addSubtableRecordCallback (fieldName, tableName, numRecords, json) {
	var form = json.form;
	var script = json.script;
	$("<DIV>").html(form).attr("id", fieldName + "_" + tableName + "_" + numRecords).appendTo("#" + fieldName + "_" + tableName + "_container");
	$(script).appendTo("head");
}

function deleteSubtableRecord (fieldName, tableName, entityID, recordNum) {
	var confirmation = confirm("Are you sure you want to delete this record? This operation cannot be undone.");
	if (!confirmation)
		return;
	else {
		decrementNumSubtableRecords(fieldName);
		$.post("schema/delete_entity.php", { table: tableName, entity_ID: entityID }, function () { deleteSubtableRecordCallback(fieldName, tableName, recordNum); });
	}
}

function deleteSubtableRecordCallback (fieldName, tableName, recordNum) {
	$("#" + fieldName + "_" + tableName + "_" + recordNum).slideUp("slow", function () { $("#" + fieldName + "_" + tableName + "_" + recordNum).remove(); });
}

function getExistingSubtableRecords (fieldName, fieldLabel, tableName) {
	$.post("schema/get_existing_subtable_records.php", { field_name: fieldName, field_label: fieldLabel, table_name: tableName }, function (response) { getExistingSubtableRecords(fieldName, fieldLabel, tableName, response); });
}

function getExistingSubtableRecordCallback (fieldName, fieldLabel, tableName, response) {
	openClientWindow("existing_" + tableName, response, 450, 500, "", "", "", 1);
}

function loadExistingSubtableRecord (recordID, fieldName, tableName) {
	closeClientInnerWindow("existing_" + tableName);
	var numRecords = incrementNumSubtableRecords(fieldName);
	$.getJSON("schema/add_subtable_record.php", { record_ID: recordID, record_num: numRecords, table_name: tableName }, function (json) { addSubtableRecordCallback(fieldName, tableName, numRecords, json); });
}


/*****************************/
/*  Kernel / File Selection  */
/*****************************/

function browseFile (fieldName, directory, extensions) {
	if (!extensions)
		extensions = "";
	if (!directory)
		directory = "";

	openInnerWindow("file_browse_window", "schema/select_file.php", 800, "", "Select File", "input_name=" + fieldName + "&directory=" + directory + "&extensions=" + extensions, "", "", 1);
}

function chooseFile (event, browserName, filename, fileID) {
	var inputID = $("#browse_input").val();

	$("#" + inputID + "_changed").val(1);
	$("#" + inputID).val(filename.substring(3, filename.length));
	closeInnerWindow("file_browse_window");
}