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
var ZenMagick = {
    // base url for jsonRCP calls
    _jsonRPCBaseUri: 'index.php?rid=ajax_',
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
        switch (error.code) {
        case 5:
            // invalid credentials
            break;
        case 6:
            // no credentials
            window.location.replace(error.data.data.location);
            return;
        }
        var text = '';
        for (var type in error.data.messages) {
            for (var msg in error.data.messages[type]) {
                text += type + ': ' + error.data.messages[type][msg] + '\n';
            }
        }
        alert(text);
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
            url: this._jsonRPCBaseUri+controller,
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
                    ZenMagick.failure(response.error);
                }
            },
            error: function() { 
                if (callbacks.failure) {
                    callbacks.failure(null);
                } else {
                    // default callback
                    ZenMagick.failure(null);
                }
            }
        });
    },

    /**
     * Confirmation dialog.
     *
     * @param string msg The message.
     * @param object src The source element.
     * @param obj args Optional arguments.
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
     * @param obj args Additional arguments: title, width, maxHeight, resizable, height, onload, id, modal, buttons
     */
    ajaxDialog: function(url, args) {
        args.id = args.id || 'ajax-dialog';
        args.width = args.width || 660;
        args.maxHeight = args.maxHeight || false;
        args.resizable = args.hasOwnProperty('resizable') ? args.resizable : true;
        args.modal = args.hasOwnProperty('modal') ? args.modal : true;
        args.height = args.height || 'auto';
        args.onload = args.onload || function () {};
        args.buttons = args.buttons || { "OK": function() { $(this).dialog("destroy"); $('#'+args.id).remove(); } };
        $('<div id="'+args.id+'">Loading...</div>').dialog({
            modal: args.modal,
            position: ['center', 20],
            title: args.title,
            width: args.width,
            height: args.height,
            resizable: args.resizable,
            maxHeight: args.maxHeight,
            close: function() {
                $(this).dialog("destroy");
                $('#'+args.id).remove();
            },
            buttons: args.buttons,
        }).load(url, args.onload);

        return false;
		},

    /**
     * Form dialog with ajax form loading and submit.
     *
     * @param string url The ajax url to load the form.
     * @param obj args Additional arguments: all of <code>ajaxDialog</code> (except buttons), plus: formId, onsubmit,
     */
    ajaxFormDialog: function(url, args) {
        // default for form dialogs
        args.id = args.id || 'ajax-form-dialog';
        // no additional buttons
        args.buttons = [];
        // on dialog load
        args.onload = function() {
            var div = this;
            // attach ajax form handler
            $('#'+args.formId).submit(function() {
                if (args.onsubmit) {
                  eval(args.onsubmit+'(this);');
                }
                $(this).ajaxSubmit({ 
                    success: function() {
                        $(div).dialog("destroy");
                        $('#'+args.id).remove();
                    }
                });
                // return false to prevent normal browser submit and page navigation
                return false;
            });
        };

        this.ajaxDialog(url, args);

        return false;
		}

};
