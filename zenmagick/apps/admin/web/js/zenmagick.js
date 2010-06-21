/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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
var zenmagick = {
    /**
     * ucwords.
     *
     * @param string s The string.
     * @return string The new string.
     */
    ucwords: function(s) {
        return s.replace(/\w+/g, function(a) {
            return a.charAt(0).toUpperCase() + a.substr(1);
        });
    },

    /**
     * Confirmation dialog.
     *
     * @param string msg The message.
     * @param object src The source element.
     * @param array args Optional arguments.
     */
    confirm: function(msg, src, args) {
        $('<div id="user-confirm"></div>')
            .html('<p>'+msg+'</p>')
            .dialog({
                modal: true,
                title: 'Please confirm:',
                close: function() {
                    $(this).dialog("destroy");
                    $('#user-confirm').remove();
                },
                buttons: {
                    "Cancel": function() {
                        $(this).dialog("destroy");
                        $('#user-confirm').remove();
                    },
                    "Ok": function() {
                        $(this).dialog("destroy");
                        $('#user-confirm').remove();
                        switch (src.nodeName.toLowerCase()) {
                            case 'a': { window.document.location = $(src).attr('href'); break; }
                            case 'form': { src.submit(); break; }
                            default: { alert("Oops, don't know how to handle a "+src.nodeName); break; }
                        }
                    }
                }
            });

        return false;
    },

    /**
     * Form dialog with ajax form loading and submit.
     *
     * @param string url The ajax url to load the form.
     * @param string title The title.
     */
    ajaxDialog: function(url, title, width) {
        var dwidth = 660;
        if (width) {
            dwidth = width;
        }
        $('<div id="ajax-dialog">Loading...</div>').dialog({
            modal: true,
            position: ['center', 20],
            title: title,
            width: dwidth,
            close: function() {
                $(this).dialog("destroy");
                $('#ajax-dialog').remove();
            },
            buttons: {
                "OK": function() {
                    $(this).dialog("destroy");
                    $('#ajax-dialog').remove();
                }
            },
        }).load(url, function() {
            var div = this;
            // nothing for now
        });

        return false;
		},

    /**
     * Form dialog with ajax form loading and submit.
     *
     * @param string url The ajax url to load the form.
     * @param string title The title.
     */
    ajaxFormDialog: function(url, title, formId, callback) {
        $('<div id="ajax-form-dialog">Loading...</div>').dialog({
            modal: true,
            position: ['center', 20],
            title: title,
            width: 660,
            close: function() {
                $(this).dialog("destroy");
                $('#ajax-form-dialog').remove();
            }
        }).load(url, function() {
            var div = this;
            // attach ajax form handler
            $('#'+formId).submit(function() {
                if (callback) {
                  eval(callback+'(this);');
                }
                $(this).ajaxSubmit({ 
                    success: function() {
                        $(div).dialog("destroy");
                        $('#ajax-form-dialog').remove();
                    }
                });
                // return false to prevent normal browser submit and page navigation
                return false;
            });
        });

        return false;
		}

};
