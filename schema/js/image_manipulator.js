
var httpRequest = null;
var altering = false;

function selectCropSize (width, height, cropSizeButtonID) {
	$(".crop_size_selector").css("border", "none #CCCC00 0px");
	$("#" + cropSizeButtonID).css("border", "solid #CCFF00 2px");
	setDimension(width, height);
}

function initializeIndicator (width, height) {
	// var indicator = getElement("indicator");
	// makeDraggable(indicator);
	// $("#indicator").css("width", width).css("height", height);

	var indicator = createDivide("indicator", width-2, height-2, 0, 0, "<DIV id=\"width_height_indicator\">" + width + " x " + height + "</DIV><DIV id=\"xy_indicator\">(0, 0)</DIV><IMG id=\"indicator_handle\" src=\"images/pixel.gif\">", "draggable");
	$("#indicator").css("cursor", "move");
	$("#indicator_handle").css("width", width).css("height", height);
	indicator.dragListener = indicatorDropped;
	makeDraggable(indicator);
}

function setIndicatorColor (color) {
	$("#indicator").css("border-color", color).css("color", color);
}

function startAlteration (callback) {
	altering = true;
	startAlterationSlave(callback);
}

function startAlterationSlave (callback) {
	if (!altering)
		return;

	callback.call(this);
	setTimeout(function () { startAlterationSlave(callback); }, 100);
}

function stopAlteration () {
	altering = false;
}

function thinner () {
	alterDimension(-1, 0);
}

function thicker () {
	alterDimension(1, 0)
}

function shorter () {
	alterDimension(0, -1)
}

function taller () {
	alterDimension(0, 1)
}

function moveUp () {
	alterPosition(0, -1);
}

function moveDown () {
	alterPosition(0, 1);
}

function moveLeft () {
	alterPosition(-1, 0);
}

function moveRight () {
	alterPosition(1, 0);
}

function zoomIn () {
	alterZoom(1);
}

function zoomOut () {
	alterZoom(-1)
}

function alterZoom (deltaZoom) {
	var zoom = getElement("zoom");
	if ((deltaZoom < 0 && zoom.value <= 1) || (deltaZoom > 0 && zoom.value >= 100))
		return;

	zoom.value = Number(zoom.value) + Number(deltaZoom);
	recalculateZoom();
}

function recalculateZoom () {
	var widthInput = getElement("source_width");
	var heightInput = getElement("source_height");
	var zoom = getElement("zoom");
	var imageStyle = getStyleObject("crop_source");
	if (zoom.value > 100)
		zoom.value = 100;

	var multiplier = zoom.value/100;
	var width = Math.ceil(Number(widthInput.value * multiplier)) + "px";
	var height = Math.ceil(Number(heightInput.value * multiplier)) + "px";
	if (multiplier > 1) {
		var frame = getStyleObject("image_frame");
		frame.width = width;
		frame.height = height;
	}
	imageStyle.width = width;
	imageStyle.height = height;
	resetIndicator(multiplier);
}

function resetIndicator (multiplier) {
	var indicator = getStyleObject("indicator");
	var image = getStyleObject("crop_source");
	var width = stripTrailing(image.width, 2);
	var height = stripTrailing(image.height, 2);
	var indicatorWidth = Math.ceil(stripTrailing(indicator.width, 2)/multiplier);
	var indicatorHeight = Math.ceil(stripTrailing(indicator.height, 2)/multiplier);
	indicator.left = "0px";
	indicator.top = "0px";
	refreshXYIndicator(0, 0);
	// refreshWidthHeightIndicator(indicatorWidth, indicatorHeight);
}

