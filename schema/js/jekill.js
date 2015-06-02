
function initJekill (pageName, contentSuffix, returnURL) {
	/* Add Jekill style. */
	$("head").append("<link rel=\"stylesheet\" type=\"text/css\" href=\"schema/css/jekill.css\" />");
	$("head").append("<link rel=\"stylesheet\" type=\"text/css\" href=\"kernel/css/kernel_style.css\" />");
	// $("head").append("<link rel=\"stylesheet\" type=\"text/css\" href=\"schema/css/spec.css\" />");

	/* Style all Jekill components */
	$(".jtext, .jcopy").bind("mouseover", function () { $(this).addClass("jeditable"); });
	// $(".jcopy").bind("mouseover", function () { $(this).addClass("jeditable"); });
	$(".jdyn").bind("mouseover", function () { $(this).addClass("jdynamic"); });
	$(".jimg").bind("mouseover", function () { $(this).addClass("jimage"); });

	$(".jtext, .jcopy").bind("mouseout", function () { $(this).removeClass("jeditable"); });
	// $(".jcopy").bind("mouseout", function () { $(this).removeClass("jeditable"); });
	$(".jdyn").bind("mouseout", function () { $(this).removeClass("jdynamic"); });
	$(".jimg").bind("mouseout", function () { $(this).removeClass("jimage"); });

	// $(".jcanvas").each(activateJCanvasComponent);

	$(".jdyn").attr("title", "This content is controlled by a database record.");

	/* Disable links. */
	disableLinks();
	
	/* Redirect links. */
	/*
	$("a").each(function () {
		var href = this.href;
		// this.href = "?page_URL=" + href;
	});
	*/

	$(".jtext").dblclick(function () { 
		var rel = $(this).attr("rel");
		loadJTextEditor(rel);
	});

	$(".jcopy").dblclick(function () { 
		var rel = $(this).attr("rel");
		loadJCopyEditor(rel);
	});

	$(".jimg").dblclick(function () {
		var rel = $(this).attr("rel");
		loadJImageEditor(rel);
	});

	$(".jdyn").dblclick(function () {
		var rel = $(this).attr("rel");
		loadJDynamicRecord(rel);
	});

	tinyMCE.init({ 
		mode : "textareas",
		theme: "advanced",
		plugins: "paste,fullscreen",
		theme_advanced_buttons1 : "separator,bold,italic,underline,separator,fontselect,fontsizeselect,forecolor,backcolor",
		theme_advanced_buttons2 : "pasteword,bullist,numlist,separator,outdent,indent,separator,undo,redo,separator,code,fullscreen",
		theme_advanced_buttons3 : "link,unlink,hr,removeformat,visualaid,separator,sub,sup,separator,charmap",
		theme_advanced_resizing : true,
		content_css : "/screen.css"
	});

	var controls = "<input type=\"button\" value=\"Save\" id=\"jekill_save\" onClick=\"saveJekill('" + contentSuffix + "')\"> <input type=\"button\" value=\"Cancel\" onClick=\"window.location = '" + returnURL + "'\"><div id=\"jekill_message\"></div>";

	var x = ""; // Center
	var y = "";
	var titlebar = true;
	var screen = false
	var windowControls = false;
	openClientWindow("jekill_control", controls, 150, 200, pageName, x, y, titlebar, screen, windowControls); 
}

var jVALUES = new Array();
var openEditors = new Array();

function activateJCanvasComponent () {
/*
	var id = $(this).attr("id");
	// Initialize spec functionality.
	//$(this).bind("click", function (event) { loadControlPanel(event, id); });
	editExisting(id);
	$(this).children().each(activateJCanvasComponent);
*/
}

function doNothing () { }

function disableLinks () {
	$("a:not(.jcopy a)").each(function () {
		var href = $(this).attr("href");
		var target = $(this).attr("target");
		$(this).attr("href-bak", href);
		$(this).attr("target-bak", target);
		$(this).attr("href", "javascript: doNothing()");
		$(this).attr("target", "");
	});
	// $("a").onclick = doNothing;
}

function enableLinks () {
	// $("a").unbind("onclick", doNothing);
	$("a:not(.jcopy a)").each(function () {
		var href = $(this).attr("href-bak");
		var target = $(this).attr("target-bak");
		$(this).attr("href", href);
		$(this).attr("target", target);
		$(this).removeAttr("href-bak", "");
		$(this).removeAttr("target-bak", "");
	});	
}

function setText (id) {
	
}

function setHTML (id, html) {
	$("[rel='" + id + "']").html(html);
}

function closeCopyEditor (id, html) {
	var editorID = id + "_editor";
	var previousValueID = id.replace(/:/g, "_") + "_previous";
	var html = $("#" + previousValueID).val();
	tinyMCE.execCommand("mceRemoveControl", false, editorID);
	setHTML(id, html);
	delete openEditors[id];
	openEditors.length--;
// console.log(id + " -> " + html);
}

function closeTextEditor (id, value) {
	setHTML(id, value)
	delete openEditors[id];
	openEditors.length--;
}

function saveCopyValue (id) {
	var editorID = id + "_editor";
	var html = tinyMCE.get(editorID).getContent();
	tinyMCE.execCommand("mceRemoveControl", false, editorID);
	delete openEditors[id];
	openEditors.length--;
	saveValue(id, html);
}

function saveValue (id, html) {
	setHTML(id, html);
	jVALUES[id] = html;
	$("[rel='" + id + "']").removeClass("jtext").removeClass("jeditable");
}

function loadJDynamicRecord (jRecordID) {
	var relParts = jRecordID.split(/:/);
	var tableName = relParts[0];
	var entityID = relParts[1];
	openSecureInnerWindow(tableName, "inline_control_panel.php?func=form&table=" + tableName + "&id=" + entityID, 800, 700, "Edit Dynamic Record", "", "", "", 1);
}

