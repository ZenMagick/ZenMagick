/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */

$(document).ready(function(){
    $('.new-win').click(function(event) {
        window.open($(this).attr('href'));
        event.preventDefault();
    });
});
// focus on particular fomr element
function focus($id) {
    if (document.getElementById) {
        var elem = document.getElementById($id);
        if (elem) { elem.focus(); }
    }
}

// zen-cart popups
function zcPopupWindow(url, name) {
  var win = window.open(url,name,'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,width=450,height=320,screenX=150,screenY=150,top=150,left=150');
  if (win && win.focus) { win.focus(); }
}
function popupWindow(url) { zcPopupWindow(url, 'popupWindow'); }
function couponpopupWindow(url) { zcPopupWindow(url, 'couponpopupWindow'); }
function submitFunction(gv, total) { if ((gv.value && gv.value >= total) || gv >= total) { submitter = 1; } }


// product image popup
function productPopup(e, parent) {
  if (e.preventDefault) { e.preventDefault(); }
  var win = window.open("","productImageWindow","height=206,width=246");
  if (!win) { return; }
  win.document.write('<!DOCTYPE html><html>'
    + '<title>' + document.getElementsByTagName('title')[0].innerHTML + '</title>'
    + '<style type="text/css">html,body,p{margin:0;padding:0}body{color:#6b6b6b}p{margin:8px;font:70% sans-serif;text-align:center}a{color:#125991}</style>'
    + '<p><img id="theimg" src="' + parent.href + '" height="160" width="200" alt="">'
    + '<p>[ <a href="#" onclick="javascript:window.close()">Close Window</a> ]</html>');
  win.document.close();
  win.focus();
}

// this depends heavily on the category creation code
function catclick(link) {
    var siblings = link.parentNode.childNodes;
    // find ul sibling
    for (var ii=0; ii < siblings.length; ++ii) {
        if ('UL' == siblings[ii].nodeName) {
            var ul = siblings[ii];
            if ("none" == ul.style.display || ("" === ul.style.display && -1 == ul.className.indexOf('act'))) {
                ul.style.display = "block";
                ul.style.visibility = "visible";
            } else {
                ul.style.display = "none";
                ul.style.visibility = "hidden";
            }
            return false;
        }
    }
    return true;
}
