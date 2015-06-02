
function loadColorPalette (color, action, actOn, directory) {
	if (typeof(directory) == "undefined")
		directory = "schema/";
	$.post(directory + "load_color_selector.php", { color: color, action: action, act_on: actOn }, function (response) { loadColorPaletteCallback(response) });
}

function loadColorPaletteCallback (response) {
	$("#palette_container").html(response);
}

function viewColor (color) {
  $("#color_viewer").css("background-color", "#" + color);

  $("#hex_preview").html("#" + color);
}