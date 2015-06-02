/*
var READY_STATE_UNINITIALIZED = 0;
var READY_STATE_LOADING = 1;
var READY_STATE_LOADED = 2;
var READY_STATE_INTERACTIVE = 3;
var READY_STATE_COMPLETE = 4;
*/

HttpRequest = function (url, onload, onerror) {
  this.url = url;
  this.caller = this;
  this.onload = (onload) ? onload : this.defaultLoad;
  this.onerror = (onerror) ? onerror : this.defaultError;
  this.request = this.initRequest();
  this.parameters = new Array();
  this.READY_STATE_UNINITIALIZED = 0;
  this.READY_STATE_LOADING = 1;
  this.READY_STATE_LOADED = 2;
  this.READY_STATE_INTERACTIVE = 3;
  this.READY_STATE_COMPLETE = 4;
}

HttpRequest.prototype = {
  initRequest:function () {
    if (window.XMLHttpRequest)        // Mozilla, etc.
      request = new XMLHttpRequest();
    else if (window.ActiveXObject)
      request = new ActiveXObject("Microsoft.XMLHTTP");
    return request;
  },

  getParameter:function (name) {
    return this.parameters[name];
  },

  setParameter:function (name, value) {
    this.parameters[name] = value;
  },

  getResponse:function () {
    return this.request.responseText;
  },

  get:function (params) {
    this.sendRequest(params, "GET");
  },

  post:function (params) {
    this.sendRequest(params, "POST");
  },

  put:function (params) {
    this.sendRequest(params, "PUT");
  },

  xmlRequest:function (xml) {
    this.sendRequest(xml, "POST", "text/xml", true, { Man:"POST https://api.sandbox.ebay.com/ws/api.dll HTTP/1.1", MessageType:"CALL" });
  },

  sendRequest:function (params, HttpMethod, contentType, secure, headers) {
    if (!HttpMethod)
      HttpMethod = "GET";
    
    if (!contentType && HttpMethod == "POST")
      contentType = "application/x-www-form-urlencoded";

    if (this.request) {
      try {
        try {
          if (secure && netscape && netscape.security.PrivilegeManager.enablePrivilege)
            netscape.security.PrivilegeManager.enablePrivilege('UniversalBrowserRead');
        }
        catch (err) { /* alert(err); */ }
        this.request.open(HttpMethod, this.url, true);
        if (contentType)
          this.request.setRequestHeader("Content-Type", contentType);
 
        var loader = this;
        this.request.onreadystatechange = function () {
          loader.onReadyState.call(loader);
        }
        if (headers) {
          for (var h in headers) {
            this.request.setRequestHeader(h, headers[h]);
          }
        }
        this.request.send(params);
      }
      catch (exception) {
        this.onerror.call(this);
      }
    }
  },

  onReadyState:function () {
    if (this.request.readyState == this.READY_STATE_COMPLETE) {
      var httpStatus = this.request.status;
      if (httpStatus == 200 || httpStatus == 0)
        this.onload.call(this.caller);
      else {
        this.onerror.call(this.caller);
      }
    }
  },

  defaultLoad:function () { // Do nothing.
  },

  defaultError:function () {
    alert("Error fetching data from " + this.url + ".");
  }
};






