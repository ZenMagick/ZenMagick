/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
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
 *
 * $Id$
 */

function focus($id) {
    if (document.getElementById) {
        var elem = document.getElementById($id);
        if (elem) elem.focus();
    }
}

function popupWindow(url) {
  var win = window.open(url,'popupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=yes,copyhistory=no,width=100,height=100,screenX=150,screenY=150,top=150,left=150');
  if (win && win.focus) {
    win.focus();
  }
}

function newWin(link) {
  var win = window.open(link.href);
  if (win && win.focus) {
    win.focus();
  }
}

function productPopup(e, parent) {
  if (e.preventDefault) e.preventDefault();
  var win = window.open("","pimage","height=206,width=246");
  if (!win) return;
  win.document.write('<!DOCTYPE html><html>'
    + '<title>' + document.getElementsByTagName('title')[0].innerHTML + '</title>'
    + '<style type="text/css">html,body,p{margin:0;padding:0}body{color:#6b6b6b}p{margin:8px;font:70% sans-serif;text-align:center}a{color:#125991}</style>'
    + '<p><img id="theimg" src="' + parent.href + '" height="160" width="200" alt="">'
    + '<p>[ <a href="#" onclick="javascript:window.close()">Close Window</a> ]</html>');
  win.document.close();
  win.focus();
}
