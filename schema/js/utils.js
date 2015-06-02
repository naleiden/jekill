
var IE = (document.all) ? true : false;
var browser = navigator.appName;

function removeAndPrepend (carrierID, callback) {
	var removed = $("#" + carrierID).children(":last").remove();
	removed.prependTo("#" + carrierID);

	if (typeof(callback) == "function")
		callback.call(this);
}

function removeAndAppend (carrierID, callback, opacity, width) {
	if (typeof(opacity) == "undefined")
		opacity = 1.0;

	if (typeof(width) == "undefined")
		width = "";

	var removed = $("#" + carrierID).children(":first").remove();
	removed.appendTo("#" + carrierID).css("width", width).css("visibility", "visible").css("opacity", opacity);

	if (typeof(callback) == "function")
		callback.call(this);
}


function redirect (URL) {
	window.location = URL;
}

var submenuCue = new Array();

function openSubmenu (menuID, style) {
	cancelSubmenuCloseCue(menuID);

	if (style == "slide")
		$("#" + menuID).slideDown("slow")
	else if (style == "fade")
		$("#" + menuID).fadeIn("slow");
	else $("#" + menuID).css("visibility", "visible");
}

function cueOpenSubmenu (menuID, style, timeout) {
	cueSubmenuOperation(menuID, function () { openSubmenu(menuID, style); }, timeout);
}

function cancelSubmenuCue (menuID) {
	if (!menuID)
		menuID = "submenu";

	if (submenuCue)
		clearTimeout(submenuCue[menuID]);
}

function cancelSubmenuCloseCue (menuID) {
	cancelSubmenuCue(menuID);
}

function cueCloseSubmenu (menuID, style, timeout) {
	cueSubmenuOperation(menuID, function () { closeSubmenu(menuID, style); }, timeout);
}

function cueSubmenuOperation (menuID, callback, timeout) {
	clearTimeout(submenuCue[menuID]);

	if (!menuID)
		menuID = "submenu";

	if (!timeout)
		timeout = 100;

	submenuCue[menuID] = setTimeout(callback, timeout);
}

function closeSubmenu (menuID, style) {
	if (!menuID)
		menuID = "submenu";

	if (style == "slide")
		$("#" + menuID).slideUp("slow");
	else if (style == "fade")
		$("#" + menuID).fadeOut("slow");
	else $("#" + menuID).css("visibility", "hidden");
}

function openTooltip (mouseEvent, content, tooltipID) {
	if (!mouseEvent) mouseEvent = window.event;
	if (!tooltipID)
		tooltipID = "tooltip";

	var x = mouseEvent.clientX;
	var y = mouseEvent.clientY + 15;
	$("body").append("<DIV class='tooltip hidden' id='" + tooltipID + "'>" + content + "</DIV>");
	$("#" + tooltipID).css("left", x + "px").css("top", y + "px");
	$("#" + tooltipID).fadeIn("slow");
}

function isAlphaNumeric (str) {
	return str.match(/^[_a-zA-Z0-9]+$/);
}

function isChecked (value) {
	return value;
}

function isColor (str) {
	return str.match(/[abcdef1-9]{6}/i);
}

function isValidTelephone (number) {
	var strNumber = new String(number);
	number = strNumber.replace(/[^0-9]/g, '');
	return (number.length >= 7);
}

function isValidCreditCard (number) {
	number = stripNonNumeric(number);

	if (number.length < 12)
		return false;

	var offset = 0;	// Even # digits: MasterCard, Visa
	if (number.length%2) {	// Odd: American Express, etc.
		offset = 1;
	}

	var sum = 0;
	var digit = 0;
	for (i=0; i<number.length; i++) {
		digit = parseInt(number.substring(i, i+1));
		if (i%2 == offset) {
			digit *= 2;
		}
		if (digit > 9) {
			digit = new String(digit);
			digit = parseInt(digit.substring(0, 1)) + parseInt(digit.substring(1));
		}
		sum += Number(digit);
	}
	sum = Number(sum);
	var remainder = sum%10;

	return (remainder == 0);
}

function isValidTelephone (num, validDigitCount) {
	if (typeof(validDigitCount) == "undefined")
		validDigitCount = 10;
	else validDigitCount = parseInt(validDigitCount);

	var stripped = stripNonNumeric(num);
	return (stripped.length == validDigitCount);
}

