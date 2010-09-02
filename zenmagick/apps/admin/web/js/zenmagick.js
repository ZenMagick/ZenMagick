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
var ZenMagick = {
    // base url for jsonRCP calls
    _jsonRPCBaseUrl: 'index.php?rid=ajax_',
    // unique request id for jsonRPC calls
    _jsonRPCRequestId: 1,


    /**
     * Compare version numbers.
     *
     * Example: 0.9.10, 0.9.9+.20100804
     */
    versionCompare: function (first, second) {
        if ('${zenmagick.version}' == first || '${zenmagick.version}' == second) {
            return '${zenmagick.version}' == first ? ('${zenmagick.version}' == second ? 0 : 1) : -1;
        }
        var parseVersion = function (version) {
            token = /(\d+)\.?(\d+)?\.?(\d+\+?)?\.?(\d+)?/.exec(version);
            var maj = parseInt(token[1]) || 0;
            var min = parseInt(token[2]) || 0;
            var pat = parseInt(token[3].replace(/\+/, '')) || 0;
            var ts = parseInt(token[4]) || 0;
            return Array(maj, min, pat, ts);
        };
        first = parseVersion(first);
        second = parseVersion(second);
        for (var ii=0; ii<4; ++ii) {
            if (first[ii] != second[ii]) {
                return first[ii] > second[ii] ? 1 : -1;
            }
        }
        return 0;
    },

    /**
     * Init all date picker elements.
     */
    datepicker: function() {
        $('.datepicker').datepicker({
            showOn: 'button',
            buttonImageOnly: false,
            buttonText: '',
            showButtonPanel: true
		    });
    },

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
     * Default failure callback.
     *
     * @param mixed error Error object.
     */
    failure: function(error) {
        alert('error: ' + error);
    },

    /**
     * Perform Ajax JSON-RPC call.
     *
     * @param string controller The ZenMagick ajax controller name.
     * @param string method The method to call on the controller.
     * @param string params The payload as string.
     * @param object callbacks Callback object with <code>success</code> and <code>failure</code> function.
     */
    rpc: function(controller, method, params, callbacks) {
        var requestId = this._jsonRPCRequestId++;
        $.ajax({
            type: "POST",
            contentType: 'application/json',
            url: this._jsonRPCBaseUrl+controller,
            data: '{"id":'+requestId+',"method":"'+method+'","params":'+params+',"jsonrpc":"2.0"}',
            success: function(response) { 
                // parse to figure out if success really means success
                if (requestId == response.id && response.result && !response.error) {
                    // success
                    callbacks.success(response.result);
                    return;
                }
                if (callbacks.failure) {
                    callbacks.failure(response.error);
                } else {
                    // default callback
                    zenmagick.failure(response.error);
                }
            },
            error: function() { 
                if (callbacks.failure) {
                    callbacks.failure(null);
                } else {
                    // default callback
                    zenmagick.failure(null);
                }
            }
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
     * @param formId The id of the form to 'ajaxify'.
     * @param function callback Optional callback function called before the actual submit.
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
