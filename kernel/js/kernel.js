
var selected = new Array();

function createDirectory (kernelID) {
	var directory = getCurrentDirectory(kernelID);
	var folderName = prompt("Please supply a folder name:");
	if (folderName == "" || folderName == null)
		return;

	$.post("kernel/create_directory.php", { directory: directory, directory_name: folderName }, function (response) {
		refreshKernel(kernelID);
	});
}

function refreshKernel (kernelID) {
	var directory = getCurrentDirectory(kernelID);
	explore(null, kernelID, directory, '');
}

function getSelected () {
	return selected;
}

function getCurrentDirectory (kernelID) {
	return $("#" + kernelID + "_directory").val();
}

function select (event, browserName, directory, clickedID) {
	var shift = event.shiftKey;	// isShiftDown();
	var ctrl = event.ctrlKey;	// isCtrlDown();

	if (!shift && !ctrl)
		deselectAll();

	if (shift && (selected.length > 0)) {
		var selectedID = null;
		for (var id in selected) {
			selectedID = id;
			break;
		}

		var selectedNumIndex = selectedID.lastIndexOf("_") +1;
		var baseName = selectedID.substring(0, selectedNumIndex);
		var idParts = clickedID.split("_");
		var selectedNum = selectedID.substring(selectedNumIndex, selectedID.length);
		var idNum = clickedID.substring(selectedNumIndex, clickedID.length);

		var min = idNum;
		var max = selectedNum;
		if (Number(idNum) > Number(selectedNum)) {
			min = selectedNum;
			max = idNum;			
		}
		for (var i=Number(min)+1; i<Number(max); i++)
			selectFile(baseName + i); 
 }
	selectFile(clickedID);
}

function selectFile (objectID) {
	var objectStyle = getStyleObject(objectID);
	// objectStyle.border = "#000000 solid 1px";
	objectStyle.backgroundColor = "#DDDDDD";
	selected[objectID] = 1;
	selected.length++;
}

function deselect (objectID) {
	// console.log("deselecting " + objectID);
	if (!objectID) {
		selected = new Array();
		selected.length = 0;
		return;
	}

	// objectStyle.border = "#000000 solid 0px";
	$("#" + objectID).css("background-color", "");
	delete selected[objectID];
	selected.length--;
}

function deleteSelected () {
	var selected = getSelected();

	if (selected.length <= 0) {
		alert("Please select a file to delete.");
		return;
	}

	var confirmation = confirm("Are you sure you want to delete these files? This operation cannot be undone.");
	if (!confirmation) {
		return;
	}

	var selectedID = null;
	var filename = "";
	for (var id in selected) {
		if (filename != "")
			filename += ",";
		filename += $("#" + id + "_name").val();
		// $("#" + id).removeClass("kernel_file").html("");
		$("#" + id).remove();
	}

	$.post("kernel/delete_file.php", { filename: filename }, deleteSelectedCallback);
	deselectAll();
}

function deleteSelectedCallback () {}

function deselectAll () {
	for (var key in selected) {
		deselect(key);
	}
}

function explore (event, browserName, directory, clickedID) {
	deselectAll();
	$("#" + browserName).css("opacity", 0.25);
	$.post("kernel/get_directory_browser.php", { directory: directory, name: browserName }, function (response) { exploreCallback(browserName, response); });
}

function exploreCallback (browserName, response) {
	$("#" + browserName).html(response).css("opacity", 1.0);
	selected = new Array();
}

function edit (event, browserName, url, clickedID) {
	openInnerWindow(clickedID + "_editor_window", "kernel/editor.php", 700, "", "Edit " + url, "filename=" + url + "&name=" + browserName);
	deselect(clickedID);
}

function saveFile (editorName, fileURL) {
	var contents = $("#" + editorName).val();
	$.post("kernel/save_file.php", { filename: fileURL, contents: contents }, function () { saveFileCallback() });
}

function saveFileCallback () {
}

function viewWebComponent (event, browserName, componentURL, clickedID) {
	window.open(componentURL);
}