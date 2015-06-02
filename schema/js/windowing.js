
function openInnerWindow (name, url, width, height, title, params, x, y, titlebar, screen) {
  var bookmark = getElement("_BOOKMARK_");
  if (!bookmark) {
    var window = new InnerWindow(name, url, width, height, title, params, x, y, titlebar);
    if (screen)
      openScreen(name);
    window.open();
  }
  else bookmarkWindow(name, url, width, height, title, params, x, y, titlebar, screen);
}

function isInnerWindow (name) {
	return windowManager.isWindow(name);
}

function openSecureInnerWindow (name, url, width, height, title, params, x, y, titlebar, screen) {
  var window = new SecureInnerWindow(name, url, width, height, title, params, x, y, titlebar);
  if (screen)
    openScreen(name);
  window.open();
}

function openClientWindow (name, contents, width, height, title, x, y, titlebar, screen, controls) {
  var window = new ClientInnerWindow(name, contents, width, height, title, x, y, titlebar, controls);
  if (screen)
    openScreen(name);
  window.open();

}

function openScreen (screenName) {
  var windowWidth = getWindowWidth();
  var windowHeight = getWindowHeight();
  var pageHeight = getPageHeight();
  if (pageHeight > windowHeight)
    windowHeight = pageHeight;

  var screen = document.createElement("DIV");
  var opacity = 0.85;

  var opacityStyle = "opacity: " + opacity;

  if (IE)
    opacityStyle = "filter: alpha(opacity=" + opacity*100 + ")";

  screen.setAttribute("onClick", "closeInnerWindow('" + screenName + "')");
  screen.setAttribute("id", screenName + "_screen");
  // setStyle(screen, "position: absolute; left: 0px; top: 0px; width: " + windowWidth + "px; height: " + windowHeight + "px; background-color: #FFFFFF; " + opacityStyle);
  setStyle(screen, "position: absolute; left: 0px; top: 0px; width: " + windowWidth + "px; height: 100%; background-color: #FFFFFF; " + opacityStyle);

  var body = document.body;
  body.appendChild(screen);
}

function closeScreen (name) {
  var screen = getElement(name + "_screen");

  if (screen) {
    var body = document.body;
    body.removeChild(screen);
  }
}

function closeInnerWindow (name) {
  $("#" + name).fadeOut("slow", function () { windowManager.closeWindow(name) });
  closeScreen(name);
}

function closeClientInnerWindow (name) {
  closeInnerWindow(name);
}

function closeSecureInnerWindow (name) {
  closeInnerWindow(name);
}

function openToolTip (html, width, height) {
  var mousePos = getMouseOffset(event.srcElement, event); // getPosition(element);
  var x = mousePos.x;
  var y = mousePos.y;
  openClientWindow("tool_tip", html, width, height, "", x, y, 0);
}

function closeToolTip () {
  closeInnerWindow("tool_tip");
}

WindowManager = function () {
  this.windows = new Array();
}

WindowManager.prototype = {

  addWindow:function (window) {
    this.windows[window.name] = window;
  },

  removeWindow:function (windowName) {
    this.windows[windowName] = null;
  },

  getWindow:function (windowName) {
    return this.windows[windowName];
  },

  getMaximumZIndex:function () {
    var maximumIndex = 1;
    for (var i=0; i<this.windows.length; i++) {
      var windowIndex = this.windows[i].getZIndex();
      if (windowIndex > maximumIndex)
        maximumIndex = windowIndex;
    }
    return maximumIndex;
  },

  isWindow:function (name) {
    return (typeof(this.windows[name]) != "undefined");
  },

  closeWindow:function (windowName) {
    var window = this.windows[windowName];
    if (window)
      window.close();
  },

  closeAllWindows:function () {

  },

  minimizeAllWindows:function () {

  },

  minimizeWindow:function (windowName) {
    var window = this.windows[windowName];
    if (window)
      window.minimize();
  },

  maximizeWindow:function (windowName) {
    var window = this.windows[windowName];
    if (window)
      window.maxmize();
  },

  normalizeWindow:function (windowName) {
    var window = this.windows[windowName];
    window.normalize();
  }

}

var windowManager = new WindowManager();