function stripNonNumeric (str) {
	var testString = new String(str);
	var stripped = str.replace(/[^0-9]/g, ''); 
	return "" + stripped; 
}


function closeTooltip (tooltipID) {
	if (!tooltipID)
		tooltipID = "tooltip";

	$("#" + tooltipID).fadeOut("slow", function () { $("body").remove("#" + tooltipID) });
}

function setClass (element, newClass) {
  if (IE)
    element.setAttribute("className", newClass);
  else element.setAttribute("class", newClass);
}

function load (divName, page, params, initialization) {
  var contentDiv = getElement(divName);
  httpRequest = new HttpRequest(page, loadCallback);
  if (typeof(initialization) != "undefined")
    httpRequest.setParameter("initialization", initialization);
  httpRequest.setParameter("div", divName);
  contentDiv.innerHTML = "Loading...";
  // alert(params);
  httpRequest.post(params);
}

function defaultLoadContent (page, params, initialization) {
  var bookmark = getElement("_BOOKMARK_");
  if (bookmark)
    bookmarkPage(page, params, initialization);
  else load("content", page, params, initialization);
}

function loadBookmark (page, params, initialization) {
  // console.log("Loading " + page);
  if (page != "")
    load("content", page, params);
  if (initialization != "")
  invokeInitializer(initialization);
}

function loadBookmarkWindow (name, url, width, height, title, params, x, y, titlebar, screen) {
  var window = new InnerWindow(name, url, width, height, title, params, x, y, titlebar, screen);
  //if (screen)
  //  openScreen(name);

  window.open();
}

function bookmarkPage (page, params, initialization) {
  var bookmarkParams = "bookmark_page=" + page + "&" + params + "&initialization=" + initialization;
  // console.log(bookmark_url);
  setBookmark(bookmarkParams);
}

function bookmarkWindow (windowName, page, windowWidth, windowHeight, windowTitle, params, x, y, titlebar, screen) {
/*
  windowHeight = defaultValue(windowHeight, "");
  windowTitle = defaultValue(windowTitle, "");
  params = defaultValue(params, "");
  x = defaultValue(x, "");
  y = defaultValue(y, "");
  titlebar = defaultValue(titlebar, 1);
  screen = defaultValue("screen", 0);
*/

  var bookmarkParams = "bookmark_page=" + page + "&window_name=" + windowName + "&window_width=" + windowWidth + "&window_height=" + windowHeight + "&window_x=" + x + "&window_y=" + y + "&window_titlebar=" + titlebar + "&window_screen=" + screen + "&" + params;
// console.log(bookmarkParams);
  setBookmark(bookmarkParams);
}

function setBookmark (params) {
  var bookmark = getElement("_BOOKMARK_");
  var bookmarkURL = "bookmark.php?" + params;
  bookmark.src = bookmarkURL;
}

function loadCallback () {
  var divName = httpRequest.getParameter("div");
  if (!divName)
    divName = "content";

  var initializer = httpRequest.getParameter("initialization");
  var contentDiv = getElement(divName);

  var response = httpRequest.getResponse();
  contentDiv.innerHTML = response;
  invokeInitializer(initializer);
}

function invokeInitializer (initializer) {
  var initializerType = typeof(initializer);
  if (initializerType == "function") {
    initializer.call(this); // If we are able to pass a function...
  }
  else if (initializerType != "undefined") {
    setTimeout(initializer, 10); // If we must pass initialization as a string.
  }
}

function setStyle (element, style) {
  if (IE && browser != "Opera")
    element.style.setAttribute('cssText', style, 0);
  else element.setAttribute("style", style);
}

function defaultValue (variable, defaultValue) {
  if (typeof(variable) == "undefined")
    return defaultValue;
  else return variable;
}

function removeAllChildren (element) {
  while (element.childNodes.length >= 1) {
    element.removeChild(element.firstChild);       
  } 
}

