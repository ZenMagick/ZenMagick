<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
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
// generic field tester
function isNotEmpty(elem) { 
if (elem.type) {
switch (elem.type.toLowerCase()) {
case 'text': case 'password': case 'textarea': return '' != elem.value; break;
case 'checkbox': return elem.checked; break;
case 'radio': for (var ii=0; ii<elem.length; ++ii) { if (elem[ii].checked) { return true; } } return false; break;
case 'select': return -1 != elem.selectedIndex; break;
}
} else { for (var ii=0; ii<elem.length; ++ii) { if (elem[ii].checked) { return true; } } return false; }
return true;
}
function isMinLength(elem, min) { return !(elem.value == '' || elem.value.length < min); }
function isRegexp(elem, expr) { return elem.value.match(expr); }
function isFieldMatch(elem1, elem2) { return elem1.value == elem2.value; }

// stop duplicate form submits
var _zm_submitted = false;
// generic form validation
function validate(form) {
if (_zm_submitted) { alert('<?php zm_l10n("This form has already been submitted. Please press Ok and wait for this process to be completed.") ?>'); return false; }
var msg = '<?php zm_l10n("Errors have occurred during the processing of your form.\\n\\nPlease make the following corrections:\\n\\n") ?>';
var isValid = true;
var rules = eval(form.id+"_rules");
for (var ii=0; ii<rules.length; ++ii) { var rule = rules[ii];
switch (rule[0]) {
case 'required': if (!isNotEmpty(form.elements[rule[1]])) { isValid = false; msg += '* ' + rule[2] + '\n'; } break;
case 'min': if (!isMinLength(form.elements[rule[1]], rule[3])) { isValid = false; msg += '* ' + rule[2] + '\n'; } break;
case 'regexp': if (!isRegexp(form.elements[rule[1]], rule[3])) { isValid = false; msg += '* ' + rule[2] + '\n'; } break;
case 'fieldMatch': if (!isFieldMatch(form.elements[rule[1]], form.elements[rule[3]])) { isValid = false; msg += '* ' + rule[2] + '\n'; } break;
default: alert('unknown validation rule: ' + rule[0]); break;
}
}
if (isValid) { _zm_submitted = true;
} else { alert(msg); }
return isValid;
}
--></script>