function saveImage () {
	$("#manipulator_form").submit();
	/*
	var location = "save_image_script.php?";
	var file = getElement("filename");
	var outputFile = getElement("output_image");
	var cropX = getElement("crop_x");
	var cropY = getElement("crop_y");
	var cropWidth = getElement("crop_width");
	var cropHeight = getElement("crop_height");
	var sourceWidth = getElement("source_width");
	var sourceHeight = getElement("source_height");
	var backgroundColor = getElement("image_background_color");
	var returnURL = getElement("return_URL");
	var zoom = getElement("zoom");

	location += "filename=" + file.value + "&output_file=" + outputFile.value + "&x=" + cropX.value + "&y=" + cropY.value + "&width=" + cropWidth.value + "&height=" + cropHeight.value + "&zoom=" + zoom.value/100 + "&source_width=" + sourceWidth.value + "&source_height=" + sourceHeight.value + "&background_color=" + backgroundColor.value + "&return_URL=" + returnURL.value;
console.log(location);
	*/
	// window.location = location;
}

function previewCrop () {
	httpRequest = new HttpRequest("crop_preview.php", previewCropCallback);
	var file = getElement("filename");
	var cropX = getElement("crop_x");
	var cropY = getElement("crop_y");
	var cropWidth = getElement("crop_width");
	var cropHeight = getElement("crop_height");
	var sourceWidth = getElement("source_width");
	var sourceHeight = getElement("source_height");
	var backgroundColor = getElement("image_background_color");
	var zoom = getElement("zoom");
	// var previewFilename = getElement("preview_filename");
	
	var params = "filename=" + file.value + "&x=" + cropX.value + "&y=" + cropY.value + "&width=" + cropWidth.value + "&height=" + cropHeight.value + "&zoom=" + zoom.value/100 + "&source_width=" + sourceWidth.value + "&source_height=" + sourceHeight.value + "&background_color=" + backgroundColor.value;
	httpRequest.post(params);
}

function previewCropCallback () {
//	var preview_frame = getElement("preview");
	var response = httpRequest.getResponse();
	response = trim(response);
//	alert(response);
	var previewFrame = getElement("preview_frame");
	previewFrame.innerHTML = response;
}

function alterManually () {
	var cropWidth = getElement("crop_width");
	var cropHeight = getElement("crop_height");
	var cropX = getElement("crop_x");
	var cropY = getElement("crop_y");
	var indicator = getStyleObject("indicator");
// 	alert(cropX.value + ", " + cropY.value + ", " + cropWidth.value + ", " + cropHeight.value); return;
	var relativeTop = -1*($source_height+3) + parseInt(cropY.value);
	// alert(cropX.value + ", " + relativeTop);
	indicator.left = cropX.value + "px";
	indicator.top = relativeTop + "px";
	indicator.width = cropWidth.value + "px";
	indicator.height = cropHeight.value + "px";
	refreshXYIndicator(cropX.value, cropY.value);
	refreshWidthHeightIndicator(cropWidth.value, cropHeight.value);
}

function refreshXYIndicator (x, y) {
	var xyIndicator = getElement("xy_indicator");
	xyIndicator.innerHTML = "(" + x + ", " + y + ")";
}

function validateIndicator () {

	var x = parseInt($("#indicator").css("left"));
	var y = parseInt($("#indicator").css("top"));
	var width = parseInt($("#indicator").css("width")) + 2;		// +2 for the border.
	var height = parseInt($("#indicator").css("height")) + 2;	// +2 for the border.

	var zoom = $("#zoom").val()/100;
	var sourceWidth = $("#source_width").val()*zoom;
	var sourceHeight = $("#source_height").val()*zoom;
	var fixedWidth = $("#fixed_width").val();
	var fixedHeight = $("#fixed_height").val();
	var validateIndicatorPosition = $("#validate_indicator_position").attr("checked");

	if (validateIndicatorPosition) {
		var moveIndicator = false;
		var resizeIndicator = false;

		if (x < 0) {
			x = 0;
			moveIndicator = true;
		}
		if (y < 0) {
			y = 0;
			moveIndicator = true;
		}

		if (x + width > sourceWidth) {
			if (sourceWidth - width >= 0) {
				x = sourceWidth - width;
				moveIndicator = true;
			}
		}
		if (y + height > sourceHeight) {
			if (sourceHeight - height >= 0) {
				y = sourceHeight - height;
				moveIndicator = true;
			}
		}

		if (moveIndicator)
			$("#indicator").animate({ left: x, top: y }, "slow");

		if (resizeIndicator)
			;//$("#indicator").animate({ width: width, height: height }, "slow");
	}

	refreshControls(x, y);
	refreshXYIndicator(x, y);
}