function prettyNumber (numb) {
  numb = "" + numb;
  var decimalIndex = numb.indexOf(".");
  var decimal = "";
  if (decimalIndex > 0) {
    var wholeDecimal = numb.split(".");
    numb = wholeDecimal[0];
    decimal = wholeDecimal[1];
  }

  var length = numb.length;
  var formatted = "";
  if (length <= 3)
    return numb;

  while (length > 3) {
    var lastThree = numb.substring(length-3, length);

    if (formatted.length > 0)
      formatted = "," + formatted;
    formatted = lastThree + formatted;
    numb = numb.substring(0, length-3);
    length = numb.length;
  }
  if (formatted != "") 
    formatted = "," + formatted;
  formatted = numb + formatted;

  if (decimal != "") {
    if (decimal.length == 1)
      decimal = decimal + "0";
    decimal = "." + decimal;
  }
  formatted = formatted + decimal;
  return formatted;
}

function getEpochTime () {
  
}

function log (message) {
  while (message.length > 100) {
    var first = message.substring(0, 100);
    var next = message.substring(100, message.length);
    console.log(first);
    message = next;
  }
  console.log(message);
}

function trim (str) {
  if (!str || typeof(str) == "undefined") {
	return "";
  }
  else if (typeof(str) == "number") {
	return "" + str;
  }
  return str.replace(/^\s+/g, '').replace(/\s+$/g, '');
}

function getTime () {
  var now = new Date();
  var hours = now.getHours();
  var minutes = now.getMinutes();
  var seconds = now.getSeconds();
  hours = formatHours(hours);
  // add a zero in front of numbers < 10
  minutes = checkTime(minutes);
  seconds = checkTime(seconds);
  return { hours: hours, minutes: minutes, seconds: seconds };
}

function isValidURL (url) {
	return true; // "(http|ftp|https)://([\w-]+\.)+(/[\w- ./?%&=]*)?");
}

function isValidPassword (passwordID) {
	if (!$("#confirm_" + passwordID).length)
		return trim($("#" + passwordID).val()) != "";
	else return ($("#" + passwordID).val() == $("#confirm_" + passwordID).val() && trim($("#" + passwordID).val()) != "")
}

function isValidDate (date) {
	if (trim(date) == "")
		return false;
	else {
		var dateParts = date.split("/");
		if (dateParts.length < 3)
			return false;
		if (!isNonZero(dateParts[0]) || !isNonZero(dateParts[1]) || !isNonZero(dateParts[2]))
			return false;
		else if (dateParts[2] < 1000)	// 2 digit year specified.
			return false;
	}
	return true;
}

function notEmpty (str) {
	return (trim(str) != "");
}

function isNonZero (num) {
	return Number(num) && Number(num) != 0;
}

function isNumber (num) {
	return Number(num);
}

