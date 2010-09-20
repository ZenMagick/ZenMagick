<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
 *
 * Based on andreas08 by Andreas Viklund  -  http://andreasviklund.com
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
?>
<script type="text/javascript"><!--
// generic
function isValidDate(day, month, year) {
if (month < 1 || month > 12) { return false; }
if (day < 1 || day > 31) { return false; }
if ((month == 4 || month == 6 || month == 9 || month == 11) && (day == 31)) { return false; }
if (month == 2) { var leap = (year % 4 == 0 && (year % 100 != 0 || year % 400 == 0)); if (day > 29 || (day == 29 && !leap)) { return false; } } return true; }
function parseDate(s, format) {
var dd = '??'; var mm = '??'; var cc = '??'; var yy = '??';
format = format.toUpperCase();
var dpos = format.indexOf('DD');
if (-1 != dpos) { dd = s.substring(dpos, dpos+2); }
var mpos = format.indexOf('MM');
if (-1 != mpos) { mm = s.substring(mpos, mpos+2); }
var cpos = format.indexOf('CC');
if (-1 != cpos) { cc = s.substring(cpos, cpos+2); }
var cypos = format.indexOf('YYYY');
if (-1 != cypos) { cc = s.substring(cypos, cypos+2); yy = s.substring(cypos+2, cypos+2+2);
} else { var ypos = format.indexOf('YY'); if (-1 != ypos) { yy = s.substring(ypos, ypos+2); } }
return new Array(dd, mm, cc.concat(yy));
}

// validations
function isDate(elem, format) {
var da = parseDate(elem.value, format); return ('' == elem.value || isValidDate(da[0], da[1], da[2]));
}
function isNotEmpty(elem) { 
if (!elem || undefined == elem) { return false; }
if (elem.type) {
switch (elem.type.toLowerCase()) {
case 'text': case 'password': case 'textarea': return '' != elem.value; break;
case 'checkbox': return elem.checked; break;
case 'radio': if (typeof(elem.length) == "undefined" && elem.checked) { return true; } for (var ii=0; ii<elem.length; ++ii) { if (elem[ii].checked) { return true; } } return false; break;
case 'select': return -1 != elem.selectedIndex; break;
}
} else { for (var ii=0; ii<elem.length; ++ii) { if (elem[ii].checked) { return true; } } return false; }
return true;
}
function isMinLength(elem, min) { return (!elem || undefined == elem || '' == elem.value || elem.value.length >= min); }
function isMaxLength(elem, max) { return (!elem || undefined == elem || '' == elem.value || elem.value.length <= max); }
function isRegexp(elem, expr) { return (!elem || undefined == elem || '' == elem.value || elem.value.match(expr)); }
function isFieldMatch(elem1, elem2) { return elem1.value == elem2.value; }
function inArray(elem, arr) { for (key in arr) { if (elem.value == arr[key]) { return true; } } return false;; }

// stop duplicate form submits
var _zm_submitted = false;
// generic form validation
function validate(form) {
if (_zm_submitted) { alert('<?php _vzm("This form has already been submitted. Please press Ok and wait for this process to complete.") ?>'); return false; }
var msg = '<?php _vzm("Errors have occurred during the processing of your form.\\n\\nPlease make the following corrections:\\n\\n") ?>';
var isValid = true;
var rules = eval("zm_"+form.getAttribute('id')+"_validation_rules");
for (var ii=0; ii<rules.length; ++ii) { var rule = rules[ii];
switch (rule[0]) {
case 'required': var relems = rule[1].split(','); var isEmpty = true;
  for (var jj=0; jj<relems.length; ++jj) { if (isNotEmpty(form.elements[relems[jj]])) { isEmpty = false; break; } } 
  if (isEmpty) { isValid = false; msg += '* ' + rule[2] + '\n'; }
  break;
case 'min': if (!isMinLength(form.elements[rule[1]], rule[3])) { isValid = false; msg += '* ' + rule[2] + '\n'; } break;
case 'max': if (!isMaxLength(form.elements[rule[1]], rule[3])) { isValid = false; msg += '* ' + rule[2] + '\n'; } break;
case 'regexp': if (!isRegexp(form.elements[rule[1]], rule[3])) { isValid = false; msg += '* ' + rule[2] + '\n'; } break;
case 'fieldMatch': if (!isFieldMatch(form.elements[rule[1]], form.elements[rule[3]])) { isValid = false; msg += '* ' + rule[2] + '\n'; } break;
case 'list': if (!inArray(form.elements[rule[1]], rule[3])) { isValid = false; msg += '* ' + rule[2] + '\n'; } break;
case 'date': if (!isDate(form.elements[rule[1]], rule[3])) { isValid = false; msg += '* ' + rule[2] + '\n'; } break;
default: alert('unknown validation rule: ' + rule[0]); break;
}
}
if (isValid) { _zm_submitted = true;
} else { alert(msg); }
return isValid;
}
--></script>
