<?php

require_once("../../base/settings.php");

?>

function startSWFUpload () {
	return true;
}

//this function includes all necessary js files for the application
function include (file) {
	var script  = document.createElement('script');
script.src  = file;
script.type = 'text/javascript';
script.defer = true;

document.getElementsByTagName('head').item(0).appendChild(script);
}

var JEKILL_ROOT = "<?php echo $SETTINGS['JEKILL_ROOT']; ?>";

/****************************/
/*  ASSOCIATED RECORD ROWS  */
/****************************/
function loadAssociatedRecords (tableName, fieldName, entityID) {
	$.post(JEKILL_ROOT + "/schema/load_associated_rows.php", { table_name: tableName, field_name: fieldName, entity_ID: entityID }, function (response) {
		$("#" + tableName + "_" + entityID + "_subrecords").html(response).slideDown("slow");
		$("#" + fieldName + "_" + entityID + "_subrecords").attr("src", "schema/images/up.gif").attr("onclick", "").bind("click", function () { unloadAssociatedRecords(tableName, fieldName, entityID); });
	});
}

function unloadAssociatedRecords (tableName, fieldName, entityID) {
	$("#" + tableName + "_" + entityID + "_subrecords").slideUp("slow", function () {
		$("#" + fieldName + "_" + entityID + "_subrecords").attr("src", "schema/images/down.gif").unbind("click").bind("click", function () { loadAssociatedRecords(tableName, fieldName, entityID); });
	});
}

/*****************/
/*  ANNOTATIONS  */
/*****************/

function annotateImage (parentTable, imageField, parentID, annotationTable, annotationSuffix) {
	var x = $("#x" + annotationSuffix).val();
	var y = $("#y" + annotationSuffix).val();
	var width = $("#width" + annotationSuffix).val();
	var height = $("#height" + annotationSuffix).val();
	var color = $("#color" + annotationSuffix).val();
	var annotation = $("#label" + annotationSuffix).val();

	if (typeof(width) == "undefined")
		width = "";
	if (typeof(height) == "undefined")
		height = "";
	if (typeof(color) == "undefined")
		color = "";
	if (typeof(annotation) == "undefined")
		annotation = "";

	var params = "parent_table=" + parentTable + "&image_field=" + imageField + "&parent_ID=" + parentID + "&annotation_table=" + annotationTable + "&annotation_suffix=" + annotationSuffix + "&annotation=" + annotation + "&width=" + width + "&height=" + height + "&x=" + x + "&y=" + y + "&color=" + color;
	openInnerWindow("annotate_image", JEKILL_ROOT + "/schema/annotate_image.php", 850, 675, "Annotate Image", params);

	setTimeout(function () { initializeAnnotation() }, 2000);
}

function initializeAnnotation () {
	makeDraggableHandle(getElement("annotation_drag_handle"));
	makeResizeableHandle(getElement("annotation_resize_handle"));
}

function annotate (annotationTable, annotationSuffix) {
	var annotation = $("#annotation");

	var zoom = $("#annotation_zoom").val();
	var x = parseInt(annotation.css("left"))/zoom;
	var y = parseInt(annotation.css("top"))/zoom;
	$("#x" + annotationSuffix).val(x);
	$("#y" + annotationSuffix).val(y);
	$("#width" + annotationSuffix).val(stripTrailing(annotation.css("width"), 2));
	$("#height" + annotationSuffix).val(stripTrailing(annotation.css("height"), 2));


	$("#x" + annotationSuffix + "_changed").val(1);
	$("#y" + annotationSuffix + "_changed").val(1);
	$("#width" + annotationSuffix + "_changed").val(1);
	$("#height" + annotationSuffix + "_changed").val(1);

	closeInnerWindow("annotate_image");
}

/********************/
/*  IMAGES / FILES  */
/********************/

function browseImages (parentField, directory) {
	var extensions = ".bmp,.jpg,.jpeg,.gif,.png";
	// var directory = "/images";
	browseFiles(parentField, directory, extensions, "addImages('" + parentField + "')");
}