InnerWindow = function (name, url, width, height, title, params, x, y, titlebar, controls) {
  this.name = name;
  this.url = url;
  this.width = defaultValue(width, 500);
  this.height = defaultValue(height, 350);
  this.title = defaultValue(title, "");
  this.x = defaultValue(x, "");
  this.y = defaultValue(y, "");
  this.titlebar = defaultValue(titlebar, true);
  this.controls = defaultValue(controls, true);
  this.zIndex = 999999;
  this.params = defaultValue(params, "");
  this.contentRetriever = null;
}

InnerWindow.prototype = {

  bringToFront:function () {
    var maxIndex = windowManager.getMaximumZIndex();
    setZIndex(maxIndex +1);
  },

  getZIndex:function () {
    return this.zIndex;
  },

  openLoadingMessage:function () {
    var loadingDiv = getElement("loading_messge");
    if (!loadingDiv)
      loadingDiv = createCenteredDivide(this.name + "_loading_message", 100, 20, "Loading...");

    // setStyle(loadingDiv, "background-color: #FFFFFF; padding: 4px;");
    loadingStyle = getStyleObject(this.name + "_loading_message");
    loadingStyle.padding = "4px";
    loadingStyle.backgroundColor = "#FFFFFF";
  },

  getTitlebar:function () {
    var titlebar = document.createElement("DIV");
    titlebar.setAttribute("class", "titlebar");
    titlebar.setAttribute("id", this.name + "_titlebar");
    // setStyle(titlebar, "height: 25px;");
    titlebar.setAttribute("onDblClick", "windowManager.maximizeWindow('" + this.name + "')");
    setStyle(titlebar, /* "background-color: #CCCCCC; */ "padding: 5px; cursor: move;");
    var controls = "<A href=\"javascript: closeInnerWindow('" + this.name + "')\" style=\"float: right;\">close [ x ]</A>&nbsp;<!-- <IMG src=\"images/minimize.gif\" id=\"" + this.name + "_minimize_button\" onClick=\"windowManager.minimizeWindow('" + this.name + "')\"> -->&nbsp;";
    var titlebarHTML = "<B>" + this.title + "</B>";
    if (this.controls)
      titlebarHTML = controls + titlebarHTML;
    titlebar.innerHTML = titlebarHTML;

    var resizeHandle = document.createElement("DIV");
    resizeHandle.setAttribute("id", this.name + "_resize_handle");
    resizeHandle.innerHTML = "<IMG src=\"images/resize.gif\">";
    makeResizeableHandle(resizeHandle);

    resizeHandle.style.position = "absolute"; // "relative";
    resizeHandle.style.left = (Number(this.width) - 5) + "px";
    resizeHandle.style.top = (Number(this.height) - 5) + "px";

    return titlebar;
  },

  loadContents:function () {
    this.contentRetriever = new HttpRequest(this.url, this.loadContentsCallback);
    this.contentRetriever.caller = this;
    this.contentRetriever.post(this.params);
  },

  loadContentsCallback:function () {
    var contentsContainer = getElement(this.name + "_contents");
    var content = this.contentRetriever.getResponse();
    // console.log(content);
    if (!contentsContainer)
      return;

    contentsContainer.innerHTML = content;
  },

  open:function () {
    // this.openLoadingMessage();
    windowManager.addWindow(this);
    /* 'this' in this context will be the HttpRequest, as this function is a callback. */
    var innerWindow = getElement(this.name);
    var loadingMessage = getElement(this.name + "_loading_message");
    var body = document.body;
    if (innerWindow)
      body.removeChild(innerWindow);

    var contentsContainer = document.createElement("DIV");
    contentsContainer.setAttribute("id", this.name + "_contents");
    setStyle(contentsContainer, "padding: 5px;");
    contentsContainer.innerHTML = "<DIV align=\"center\"><B>Loading...</B></DIV>";

    var windowFrame = null;

    if (this.x != "" || this.y != "")
      windowFrame = createDivide(this.name + "_frame", this.width, this.height, this.x, this.y, "", "inner_window", false);
    else windowFrame = createCenteredDivide(this.name + "_frame", this.width, this.height, "", "inner_window", false);

    if (loadingMessage)
      body.removeChild(loadingMessage);

    if (this.titlebar) {
      var titlebarDiv = this.getTitlebar();
      makeDraggableHandle(titlebarDiv);
      // windowFrame.appendChild(resizeHandle);
      windowFrame.appendChild(titlebarDiv);
    }
    windowFrame.appendChild(contentsContainer);
    var windowDivide = document.createElement("DIV");

/*
    var shadow = document.createElement("DIV");
    // difficult if height is calculated and not directly specified
    setStyle(shadow, "background-color: #000000; opacity: 0.25; width: " + this.width + "px; height: " +  + "px; position: relative;");
*/

    windowDivide.setAttribute("id", this.name);
    windowDivide.appendChild(windowFrame);
    // windowDivide.appendChild(shadow);

    document.body.appendChild(windowDivide);

    var windowStyle = getStyleObject(this.name + "_frame");
    windowStyle.backgroundColor = "#FFFFFF";

    this.loadContents();
  },

  minimize:function () {
    var minimizeButton = getElement(this.name + "_minimize_button");
    var windowStyle = getStyleObject(this.name);
    var left = stripTrailing(windowStyle.left, 2);
    var top = stripTrailing(windowStyle.top, 2);
    var width = stripTrailing(windowStyle.width, 2);
    var height = stripTrailing(windowStyle.height, 2);
    minimizeButton.setAttribute("src", "images/maximize.gif");
    minimizeButton.setAttribute("onClick", "windowManager.normalizeWindow('" + this.name + "')");
    var leftMin = 0;
    var topMin = 0;
    var minWidth = 200;
    var minHeight = 20;
    windowStyle.left = leftMin + "px";
    windowStyle.top = topMin + "px";
    windowStyle.width = minWidth + "px";
    windowStyle.height = minHeight + "px";
  },

  close:function (name) {
    if (this.titlebar) {
      var innerWindowTitlebar = getElement(this.name + "_titlebar");
      innerWindowTitlebar.innerHTML = "<B>Closing...</B>";
    }
    var innerWindowDiv = getElement(this.name);
    var body = document.body;
/*
    var parent = this.parentNode;
    while (parent.getAttribute("class") != "inner_window")
      parent = parent.parentNode;

    var innerWindowDiv = parent;
*/
    if (innerWindowDiv)
      body.removeChild(innerWindowDiv);

    windowManager.removeWindow(this.name);
  },

  maximize:function () {
    var titlebar = getElement(this.name + "_titlebar");
    var resizeHandle = getStyleObject(this.name + "_resize_handle");
    var windowStyle = getStyleObject(this.name);
    var left = stripTrailing(windowStyle.left, 2);
    var top = stripTrailing(windowStyle.top, 2);
    var width = stripTrailing(windowStyle.width, 2);
    var height = stripTrailing(windowStyle.height, 2);
    var windowWidth = getWindowWidth();
    var windowHeight = getWindowHeight();

    titlebar.setAttribute("onDblClick", "windowManager.normalizeWindow('" + this.name + "')");
    windowStyle.left = 0 + "px";
    windowStyle.top = 0 + "px";
    windowStyle.width = windowWidth + "px";
    windowStyle.height = windowHeight + "px";
    resizeHandle.left = (Number(windowWidth)/* -15 */) + "px";
    resizeHandle.top = (Number(windowHeight)/* -15 */) + "px";
  },

  normalize:function (width, height, x, y) {
    var titlebar = getElement(this.name + "_titlebar");
    var resizeHandle = getStyleObject(this.name + "_resize_handle");
    var windowStyle = getStyleObject(this.name);
    var normalizeButton = getElement(this.name + "_minimize_button");
    normalizeButton.setAttribute("src", "images/minimize.gif");
    normalizeButton.setAttribute("onClick", "windowManager.minimizeWindow('" + this.name + "')");
    windowStyle.left = this.x + "px";
    windowStyle.top = this.y + "px";
    windowStyle.width = this.width + "px";
    windowStyle.height = this.height + "px";
    titlebar.setAttribute("onDblClick", "windowManager.maximizeWindow('" + this.name + "')");
    resizeHandle.left = this.width + "px";
    resizeHandle.top = this.height + "px";
  },

  resize:function () {
    var innerWindowStyle = getStyleObject(this.name);
    this.width = width;
    this.height = height;
    innerWindowStyle.width = width + "px";
    innerWindowStyle.height = height + "px";
  },

  setZIndex:function (zIndex) {
    var style = getStyleObject(this.name);
    style.zIndex = zIndex;
    this.zIndex = zIndex;
  }

}