function refreshWidthHeightIndicator (width, height) {
	var widthHeightIndicator = getElement("width_height_indicator");
	widthHeightIndicator.innerHTML = width + " x " + height;
}

function refreshControls (left, top) {
	$("#crop_x").val(left);
	$("#crop_y").val(top);
}

function indicatorDropped () {
	// var sourceWidth = $("#source_width").val();
	// var sourceHeight = $("#source_height").val();
	var left = stripTrailing($("#indicator").css("left"), 2);
	var top = stripTrailing($("#indicator").css("top"), 2);

	/* indicatorDropped is a callback. We need to validate outside of this control-flow 
	   so the indicator can be 'dropped'. */
	setTimeout(function () { validateIndicator(); }, 100);
}

function alterPosition (deltaX, deltaY) {
	var zoom = getElement("zoom");
	var widthInput = getElement("source_width");
	var heightInput = getElement("source_height");
	var magnitude = zoom.value/100;
	var width = Math.ceil(Number(widthInput.value) * magnitude);
	var height = Math.ceil(Number(heightInput.value) * magnitude);
	var cropX = getElement("crop_x");
	var cropY = getElement("crop_y");
	if ((Number(cropX.value) == 0 && Number(deltaX) == -1) || (Number(cropX.value) == width && Number(deltaX) == 1))
		deltaX = 0;
	if (Number(cropY.value) == 0 && Number(deltaY) == -1 || (Number(cropY.value) == height && Number(deltaY) == 1))
		deltaY = 0;

	var indicator = getStyleObject("indicator");
	var left = stripTrailing(indicator.left, 2);
	var top = stripTrailing(indicator.top, 2);

	left = parseInt(left) + parseInt(deltaX);
	top = parseInt(top) + parseInt(deltaY);

	cropX.value = parseInt(cropX.value) + parseInt(deltaX);
	cropY.value = parseInt(cropY.value) + parseInt(deltaY);
	indicator.left = left + "px";
	indicator.top = top + "px";
	// deltaMove("indicator", deltaX, deltaY);
	refreshXYIndicator(cropX.value, cropY.value);
}

function setDimension (width, height) {
	var cropWidth = getElement("crop_width");
	var cropHeight = getElement("crop_height");

	var indicator = getStyleObject("indicator");
	cropWidth.value = width;
	cropHeight.value = height;
	indicator.width = (Number(cropWidth.value)-2) + "px";	// -2: Account for borders.
	indicator.height = (Number(cropHeight.value)-2) + "px";
	refreshWidthHeightIndicator(cropWidth.value, cropHeight.value);
	$("#indicator_handle").css("width", width).css("height", height);
}

function alterDimension (deltaX, deltaY) {
	var cropWidth = getElement("crop_width");
	var cropHeight = getElement("crop_height");
	var x = getElement("crop_x");
	var y = getElement("crop_y");
/*
	if ((Number(x.value) == 1 && Number(deltaX) == -1) || (Number(x.value) == $source_width && Number(deltaX) == 1))
		deltaX = 0;
	if (Number(y.value) == 1 && Number(deltaY) == -1 || (Number(y.value) == $source_height && Number(deltaY) == 1))
		deltaY = 0;
*/
	var width = Number(cropWidth.value) + Number(deltaX);
	var height = Number(cropHeight.value) + Number(deltaY);
	setDimension(width, height);
}

/****************************/
/*  Image Background Color  */
/****************************/

function changeImageBackgroundColor () {

}

// id, color: for callback from color selector.
function imageBackgroundColorChanged (id, color) {
	var backgroundColor = null;
	if (typeof(color) != "undefined") {
		backgroundColor = color;
		$("#image_background_color").val(color);
	}
	else backgroundColor = $("#image_background_color").val();
	$("#background_color_preview").css("background-color", "#" + backgroundColor);
	closeInnerWindow("color_chooser");
}

function chooseImageBackgroundColor () {

}