function browseFiles (parentField, directory, extensions, callback) {
	if (typeof(extensions) == "undefined")
		extensions = "";

	if (typeof(callback) == "undefined")
		callback = "addFiles('" + parentField + "')";

	openInnerWindow("browse_files", JEKILL_ROOT + "/kernel/browse_files.php", 500, 400, "File Selector", "directory=" + directory + "&extensions=" + extensions + "&callback=" + callback);
}

function refreshImagesPreview (parentField) {
	var serialized = $("#" + parentField).val();

	$.post(JEKILL_ROOT + "/schema/get_images_preview_divide.php", { field_name: parentField, serialized: serialized }, function (response) {
		$("#" + parentField + "_files").html(response);
	});
}

function splitSerialized (parentField) {
	var value = $("#" + parentField).val();
	return value.match(/a:2:{[^{^}]*}/g);
}

function joinSerialized (imageArray) {
	var serialized = "";
	var numImages = 0;
	for (var i=0; i<imageArray.length; i++) {
		if (typeof(imageArray[i]) == "undefined")
			continue;

		serialized += "i:" + i + ";" + imageArray[i];
		numImages++;
	}
	serialized = "a:" + numImages + ":{" + serialized + "}";
	return serialized;
}

function setSerialized (parentField, imageArray) {
	var serialized = joinSerialized(imageArray);
	$("#" + parentField).val(serialized);
	$("#" + parentField + "_changed").val(1);
}

function addImages (parentField) {
	$("#" + parentField + "_files").addClass("file_bank");
	var field = $("#" + parentField);
	var fieldValue = field.val();

	if (selected.length > 0) {
		var existingImages = splitSerialized(parentField);
		var numImages = 0;
		fieldValue = "";
		if (existingImages != null) {
			for (i=0; i<existingImages.length; i++) {	//  i in existingImages) {
				// alert("In there " + i + ": " + existingImages[i]);
				fieldValue += "i:" + numImages + ";" + existingImages[i];
				numImages++;				
			}
		}

		for (var i in selected) {
			var imageURL = $("#" + i + "_name").val();
			fieldValue += "i:" + numImages + ";a:2:{s:3:\"url\";s:" + imageURL.length + ":\"" + imageURL + "\";s:7:\"caption\";s:0:\"\";}"
			numImages++;
		}

		fieldValue = "a:" + numImages + ":{" + fieldValue + "}";
		$("#" + parentField).val(fieldValue);
		$("#" + parentField + "_changed").val(1);
	}
	closeInnerWindow("browse_files");
	refreshImagesPreview(parentField);
}

function setCaption (parentField, fileIndex, caption) {
	var images = splitSerialized(parentField);
	if (images != null) {
		var serializedImage = images[fileIndex];
		var captionIndex = serializedImage.indexOf("\"caption\"");
		var partial = serializedImage.substring(0, captionIndex + 9);	// 9: strlen("caption")
		partial += ";s:" + caption.length + ":\"" + caption + "\";}";
		images[fileIndex] = partial;
	}
	var serialized = setSerialized(parentField, images);
}

function shiftFileLeft (parentField, fileNum) {
	shiftFile(parentField, fileNum, -1);
}

function shiftFileRight (parentField, fileNum) {
	shiftFile(parentField, fileNum, 1);
}

function shiftFile (parentField, fileIndex, direction) {
	var images = splitSerialized(parentField);

	if (fileIndex+direction < 0 || fileIndex + direction >= images.length)
		return;

	var destIndex = fileIndex+direction;
	var srcIndex = fileIndex;

	var temp = images[destIndex];
	images[destIndex] = images[srcIndex];
	images[srcIndex] = temp;

	setSerialized(parentField, images);
	refreshImagesPreview(parentField);
}


function disassociateAllFiles (parentField) {
	$("#" + parentField).val("");
	$("#" + parentField + "_changed").val(1);
	$("#" + parentField + "_files").slideUp("slow", function () {
		$("#" + parentField + "_files").removeClass("file_bank").css("display", "block").html("");
	});
}