SecureInnerWindow = function (name, url, width, height, title, params, x, y, titlebar) {
  InnerWindow.call(this);
  this.name = name;
  this.url = url;
  this.width = defaultValue(width, 500);
  this.height = defaultValue(height, 350);
  this.title = defaultValue(title, "");
  this.x = defaultValue(x, "");
  this.y = defaultValue(y, "");
  this.titlebar = defaultValue(titlebar, true);
  this.zIndex = 999999;
  this.params = defaultValue(params, "");
}

SecureInnerWindow.prototype = new InnerWindow;
SecureInnerWindow.constructor = SecureInnerWindow;

SecureInnerWindow.prototype.loadContents = function () {
  if (this.titlebar) {
    var titlebar = getElement(this.name + "_titlebar");
    var secureIndicator = document.createElement("DIV");
    setStyle(secureIndicator, "margin-top: 3px; background: url('/schema/images/caution.jpg');");
    var lock = document.createElement("IMG");
    lock.setAttribute("id", this.name + "_lock_icon");
    lock.setAttribute("src", "/schema/images/lock_small.jpg");
    // addMouseListener(this.name + "_lock_icon", assureSecure);

    lock.onMouseOver = assureSecure;
    lock.onMouseOut = unassureSecure;

    setStyle(lock, "margin: 3px; border: #000000 solid 1px; cursor: help;");
    secureIndicator.appendChild(lock);
    titlebar.appendChild(secureIndicator);
  }
  var contentsContainer = getElement(this.name + "_contents");
  var contentFrame = document.createElement("IFRAME");
  var url = this.url;
  if (this.params != "") {
    url += "?" + this.params;
  }
  frameHeight = Number(this.height) - 75;
  frameWidth = Number(this.width) - 10;
  setStyle(contentFrame, "width: " + frameWidth + "px; border: #000000 solid 0px; height: " + frameHeight + "px;");
  contentFrame.setAttribute("src", url);
  contentsContainer.innerHTML = "";
  contentsContainer.appendChild(contentFrame);
}

