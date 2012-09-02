/*
 * ZenMagick - Another PHP framework.
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
var zmFormValidation = {
    // stop duplicate form submits
    submitted: false,
    // l10n strings
    messages: {
        'alreadySubmitted': 'This form has already been submitted. Please press Ok and wait for this process to complete.',
        'errors': "Errors have occurred during the processing of your form.\n\nPlease make the following corrections:\n\n"
    },

    /**
     * Confirmation dialog.
     *
     * @param string msg The message.
     */
    confirm: function(msg) {
        alert(msg);
    },

    /**
     * Check for valid date.
     *
     * @param int day The day.
     * @param int month The month.
     * @param int year The year.
     * @return boolean <code>true</code> if the date appears valid.
     */
    isValidDate: function(day, month, year) {
        if (month < 1 || month > 12) { return false; }
        if (day < 1 || day > 31) { return false; }
        if ((month == 4 || month == 6 || month == 9 || month == 11) && (day == 31)) { return false; }
        if (month == 2) {
            var leap = (year % 4 == 0 && (year % 100 != 0 || year % 400 == 0));
            if (day > 29 || (day == 29 && !leap)) { return false; }
        }
        return true;
    },

    /**
     * Parse a given date string.
     *
     * @param string s The string.
     * @param string format The expected date format.
     * @return array An array containing day, month and year.
     */
    parseDate: function(s, format) {
        var dd = '??'; var mm = '??'; var cc = '??'; var yy = '??';
        format = format.toUpperCase();
        var dpos = format.indexOf('DD');
        if (-1 != dpos) { dd = s.substring(dpos, dpos+2); }
        var mpos = format.indexOf('MM');
        if (-1 != mpos) { mm = s.substring(mpos, mpos+2); }
        var cpos = format.indexOf('CC');
        if (-1 != cpos) { cc = s.substring(cpos, cpos+2); }
        var cypos = format.indexOf('YYYY');
        if (-1 != cypos) {
            cc = s.substring(cypos, cypos+2); yy = s.substring(cypos+2, cypos+2+2);
        } else {
            var ypos = format.indexOf('YY');
            if (-1 != ypos) { yy = s.substring(ypos, ypos+2); }
        }
        return new Array(dd, mm, cc.concat(yy));
    },

    /**
     * Date validation.
     *
     * @param HTMLElement elem The date element.
     * @param string format The date format.
     * @return boolean <code>true</code> if the element value is a valid date.
     */
    isDate: function(elem, format) {
        var da = this.parseDate(elem.value, format);
        return ('' == elem.value || this.isValidDate(da[0], da[1], da[2]));
    },

    /**
     * Check for empty element.
     *
     * @param HTMLElement elem The element.
     * @return boolean <code>true</code> if the element value is <strong>not</strong> empty.
     */
    isNotEmpty: function(elem) {
        if (!elem || undefined == elem) {
            return false;
        }
        if (elem.nodeName) {
            // single form elements
            switch (elem.nodeName.toLowerCase()) {
                case 'input':
                  switch (elem.type.toLowerCase()) {
                      case 'text':
                      case 'password':
                      case 'textarea':
                          return '' != elem.value;
                      case 'checkbox':
                          return elem.checked;
                      case 'radio':
                          if (typeof(elem.length) == "undefined" && elem.checked) {
                              return true;
                          }
                          for (var ii=0; ii<elem.length; ++ii) {
                              if (elem[ii].checked) {
                                  return true;
                              }
                          }
                          return false;
                      }
                    break;
                case 'select':
                    return -1 != elem.selectedIndex && '' != elem.options[elem.selectedIndex].value;
            }
        } else {
            // radio/checkbox group
            for (var ii=0; ii<elem.length; ++ii) {
                if (elem[ii].checked) { return true; }
            }
            return false;
        }
        return true;
    },

    /**
     * Check for minimum length.
     *
     * @param HTMLElement elem The element.
     * @param int min The mimimum length.
     * @return boolean <code>true</code> if the element value is valid.
     */
    isMinLength: function(elem, min) {
        return (!elem || undefined == elem || '' == elem.value || elem.value.length >= min);
    },

    /**
     * Check for maximum length.
     *
     * @param HTMLElement elem The element.
     * @param int max The maximum length.
     * @return boolean <code>true</code> if the element value is valid.
     */
    isMaxLength: function(elem, max) {
        return (!elem || undefined == elem || '' == elem.value || elem.value.length <= max);
    },

    /**
     * Check for regexp match.
     *
     * @param HTMLElement elem The element.
     * @param string expr The regular expression.
     * @return boolean <code>true</code> if the element value matches the expression.
     */
    isRegexp: function(elem, expr) {
        return (!elem || undefined == elem || '' == elem.value || elem.value.match(expr));
    },

    /**
     * Check for field match.
     *
     * @param HTMLElement elem1 The first element.
     * @param HTMLElement elem2 The second element.
     * @return boolean <code>true</code> if the element values matches.
     */
    isFieldMatch: function(elem1, elem2) {
        return elem1.value == elem2.value;
    },

    /**
     * Check against a list of given values.
     *
     * @param HTMLElement elem The element.
     * @param array arr List if valid values.
     * @return boolean <code>true</code> if the element values matches any of the given values.
     */
    inArray: function(elem, arr) {
        for (key in arr) {
            if (elem.value == arr[key]) { return true; }
        }
        return false;
    },

    /**
     * Validate the given form against a list of rules.
     *
     * @param HTMLElement form The form.
     * @return boolean <code>true</code> if all validation rules have been evaluated sucessfully.
     * @todo localization
     * @todo integrate rules?
     */
    validate: function(form) {
        if (this.submitted) {
            this.confirm(this.messages['alreadySubmitted']);
            return false;
        }

        var msg = this.messages['errors'];
        var isValid = true;
        var rules = eval("zm_"+form.getAttribute('id')+"_validation_rules");
        for (var ii=0; ii<rules.length; ++ii) {
            var rule = rules[ii];
            switch (rule[0]) {
                case 'required':
                    var relems = rule[1].split(',');
                    var isEmpty = true;
                    for (var jj=0; jj<relems.length; ++jj) {
                        if (this.isNotEmpty(form.elements[relems[jj]])) {
                            isEmpty = false;
                            break;
                        }
                    }
                    if (isEmpty) {
                        isValid = false;
                        msg += '* ' + rule[2] + '\n';
                    }
                    break;
                case 'min':
                    if (!this.isMinLength(form.elements[rule[1]], rule[3])) {
                        isValid = false;
                        msg += '* ' + rule[2] + '\n';
                    }
                    break;
                case 'max':
                    if (!this.isMaxLength(form.elements[rule[1]], rule[3])) {
                        isValid = false;
                        msg += '* ' + rule[2] + '\n';
                    }
                    break;
                case 'regexp':
                    if (!this.isRegexp(form.elements[rule[1]], rule[3])) {
                        isValid = false;
                        msg += '* ' + rule[2] + '\n';
                    }
                    break;
                case 'fieldMatch':
                    if (!this.isFieldMatch(form.elements[rule[1]], form.elements[rule[3]])) {
                        isValid = false;
                        msg += '* ' + rule[2] + '\n';
                    }
                    break;
                case 'list':
                    if (!this.inArray(form.elements[rule[1]], rule[3])) {
                        isValid = false;
                        msg += '* ' + rule[2] + '\n';
                    }
                    break;
                case 'date':
                    if (!this.isDate(form.elements[rule[1]], rule[3])) {
                        isValid = false;
                        msg += '* ' + rule[2] + '\n';
                    }
                    break;
                default:
                    //alert('unknown validation rule: ' + rule[0]);
                    break;
            }
        }

        if (isValid) {
            this.submitted = true;
        } else {
            this.confirm(msg);
        }

        return isValid;
    }
};
