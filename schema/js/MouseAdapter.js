/***********************/
/*                     */
/* Mouse Functionality */
/*                     */
/***********************/

document.onmousemove = mouseMove;
document.onmouseup = mouseUp;

var topWindowIndex = 2;
var dragObject = null;
var dragResizeHandle = null;
var resizeTarget = null;
var mouseOffset = null;

var mouseListeners = new Array();

function addMouseListener (listenerName, callback) {
  mouseListeners[listenerName] = callback;
}

function removeMouseListener (listenerName) {
  mouseListeners[listenerName] = null;
}

function mouseCoords (event){
  if(event.pageX || event.pageY){
    return { x: event.pageX, y: event.pageY };
  }
  return {
    x: event.clientX + document.body.scrollLeft - document.body.clientLeft,
    y: event.clientY + document.body.scrollTop  - document.body.clientTop
  };
}

function getMouseOffset (target, event){
  event = event || window.event;

  var docPos = getPosition(target);
  var mousePos = mouseCoords(event);
  return { x:mousePos.x - docPos.x, y:mousePos.y - docPos.y };
}

function getAbsolutePosition (target) {
  var left = 0;
  var top  = 0;

  while (target.offsetParent) {
    left += target.offsetLeft;
    top  += target.offsetTop;
    target = target.offsetParent;
  }
  left += target.offsetLeft;
  top  += target.offsetTop;
  return { x: left, y: top };
}

function getPosition (target) {
  var left = 0; // stripTrailing(target.style.left, 2);
  var top  = 0; // stripTrailing(target.style.top, 2);

  if (target.offsetParent) {
    var parentPositioning = target.offsetParent.style.position;
    if (parentPositioning == "absolute" || parentPositioning == "relative") {
      return { x: target.offsetLeft, y: target.offsetTop };
    }
  }

  while (target.offsetParent) {
    var parentPositioning = target.offsetParent.style.position;
    if (parentPositioning == "relative" || parentPositioning == "absolute")
	break;

    left += target.offsetLeft;
    top  += target.offsetTop;
    target = target.offsetParent;
  }
  left += target.offsetLeft;
  top  += target.offsetTop;
  return { x: left, y: top };
}

function mouseMove (event) {
  event = event || window.event;
  var mousePos = mouseCoords(event);

  // console.log(mousePos.x + ", " + mousePos.y);
  for (var i in mouseListeners) {
    if (mouseListeners[i]) {
      var target = getElement(i);
      var position = getPosition(target);
      var offset = { x: mousePos.x - position.x, y: mousePos.y - position.y };
      mouseListeners[i].call(offset);
    }
  }

  if (dragObject) {
    dragObject.style.position = 'absolute';
    var top = mousePos.y - mouseOffset.y;
    var left = mousePos.x - mouseOffset.x;
    dragObject.style.top =  top + "px";
    dragObject.style.left = left + "px";
    // log("(" + mousePos.x + ", " + mousePos.y + ") - (" + mouseOffset.x + ", " + mouseOffset.y + ") = (" + left + ", " + top + ")");
    // log(dragObject.style.left + ", " + dragObject.style.top);
    return false;
  }
  if (resizeTarget) {
    // log("(" + mousePos.x + ", " + mousePos.y + ") - (" + mouseOffset.x + ", " + mouseOffset.y + ")");
    var width = stripTrailing(resizeTarget.style.width, 2);
    var height = stripTrailing(resizeTarget.style.height, 2);
    width = /* Number(width) - */ (Number(mousePos.x) - Number(mouseOffset.x));
    height = /* Number(height) - */ (Number(mousePos.y) - Number(mouseOffset.y));
    // log("(" + width + ", " + height + ")");
    if (width < 1) width = 1;
    if (height < 1) height = 1;
    resizeTarget.style.width = width + "px";
    resizeTarget.style.height = height + "px";
  }

  if (dragResizeHandle) {
    dragResizeHandle.style.position = 'absolute';
    var top = mousePos.y - mouseOffset.y;
    var left = mousePos.x - mouseOffset.x;
    dragResizeHandle.style.top =  top + "px";
    dragResizeHandle.style.left = left + "px";
    // log("(" + mousePos.x + ", " + mousePos.y + ") - (" + mouseOffset.x + ", " + mouseOffset.y + ") = (" + left + ", " + top + ")");
    return false;
  }
}

function mouseUp () {
  if (dragObject && typeof(dragObject.dragListener) == "function")
	dragObject.dragListener.call(this);

  dragObject = null;
  dragResizeHandle = null;
  resizeTarget = null;
}

function initializeDragHandle (event) {
  var parent = this.parentNode;
  dragObject = parent;
  bringToTop(parent);
  mouseOffset = getMouseOffset(parent, event);
  return false;
}

function initializeResizeHandle (event) {
  var parent = this.parentNode;
  dragResizeHandle = this;
  resizeTarget = parent;
  bringToTop(parent);
  mouseOffset = getAbsolutePosition(parent);
  return false;
}

function initializeDrag (event) {
  dragObject = this;
  bringToTop(this);
  mouseOffset = getMouseOffset(this, event);
  return false;
}

function bringToTop (divide) {
  topWindowIndex = Number(topWindowIndex) + 1;  // Bring the window to the top.
  divide.style.zIndex = topWindowIndex;
}

function makeDraggable (item) {
  if (!item) return;
  item.onmousedown = initializeDrag;
}

function makeDraggableHandle (handle) {
  if (!handle) return;
  handle.onmousedown = initializeDragHandle;
}

function makeResizeableHandle (handle) {
  if (!handle) return;
  handle.onmousedown = initializeResizeHandle;
}