function assureSecure (event) {
console.log(event); return;
  var verifyDotNet = "<!-- (c) 2006. Authorize.Net is a registered trademark of Lightbridge, Inc. --> <div class=\"AuthorizeNetSeal\"> <script type=\"text/javascript\" language=\"javascript\">var ANS_customer_id=\"35dd6b9d-570e-434d-b639-61348637a87b\";</script> <script type=\"text/javascript\" language=\"javascript\" src=\"//VERIFY.AUTHORIZE.NET/anetseal/seal.js\" ></script> <a href=\"http://www.authorize.net/\" id=\"AuthorizeNetText\" target=\"_blank\">Payment Processing</a> </div>";
  openToolTip(event, "<DIV style=\"background-color: #FFFF66;\"><I>This is a Secure Subwindow.</I></DIV>" + verifyDotNet, 200, 20);
}

function unassureSecure () {
  closeToolTip();
}

ClientInnerWindow = function (name, content, width, height, title, x, y, titlebar, controls) {
  InnerWindow.call(this);
  this.name = name;
  this.content = content;
  this.width = defaultValue(width, 500);
  this.height = defaultValue(height, 350);
this.height = height;
  this.title = defaultValue(title, "");
  this.x = defaultValue(x, "");
  this.y = defaultValue(y, "");
  this.titlebar = defaultValue(titlebar, true);
  this.controls = defaultValue(controls, true);
  this.zIndex = 999999;
  this.params = "";
}

ClientInnerWindow.prototype = new InnerWindow;
ClientInnerWindow.constructor = ClientInnerWindow;

ClientInnerWindow.prototype.loadContents = function () {
  var contentsContainer = getElement(this.name + "_contents");
  if (contentsContainer)
    contentsContainer.innerHTML = this.content;
}