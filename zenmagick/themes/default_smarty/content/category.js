/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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

function inject_category_code() {
    if (document.getElementsByTagName) {
        var as = document.getElementsByTagName('A');
        for (var ii=0; ii < as.length; ++ii) {
            if (-1 < as[ii].className.indexOf('empty')) {
                as[ii].setAttribute('onclick', 'return catclick(this);');
            }
        }
    }
}

// this depends heavily on the category creation code
function catclick(link) {
    var siblings = link.parentNode.childNodes;
    // find ul sibling
    for (var ii=0; ii < siblings.length; ++ii) {
        if ('UL' == siblings[ii].nodeName) {
            var ul = siblings[ii];
            if ("none" == ul.style.display || ("" == ul.style.display && -1 == ul.className.indexOf('act'))) {
                ul.style.display = "block";
                ul.style.visibility = "visible";
            } else {
                ul.style.display = "none";
                ul.style.visibility = "hidden";
            }
            return false
        }
    }
    return true;
}