function loadJTextEditorWindow (jTextID) {
	var editorHTML = "<input type=\"text\" onKeyUp=\"setHTML('" + jTextID +"', this.value)\" value=\"" + $("#" + jTextID).html() + "\" /><br /><input type=\"button\" onClick=\"\" value=\"Cancel\" /> <input type=\"button\" onClick=\"\" value=\"Submit\" />";
	openClientWindow(jTextID + "_editor_window", editorHTML, 300, 200, "Edit Jekill Text");
}

function loadJTextEditor (jTextID) {
	var value = $("[rel='" + jTextID + "']").html();
	var previousValueID = jTextID.replace(/:/g, "_") + "_previous";
	var editorID = jTextID.replace(/:/g, "_") + "_editor";
	value = value.replace(/"/g, "&quot;");
	openEditors[jTextID];
	openEditors.length++;

	var editorHTML = "<input type=\"hidden\" id=\"" + previousValueID + "\" value=\"" + value + "\"><input type=\"text\" id=\"" + editorID + "\" value=\"" + value + "\" /> <input type=\"button\"  onClick=\"closeTextEditor('" + jTextID + "', $('#" + previousValueID + "').val())\" / value=\"Cancel\"> <input type=\"button\" onClick=\"closeTextEditor('" + jTextID + "', $('#" + editorID + "').val())\" value=\"Save\" />";
	$("[rel='" + jTextID + "']").html(editorHTML);
}

function loadJCopyEditor (jCopyID) {
	var editorID = jCopyID + "_editor";
	var value = $("[rel='" + jCopyID + "']").html();
	var previousValueID = jCopyID.replace(/:/g, "_") + "_previous";
	value = value.replace(/"/g, "&quot;");
	openEditors[jCopyID];
	openEditors.length++;

	var editorHTML = "<input type=\"hidden\" id=\"" + previousValueID + "\" value=\"" + value + "\"><textarea id=\"" + editorID + "\" class=\"jcopy_editor\">" + value + "</textarea><input type=\"button\" value=\"Cancel\" onClick=\"closeCopyEditor('" + jCopyID + "')\" /> <input type=\"button\" value=\"Save\" onClick=\"saveCopyValue('" + jCopyID + "')\" />";
	$("[rel='" + jCopyID + "']").html(editorHTML);

	if (!tinyMCE.get(editorID))
		tinyMCE.execCommand("mceAddControl", false, editorID);
}

function loadJImageEditor (jImageID) {
	openInnerWindow("image_selector", "browse_images.php", 500, 400, "Image Selector", "element_name=" + jImageID);
}

function selectImage (jImageID) {
	var selectedImages = getSelected();
//alert(selectedImages.length);
//	alert(jImageID + ", " + selectedImages);
	for (var i in selectedImages){
		var source = $("#" + i + "_name").val();
		
		$("[rel='" + jImageID + "']").attr("src", source);
		break;
	}
	closeInnerWindow("image_selector");
}

function saveJekill (contentPagePath) {
	var valueMap = new Array();

	if (openEditors.length > 0) {
		alert("Please close all open editors prior to saving.");
		return;
	}

	if (typeof(contentPagePath) == "undefined")
		contentPagePath = "";

	// Disable Save Button
	$("#jekill_save").bind("onclick", doNothing).css("opacity", 0.25);

	// Re-enable links so 'doNothing()' isn't saved...
	enableLinks();

	// $(".jcanvas .control_divide").children().unwrap();
/*
	$(".jcanvas").each(function () {
		var id = $(this).attr("id");
		var rel = $(this).attr("rel");
		var relParts = rel.split(":");
		var contentBank = relParts[0];
		var contentID = relParts[1];
		if (typeof(valueMap[contentBank]) == "undefined")
			valueMap[contentBank] = "jContentPage=" + contentBank + "&jContentPath=" + contentPagePath;

		var htmlContent = "";
		$(this).children().each(function () {
			// The 'Child' is actually the child's control div
			var childID = $(this).children(":first").attr("id");
			// var childID = $(this).attr("id");
console.log(childID);
			var element = elementManager.getElement(childID);
			var elementHTML = element.render();
			htmlContent += elementHTML;
console.log(elementHTML);
		});
		valueMap[contentBank] += "&" + contentID + "=" + escape(htmlContent);
	});
*/

	$(".jtext, .jcopy, .jimg").each(function () {
		var rel = $(this).attr("rel");
		if (typeof(rel) != "undefined") {	// No properly defiend 'rel' tag
			var relParts = rel.split(/:/);
			var contentBank = relParts[0];
			var contentID = relParts[1];
			if (typeof(valueMap[contentBank]) == "undefined")
				valueMap[contentBank] = "jContentPage=" + contentBank + "&jContentPath=" + contentPagePath;
			var jContent = null;
			if ($(this).hasClass("jimg")) {
				jContent = $(this).attr("src");
			}
			else jContent = escape($(this).html());
			valueMap[contentBank] += "&" + contentID + "=" + jContent;
		}
	});


	for (var contentBank in valueMap) {
		$.ajax({ type: "POST", url: "save_jekill.php", data: valueMap[contentBank], success: function (response) {
			$("#jekill_message").append("<div id=\"" + contentBank + "_response\">" + response + "</div>");
			setTimeout(function () { $("#" + contentBank + "_response").remove() }, 5000);
		}});
	}
	// Disable links
	disableLinks();

	// Re-enable Save Button
	$("#jekill_save").bind("onclick", saveJekill).css("opacity", 1.0);
}

function cancelJekill () {
	window.location = window.location;
}