// NoRightClick Script
// Author: Markus Jung - markus@design-function.de
// Created: 20.01.2003
// Dieses Script unterdrückt die Popup-Menus in den
// Browsern. Und macht auch noch anderes ;o))

self.defaultStatus="dnsZone-Manager";

IE4plus = (document.all) ? true : false;

function clickIE() {
  return false;
}

function clickNS(e)
{
  if (e.which==2 || e.which==3) {
    return false;
  }
}

if (!IE4plus) {
  document.captureEvents(Event.MOUSEDOWN || Event.MOUSEUP);
  document.onmousedown=clickNS;
  document.onmouseup= clickNS;
  document.oncontextmenu=clickIE; // Für NS6+
} 
else {
  document.onmouseup= clickIE;
  document.oncontextmenu=clickIE;
}

function inputfocus() {
	document.forms["login"].elements["user"].focus();
}

function mOverActive(cell) {
  cell.style.cursor='hand';
}
function mOutActive(cell) {
  cell.style.cursor='default';
}
function mOver(cell) {
  cell.bgColor='#E5E5E5';
  cell.style.cursor='hand';
}
function mOut(cell) {
  //cell.bgColor='#CEDFF1';
  cell.bgColor='#E8F3FF';
  cell.style.cursor='default';
}