function isValidName (name) {
    if (!name || typeof name != "string") {
        return false;
    } else {
        return (name.match(/[a-z\-\s']{2,}/i).length > 0);
    }
}

function isValidEmail (email) {
	if (!email || typeof email != "string") return false;
	return (email.match(/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b/i).length > 0);
/*
  if (email == "")
    return false;

  var atIndex = email.indexOf("@");
  var dotIndex = email.lastIndexOf(".");
  if ((atIndex > 0) && (dotIndex > 0) && (dotIndex > atIndex))
    return true;
  else return false;
*/
}

function formatHours (hours) {
  if (hours > 12)
    hours = hours - 12;
  return hours;
}

function checkTime (i) {
  if (i<10) 
    i = "0" + i;
  return i;
}

function getWindowWidth () {
  if (parseInt(navigator.appVersion)>3) {
    if (navigator.appName=="Netscape")
      return window.innerWidth;

    if (navigator.appName.indexOf("Microsoft")!=-1)
      return document.body.offsetWidth;
  }
}

function getPageHeight () {
  return document.body.offsetHeight;
}

function getWindowHeight () {
  if (parseInt(navigator.appVersion)>3) {
    if (navigator.appName=="Netscape")
      return window.innerHeight;
    if (navigator.appName.indexOf("Microsoft")!=-1)
      return document.body.offsetHeight;
  }
}

function getScrollOffset () {
  if (IE) {
    return { x:document.body.scrollLeft, y:document.body.scrollTop };
  }
  else {
    return { x:window.pageXOffset, y:window.pageYOffset };
  }
}

function getElement (name) {
  if (document.getElementById) {
    return document.getElementById(name);
  }
  else if (document.all) {
    return document.all[name];
  }
  else if (document.layers) {
    return document.layers[name];
  }
}

function getCookie (name) {
  var value = "; " + document.cookie;
  var parts = value.split("; " + name + "=");
  if (parts.length == 2)
    return parts.pop().split(";").shift();
  else return "";
}

function setCookie(name, value, expires, path, domain, secure) {
  var cookie = name + "=" + escape(value) +
      ((expires) ? "; expires=" + expires.toGMTString() : "") +
      ((path) ? "; path=" + path : "") +
      ((domain) ? "; domain=" + domain : "") +
      ((secure) ? "; secure" : "");
  document.cookie = cookie;
}

function loadPage (url) {
  window.location = url;
}

function validDate (date) {
  return true;
}

function setPadding (divID, padding) {
  var style = getStyleObject(divID);
  if (document.layers)
    style.padding = padding
  else style.padding = padding + "px";
}

function setDimensions (divID, widthDim, heightDim) {
/*
  var div = getElement(divID);
  div.width = width;
  div.height = height;
*/
  var style = getStyleObject(divID);
  if (document.layers) {
    style.width = widthDim;
    style.height = heightDim;
  }
  else {
    style.width = widthDim + "px";
    //style.height = heightDim = "px";
  }
}

function absoluteMove (toMove, x, y) {
  var style = getStyleObject(toMove);
  style.position = "absolute";
  if (document.layers) {
    style.left = x;
    style.top = y;
  }
  else {
    style.left = x + "px";
    style.top = y + "px";
  }
}

function stripTrailing (toStrip, numStripped) {
  toStrip = toStrip.substring(0, toStrip.length - numStripped);
  return toStrip;
}

function deltaMove (toMove, deltaX, deltaY) {
  var style = getStyleObject(toMove);
  style.position = "relative";
  var left = stripTrailing(style.left, 2);
  var top = stripTrailing(style.top, 2); 
  left = parseInt(left) + deltaX;
  top = parseInt(top) + deltaY;

  if (document.layers) {
    style.left = left;
    style.top = top;
  }
  else {
    style.left = left + "px";
    style.top = top + "px";  
  }
  
}

function collapse (toCollapse) {
  var the_obj = document.getElementById(toCollapse);
  the_obj.innerHTML = "";
  var the_style = getStyleObject(toCollapse);
  the_style.width = 0;
  the_style.height = 0;
}

function getStyleObject (objectId) {
  if (document.all && document.all(objectId)) {
    // MSIE 4 DOM
    return document.all(objectId).style;
  }
  else if (document.layers && document.layers[objectId]) {
    // NN 4 DOM.. note: this won't find nested layers
    return document.layers[objectId];
  } 
  else return getElement(objectId).style;
}

function openCenteredWindow (url, width, height, toolbar, scrollbars, resizable) {
//  var startX = screen.availWidth/2 - width/2;
//  var startY = screen.availHeight/2 - height/2;
  var startX = $(window).width()/2 - width/2;
  var startY = $(window).height()/2 - height/2;

  var parameters = "left=" + startX + ",top=" + startY + ",width=" + width + ",height=" + height + ",toolbar=" + toolbar + ",location=1,scrollbars=" + scrollbars + ",resizable=" + resizable;
  // alert(parameters);
  window.open(url, "", parameters);
}

function getFormContents (form) {
  var value = "";
  var params = "";
  for (i=0; i<form.elements.length; i++) {
    if (!form.elements[i] || (form.elements[i].name.length > 3 && form.elements[i].name.substring(0, 3) == "mce") || form.elements[i].name == "button")
      continue;

    var name = form.elements[i].name;
    var strlen = name.length;
    var ending = form.elements[i].name.substring(strlen-2, strlen);
    if (ending == "[]") {
      name = form.elements[i].name.substring(0, strlen-2);
      var valueArray = form.elements[i].value;
      
      for (var j=0; j<form.elements[i].options.length; j++) {
        if (form.elements[i].options[j].selected)
          params += name + "_" + form.elements[i].options[j].value + "=1&";
      }
      continue;
    }

    switch (form.elements[i].type) {
       case 'button':
       case 'submit':
         continue;
       case 'textarea': {
         var textarea = document.getElementById(form.elements[i].name);
         value = textarea.value;
         break;
       }
       case 'checkbox':
         value = (form.elements[i].checked) ? "on" : 0;
         break;
       case 'text':
       case 'hidden':
       case 'password':
       case 'select':
       case 'select-one':
         value = form.elements[name].value;
         // value = form.elements[i].value;
         break;
       default:
//         alert(form.elements[i].type);
         break;
    }
    if (!value)
      value = "";

    params += form.elements[i].name + "=" + escape(value) + "&";
  }
  return params;
}

function ensureDouble (inputName) {
  var input = getElement(inputName);
  var value = input.value;
  
  if (value == "")
    value = "0.00";
}


function centeredSubWindow (content_url, name, params, width, height, opacity) {
  var windowHeight = window.innerHeight;
  var windowWidth = window.innerWidth;
  var x = windowWidth/2 - width/2;
  var y = windowHeight/2 - height/2;
  // alert("(" + x + ", " + y + ") " + windowWidth + " x " + windowHeight + " | " + width + " x " + height);
  subWindow(content_url, name, params, x, y, width, height, opacity);
}

function subWindow (content_url, name, params, x, y, width, height, opacity) {
  if (getElement(name))  // subWindow already open
    return;
  var div = document.createElement("DIV");
  div.setAttribute("name", name);
  div.setAttribute("id", name);
  div.setAttribute("class", "draggable");
  div.setAttribute("border", 1);
  div.style.border = "#000000 solid 1px;";
  div.style.backgroundColor = "#FFFFFF";
  div.style.zIndex = 9;
  // div.innerHTML = "<IMG src=\"images/indicator.gif\">Loading...";
  if (!opacity)
    opacity = "0.90";

  div.style.opacity = opacity;

  var body = document.body;
  body.appendChild(div);
  if (width && height) {
    //alert("Dimensioning to " + width + " x " + height);
    setDimensions(name, width, height);
  }
  if (x && y) {
    // alert("Moving " + name + " to " + x + ", " + y);
    absoluteMove(name, x, y);
  }
  httpRequest = new HttpRequest(content_url, subWindowContentLoaded);
  if (params)
    params += "&";
  params += "subwindow_name=" + name;
  httpRequest.post(params);
}

function subWindowContentLoaded () {
  var response = httpRequest.getResponse();
  // alert(response);
  var response_parts = response.split("|");
  var subWindowName = response_parts[0];
  var subWindowTitleName = subWindowName + "_title"
  var subWindow = getElement(subWindowName);
  var title = document.createElement("DIV");
  var content = document.createElement("DIV");
  var closeButton = document.createElement("IMG");
  
  title.setAttribute("id", subWindowTitleName);
  content.setAttribute("id", subWindowName + "_content");
  closeButton.setAttribute("id", subWindowName + "_close");
  closeButton.setAttribute("src", "images/close.jpg");
  closeButton.setAttribute("onClick", "closeSubWindow('" + subWindowName + "')");
  closeButton.setAttribute("align", "right");
  
  if (!subWindow)
    alert(subWindowName + " not found.");

  subWindow.appendChild(title);
  subWindow.appendChild(content);
  title.appendChild(closeButton);
  setPadding(subWindowName, 5);

  content.innerHTML = response_parts[1];
}

function closeSubWindow (name) {
  var subWindow = getElement(name);
  var body = document.body;
  // alert("Attempting to close " + name);
  body.removeChild(subWindow);
}

function createDivide (divName, width, height, x, y, content, clazz) {

  height = defaultValue(height, "");
  if (height != "")
    height += "px";

  $("<DIV>").attr("id", divName).addClass(clazz).css({ position: "absolute", overflow: "hidden", left: x + "px", top: y + "px", width: width + "px", height: height, border: "solid #000000 1px" }).css("z-index", 900).html(content).appendTo("body");

  var divide = getElement(divName);

  return divide;
}

function createCenteredDivide (divName, width, height, content, clazz) {
  var windowWidth = $(window).width();
  var windowHeight = $(window).height();
  var scrollOffset = getScrollOffset();

  var left = windowWidth/2 - Number(width)/2;
  var top = 110;
  if (height > 0 || height != "")
    top = windowHeight/2 - Number(height)/2;

  var divide = createDivide(divName, width, height, left + scrollOffset.x, top + scrollOffset.y, content, clazz);
  return divide;
}