function disassociateFile (parentField, fileIndex) {
	var confirmation = confirm("Are you sure you want to disassociate this file?");
	if (!confirmation)
		return;

	var images = splitSerialized(parentField);

	delete images[fileIndex];

	setSerialized(parentField, images);
	refreshImagesPreview(parentField);
}

function scrollMenuLeft () {
	scrollMenu(300, -1200, 0);
}

function scrollMenuRight () {
	scrollMenu(-300, -100, 0);
}

function scrollMenu (amount, min, max) {
	var left = $("#menubar_carrier").css("left");
	left = left.substring(0, left.length-2);
	left = Number(left) + Number(amount);
	if (left < min || left > max)
		return;
	left = left + "px";
	$("#menubar_carrier").stop().animate({ left: left }, "slow");
}

/*********************/
/*  Update Database  */
/*********************/

function updateDatabase (tableName, entityID) {
	var i = 1;
	var params = "table_name=" + tableName;
	while (typeof($("#incompatible_field_name_" + i).val()) != "undefined") {
		params += "&field_name_" + i + "=" + $("#incompatible_field_name_" + i).val();
		i++;
	}
	$.post(JEKILL_ROOT + "/schema/add_database_field.php", params, function (response) { updateDatabaseCallback(tableName, response); });
}

function updateDatabaseCallback (tableName, response) {
	$(".warning").slideUp();
	$("#" + tableName + "_notes").html(response).slideDown();
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

/***************************/
/*  Copy Top-Level Record  */
/***************************/

function copyEntity (tableName) {
	var entityID = $("#" + tableName + "_ID").val();
	$("#" + tableName + "_ID").val("");		// Clear table ID to force re-insertion.
	$("#source_page").val("");			// Clear pre-defined return-to page.
	if (!window[tableName + "Validation"](""))	// Call Validation function reflectively. "": No table suffix
		$("#" + tableName + "_ID").val(entityID);	// Form validation failed. Reset table ID.
}

/*****************************/
/*  Delete Top-Level Record  */
/*****************************/

function deleteEntity (tableName, entityID, subrecordDivID) {
	var confirmation = confirm("Are you sure you want to delete this record? This operation cannot be undone.");
	if (!confirmation)
		return;
	else {
		$.post(JEKILL_ROOT + "/schema/delete_entity.php", { table: tableName, entity_ID: entityID }, function () { deleteEntityCallback(tableName, subrecordDivID); });
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
		$.post(JEKILL_ROOT + "/schema/delete_uploaded_file.php", { table_name: tableName, field_name: fieldName, entity_ID: entityID, filename: filename }, function () { deleteUploadedFileCallback(fieldName); });
	}
}

function deleteUploadedFileCallback (fieldName) {
	$("#preview_" + fieldName).fadeOut("slow");
	$("#delete_" + fieldName).fadeOut("slow");
}

/*******************************/
/*  Manipulate Uploaded Image  */
/*******************************/

function manipulateImage (imageURL) {
	formatImage(imageURL, "", "");
}

function formatImage (imageURL, width, height, outputURL, callback) {
	if (typeof(outputURL) == "undefined")
		outputURL = imageURL;
	if (typeof(callback) == "undefined")
		callback = "window.parent.closeInnerWindow('format_image');"

	var params = "max_width=800&image_URL=" + imageURL + "&width=" + width + "&height=" + height + "&output_image=" + outputURL + "&callback=" + callback;
	openInnerWindow("format_image", JEKIL_ROOT + "/schema/embedded_image_manipulator.php", 970, 600, "Crop Image", params);
}

function saveImageExcerpt (excerptID, path) {
	closeInnerWindow("format_image");
	$("#" + excerptID).val(path);
	$("#" + excerptID + "_changed").val(1);
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
	// $.post(JEKILL_ROOT + "/ajax/preview_image.php", { image_URL: imageURL }, function (response) { imagePreviewCallback(response, x, y) });
}

function closeImagePreview () {
	$("#image_preview").stop().fadeOut();
	// $("body").remove($("#image_preview"));
}

/***********************************/
/*  Sub-table Record Manipulation  */
/***********************************/

function reorderSubrecord (tableName, fieldName, suffix, recordNum, direction) {
	if (typeof(suffix) == "undefined")
		suffix = "";

	var prefix = "#" + tableName + "_" + fieldName + suffix + "_";

	if (direction > 0) {
		var next = $(prefix + recordNum).next(".subtable_record");
		if (next.size() == 0)
			return;

		var nextID = next.attr("id")
		var targetIndex = $("#" + nextID + "_record_num").val();
		var sourceIndex = $(prefix + recordNum + "_record_num").val();

		$("#" + nextID + "_record_num").val(sourceIndex);
		$(prefix + recordNum + "_record_num").val(targetIndex);
// console.log(sourceIndex + " -> " + targetIndex);

		next.children(".record_num").val();
		next.remove().insertBefore(prefix + recordNum).toggleClass("row_even").toggleClass("row_odd");
		$(prefix + recordNum).toggleClass("row_even").toggleClass("row_odd");
	}
	else {
		var prev = $(prefix + recordNum).prev(".subtable_record");
		if (prev.size() == 0)
			return;

		var prevID = prev.attr("id")
		var targetIndex = $("#" + prevID + "_record_num").val();
		var sourceIndex = $(prefix + recordNum + "_record_num").val();

		$("#" + prevID + "_record_num").val(sourceIndex);
		$(prefix + recordNum + "_record_num").val(targetIndex);

		prev.remove().insertAfter(prefix + recordNum).toggleClass("row_even").toggleClass("row_odd");
		$(prefix + recordNum).toggleClass("row_even").toggleClass("row_odd");
	}
}

function showSubrecords (tableName, fieldName) {
	$("#show_" + tableName + "_" + fieldName).slideUp("slow");
	$("#" + tableName + "_" + fieldName + "_container").slideDown("slow");
}

function hideSubrecords (fieldName, tableName) {
}

function showSubrecordForm (tableName, fieldName, suffix, recordNum) {
	$("#" + tableName + "_" + fieldName + suffix + "_" + recordNum + "_form").slideDown("slow");
	$("#" + tableName + "_" + fieldName + suffix + "_" + recordNum + "_link").attr("href", "javascript: hideSubrecordForm('" + tableName + "', '" + fieldName + "', '" + suffix + "', " + recordNum + ")");
}

function hideSubrecordForm (tableName, fieldName, suffix, recordNum) {
	$("#" + tableName + "_" + fieldName + suffix + "_" + recordNum + "_form").slideUp("slow");
	$("#" + tableName + "_" + fieldName + suffix + "_" + recordNum + "_link").attr("href", "javascript: showSubrecordForm('" + tableName + "', '" + fieldName + "', '" + suffix + "', " + recordNum + ")");
}

function incrementNumSubtableRecords (fieldName, suffix) {
	return alterNumSubtableRecords(fieldName + suffix, 1);
}

function decrementNumSubtableRecords (fieldName) {
	/* Do not change the number of records - that way, if one is deleted from the middle, we know the range of
	   valid records to update. */
	return alterNumSubtableRecords(fieldName, 0); // -1);
}

function alterNumSubtableRecords (fieldName, delta) {
	var numRecords = $("#num_" + fieldName + "s").val();
	numRecords = Number(numRecords) + Number(delta);
	$("#num_" + fieldName + "s").val(numRecords);
	return numRecords;
}

function addExistingSubtableRecords (tableName, fieldName, suffix) {
	/* Retrieve record IDs from checked checkboxes. */
	$(".add_existing_record:checked").each(function () {
		var recordID = $(this).val();
		$(this).removeAttr("checked");
		addSubtableRecord(tableName, fieldName, recordID, suffix);
	});
}

function addExistingSubtableRecord (tableName, fieldName, recordID, suffix) {
	closeInnerWindow("subtable_records");
	addSubtableRecord(tableName, fieldName, recordID, suffix);
}

function loadExistingSubtableRecords (parentTable, fieldName, suffix) {
	$("#" + fieldName + suffix + "_add_" + fieldName).slideUp("slow");

	if (typeof(suffix) == "undefined")
		suffix = "";
	var params = "table_name=" + parentTable + "&field_name=" + fieldName + "&suffix=" + suffix;
	openInnerWindow("subtable_records", JEKILL_ROOT + "/schema/load_subtable_records.php", 400, "", "Select Existing Record", params, "", "", 1);
}

function toggleAddRecordOptions (fieldName) {
	$("#" + fieldName + "_add_options").toggle("slow");
}

function addSubtableRecord (tableName, fieldName, recordID, suffix) {
	toggleAddRecordOptions(fieldName);	// Hide 'Add' Options
	showSubrecords(tableName, fieldName);

	if (typeof(recordID) == "undefined")
		recordID = "";

	if (typeof(suffix) == "undefined")
		suffix = "";

	var numRecords = incrementNumSubtableRecords(fieldName, suffix);
	var arguments = { table_name: tableName, field_name: fieldName, record_num: numRecords, record_ID: recordID, suffix: suffix };
	// $.getJSON("schema/add_subtable_record.php", arguments, function (json) { addSubtableRecordCallback(fieldName, tableName, numRecords, json); });
	$.post(JEKILL_ROOT + "/schema/add_subtable_record.php", arguments, function (response) {
		addSubtableRecordCallback(fieldName, tableName, numRecords, response, suffix);
	});
}

function addSubtableRecordCallback (fieldName, tableName, numRecords, response, suffix) {	// json) {
	if (typeof(suffix) == "undefined")
		suffix = "";

	$("#" + tableName + "_" + fieldName + suffix + "_container").append(response);
/*
	var form = json.form;
	var script = json.script;
	$("#" + tableName + "_" + fieldName + "_container").append(form);
	$(script).appendTo("head");
*/
}

function undeleteSubtableRecord (fieldName, tableName, suffix, entityID, recordNum) {
	$("#" + tableName + "_" + fieldName + suffix + "_" + recordNum + "_deleted").val(0);
	$("#" + tableName + "_" + fieldName + suffix + "_" + recordNum + "_deleted")
	$("#" + tableName + "_" + fieldName + suffix + "_" + recordNum).removeClass("subtable_record_deleted").children(".delete_message").remove();

	var deleteButton = $("#delete_" + tableName + "_" + fieldName + "_" + recordNum);
	deleteButton.attr("src", "/admin/schema/images/close.gif").click(function () { deleteSubtableRecord(fieldName, tableName, suffix, entityID, recordNum) }).attr("title", "");
}

function deleteSubtableRecord (fieldName, tableName, suffix, entityID, recordNum) {
	var confirmation = confirm("Are you sure you want to delete this record? This operation cannot be undone.");
	if (!confirmation)
		return;

	if (typeof(suffix) == "undefined")
		suffix = "";

	var deleteWarning = "<div class=\"delete_message\">(Marked for deletion - Save to delete)</div>";

	// Mark for deletion.
	$("#" + tableName + "_" + fieldName + suffix + "_" + recordNum + "_deleted").val(1);
	$("#" + tableName + "_" + fieldName + suffix + "_" + recordNum).addClass("subtable_record_deleted").append(deleteWarning);	// css("background-color", "#FFEEEE");
	$("#delete_" + tableName + "_" + fieldName + "_" + recordNum).attr("src", "/admin/schema/images/undelete.gif").removeAttr("onclick").click(function () { undeleteSubtableRecord(fieldName, tableName, suffix, entityID, recordNum) }).attr("title", "");

/*  OLD WAY OF DELETING
	else {
		decrementNumSubtableRecords(fieldName);
		$.post(JEKILL_ROOT + "/schema/delete_entity.php", { table: tableName, entity_ID: entityID }, function () { deleteSubtableRecordCallback(fieldName, tableName, recordNum, "#CC0000"); });
	}
*/
}

function deleteSubtableRecordCallback (tableName, fieldName, recordNum, backgroundColor) {
	$("#" + tableName + "_" + fieldName + "_" + recordNum).css("background-color", backgroundColor);
//	$("#" + tableName + "_" + fieldName + "_" + recordNum).append("<div style=\"background-color: " + backgroundColor + "; height: 100%; opacity: 0.0; width: 100%;\"></div>");
/*
	$("#" + tableName + "_" + fieldName + "_" + recordNum).slideUp("slow", function () { 
		$("#" + tableName + "_" + fieldName + "_" + recordNum).remove(); 
	});
*/
}

function getExistingSubtableRecords (fieldName, fieldLabel, tableName) {
	$.post(JEKILL_ROOT + "/schema/get_existing_subtable_records.php", { field_name: fieldName, field_label: fieldLabel, table_name: tableName }, function (response) { getExistingSubtableRecords(fieldName, fieldLabel, tableName, response); });
}

function searchExistingSubtableRecords (fieldName, tableName) {
	var search = $("#existing_record_search").val();
	$.post(JEKILL_ROOT + "/schema/get_subtable_records.php", { field_name: fieldName, table_name: tableName, search: search }, function (response) { $("#subrecord_chooser").html(response); });
}

function getExistingSubtableRecordCallback (fieldName, fieldLabel, tableName, response) {
	openClientWindow("existing_" + tableName, response, 450, 500, "", "", "", 1);
}

function loadExistingSubtableRecord (recordID, fieldName, tableName) {
	closeClientInnerWindow("existing_" + tableName);
	var numRecords = incrementNumSubtableRecords(fieldName);
	$.getJSON("schema/add_subtable_record.php", { record_ID: recordID, record_num: numRecords, table_name: tableName }, function (json) { addSubtableRecordCallback(fieldName, tableName, numRecords, json); });
}

function relinkSubrecord (tableName, fieldName, recordID, subrecordID, recordNum) {

}

function unlinkSubrecord (tableName, fieldName, suffix, recordID, subrecordID, recordNum) {
	var confirmation = confirm("Are you sure you want to unlink this record? This will not actually delete the record, just disassociate it.");
	if (!confirmation)
		return;

	if (typeof(suffix) == "undefined")
		suffix = "";

	/* Mark for disassociation. */
	$("#" + tableName + "_" + fieldName + suffix + "_" + recordNum + "_disassociated").val(1);
	$("#" + tableName + "_" + fieldName + suffix + "_" + recordNum).css("background-color", "#CCCC99");	// addClass("disassociated");

/* OLD WAY OF DISASSOCIATING
	$.post(JEKILL_ROOT + "/schema/unlink_subrecord.php", { table_name: tableName, field_name: fieldName, entity_ID: recordID, link_ID: subrecordID }, function () { deleteSubtableRecordCallback(tableName, fieldName, recordNum, "#CCCC00"); });
*/
}

/*****************************/
/*  Kernel / File Selection  */
/*****************************/

function browseFile (tableName, fieldName, directory, extensions, suffix) {
	if (!extensions)
		extensions = "";
	if (!directory)
		directory = "";

	if (!suffix)
		suffix = "";

	openInnerWindow("file_browse_window", "schema/select_file.php", 800, "", "Select File", "table_name=" + tableName +"&field_name=" + fieldName + "&directory=" + directory + "&extensions=" + extensions + "&suffix=" + suffix, "", "", 1);
}

function chooseFile (event, browserName, filename, fileID) {
	var inputID = $("#browse_input").val();

	$("#" + inputID + "_changed").val(1);

	if (filename.substring(0, 2) == "./");
		filename = filename.substring(2, filename.length);

	$("#" + inputID).val(filename);
	closeInnerWindow("file_browse_window");
}

/*********************/
/*  Manipulate Copy  */
/*********************/

function addCopy () {
	var copyName = "";
	while (copyName == "") {
		copyName = prompt("Please enter a name for this block of copy. No special characters please.");
		if (copyName == null)
			return;
		var validName = copyName.match(/^[a-zA-Z]+[a-zA-Z0-9( )]*$/);
		if (!validName) {
			copyName = "";
		}
	}
	$.post(JEKILL_ROOT + "/schema/add_copy.php", { copy_name: copyName }, function () { window.location = window.location; });
}


/*************************/
/*  Manipulate Settings  */
/*************************/

function addSetting () {
	var settingName = "";
	while (settingName == "") {
		settingName = prompt("Please enter a name for this setting. No special characters please.");
		if (settingName == null)
			return;
		var validName = settingName.match(/^[a-zA-Z]+[a-zA-Z0-9( )]*$/);
		if (!validName) {
			settingName = "";
		}
	}
	$.post(JEKILL_ROOT + "/schema/add_setting.php", { setting_name: settingName }, function () { window.location = window.location; });
}


/***********************/
/*  Manipulate Schema  */
/***********************/

function createDatabaseTable (tableName) {
	$.post(JEKILL_ROOT + "/schema/create_database_table.php", { table_name: tableName }, function (response) { createDatabaseTableCallback(response, tableName); });
}

function createDatabaseTableCallback (response, tableName) {
	$(".warning").slideUp("slow");
}

function validateTableSchema () {
	var tableName = $("#table_label").val();
	if (tableName == "") {
		registerErrors("schema_error", new Array("Please specify a 'Table Label'"));
		return false;
	}

	var validName = tableName.match(/^[a-zA-Z]+[a-zA-Z0-9( )]*$/);
	if (!validName) {
		registerErrors("schema_error", new Array("Special characters are not permitted in 'Table Label'"));
		return false;
	}
	$("#schema_form").attr("action", "schema/save_database_table.php").submit();
	return true;
}

function editSchemaTableChanged () {
	window.location = "control_panel.php?func=schema&table=" + $("#table").val();
}

function addDatabaseField (tableName) {
	var fieldName = "";
	while (fieldName == "") {
		fieldName = prompt("Please enter the name of the field. No spaces or special characters, please.");
		if (fieldName == null)
			return;

		// Check 'fieldName' with regexp
		if (!fieldName.match(/^[a-zA-Z]+[a-zA-Z0-9_]*$/))
			fieldName = "";
	}
	var fieldNum = $("#num_fields").val();
	fieldNum = Number(fieldNum) +1;
	$("#num_fields").val(fieldNum);
	$.post(JEKILL_ROOT + "/schema/get_schema_field_row.php", { table_name: tableName, field_num: fieldNum, field_name: fieldName }, function (response) { addDatabaseFieldCallback(fieldName, response); });
}

function addDatabaseFieldCallback (fieldName, response) {
	$("#fields").append(response);
	$("#table_sort").append("<OPTION value='" + fieldName + "'>" + fieldName + "</OPTION>");
}

function addFieldModifier (fieldName) {
	
}

function dropDatabaseField (table, field) {
	var confirmation = confirm("Are you sure you want to delete this field? All data associated with this field will be permanently lost. This operation cannot be undone.");
	if (!confirmation)
		return;

	$.post(JEKILL_ROOT + "/schema/delete_database_field.php", { table_name: table, field_name: field }, function () { dropDatabaseEntityCallback(field); });
}

function dropDatabaseEntityCallback (entityID) {
	$("#" + entityID).slideUp("fast", function() { $("#" + entityID).remove() });
}

function dropDatabaseFieldModifier (table, field, fieldNum, modifierNum) {

}


function dropDatabaseTable (tableName) {
	var failureMessage = "Verification mismatch. The table '" + tableName + "' will not be dropped.";
	var message = "You are about to drop the database table '" + tableName + "'. This operation will delete all data associated with the table and cannot be undone.";
	var success = function () { $.post(JEKILL_ROOT + "/schema/drop_database_table.php", { table_name: tableName }, function () { window.location = "control_panel.php?func=schema" }) };
	verifyOperation(message, success, failureMessage);
}

function verifyOperation (message, verifiedCallback, failureMessage) {
	var alpha = "abcdefghijklmnopqrstuvwxyx1234567890";
	var verificationText = "";
	for (var i=0; i<6; i++) {
		var index = Math.floor(Math.random()*36);
		verificationText += alpha[index];
	}
	var userVerification = prompt(message + " Please enter the following verification text to proceed:\n\n" + verificationText);
	if (userVerification == null)
		return;

	else if (userVerification != verificationText) {
		if (typeof(failureMessage) != "undefined")
			alert(failureMessage);
		return;
	}
	else {
		verifiedCallback.call(this);
	}
}

