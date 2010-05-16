/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
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
 * $Id: zenmagick.js 1966 2009-02-14 10:52:50Z dermanomann $
 */

var zenmagick = {
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
    ajaxFormDialog: function(url, title) {
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
            $('#ajax-form').submit(function() {
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
		}

};
