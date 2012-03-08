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
$(function() {
    var lastState = '';

    function buildState() {
        var state = {};
        state.layout = $('#dashboard').attr('class');;
        state.widgets = [];
        $('.db-column').each(function(columnIndex, column) {
            state.widgets[columnIndex] = [];
            $(column).find('.portlet').each(function(index, portlet) {
              var bean = portlet.getAttribute('id').substring(8)+'#';
              var open = 0 != $(portlet).find('.ui-icon-minusthick').length;
              bean += 'open='+(open?'true':'false');
              state.widgets[columnIndex].push(bean);
            });
        });
        // convert to json
        var json = '{"layout":"'+state.layout+'","widgets":[';
        for (var ii=0; ii<state.widgets.length; ++ii) {
            if (0 < ii) { json += ','; }
            json += '[';
            for (var jj=0; jj<state.widgets[ii].length; ++jj) {
                if (0 < jj) { json += ','; }
                json += '"'+state.widgets[ii][jj]+'"';
            }
            json += ']';
        }
        json += ']}';
        return json;
    }

    function saveState() {
        state = buildState();
        if (state == lastState) {
            // no change
            return;
        }
        lastState = state;
        ZenMagick.rpc('dashboard', 'saveState', state, {
            success: function(result) {
                // nothing right now
            }
        });
    }

    function mkSortableColumn(selector) {
        // set up dashboad
        $(selector).sortable({
            connectWith: '.db-column, .widget-box-col',
            handle: '.portlet-grip',
            zIndex: 2001,
            update: function(event, ui) { saveState(); },
            receive: function(event, ui) { 
                // open
                $(ui.item).find('.ui-icon-plusthick').toggleClass("ui-icon-minusthick").toggleClass("ui-icon-plusthick")
                    .parents(".portlet:first").find(".portlet-content").toggle();
            },
            cursor: 'move'
        });
    }

    // set up dashboard
    mkSortableColumn('.db-column');

    // set up widget box
    $(".widget-box-col").sortable({
        connectWith: '.widget-box-col, .db-column',
        handle: '.portlet-grip',
        receive: function(event, ui) { 
            // close
            $(ui.item).find('.ui-icon-minusthick').toggleClass("ui-icon-minusthick").toggleClass("ui-icon-plusthick")
                .parents(".portlet:first").find(".portlet-content").toggle();
        },
        cursor: 'move'
    });

    // inital setup
    $(".portlet").addClass("ui-widget ui-widget-content ui-helper-clearfix ui-corner-all")
        .find(".portlet-header")
            .addClass("ui-widget-header ui-corner-top")
            .html(function(index, oldhtml) { return '<div class="portlet-grip">'+oldhtml+'</div>'; })
            // add icons
            .prepend(function(index, oldhtml) {
                var oc = $(this).hasClass('open') ? 'minusthick' : 'plusthick';
                return '<a href="" class="ui-icon ui-icon-closethick"></a><a href="" class="ui-icon ui-icon-'+oc+'"></a><a href="" class="ui-icon ui-icon-gear"></a>'
            })
            .end()
        .find(".portlet-content")
    ;

    // track open/close
    $(".portlet-header .ui-icon-minusthick, .portlet-header .ui-icon-plusthick").click(function() {
        if ($(this).parents('.db-column').length) {
            $(this).toggleClass("ui-icon-minusthick").toggleClass("ui-icon-plusthick");
            $(this).parents(".portlet:first").find(".portlet-content").toggle();
            saveState();
        }
        return false;
    });
    // track remove
    var nextAppendTo = 'first';
    $(".portlet-header .ui-icon-closethick").click(function() {
        if ($(this).parents('.db-column').length) {
            // close
            $(this).parents('.portlet')
                .find('.ui-icon-minusthick').toggleClass("ui-icon-minusthick").toggleClass("ui-icon-plusthick")
                .parents(".portlet:first").find(".portlet-content").toggle();
            // move to widget-box
            $(this).parents('.portlet').appendTo('#widget-box .widget-box-col:'+nextAppendTo);
            nextAppendTo = 'first' == nextAppendTo ? 'last' : 'first';
            saveState();
        }
        return false;
    });

    // set cursor on grip
    $(".portlet-grip").hover(
        function() { $(this).css('cursor', 'move'); }, 
        function() { $(this).css('cursor', 'auto'); }
    );

    // attach callback to grid-layout links
    $(".db-grid-selector").click(function() {
        var selected = $(this).attr('id');
        var current = $('#dashboard').attr('class');

        // has it changed
        if (selected != current) {
            var cols = selected.match(/\d/);
            if (0 != $('#dashboard #db-column-2').length && 2 == cols) {
                // move widgets
                $('#dashboard #db-column-2 .portlet').each(function(columnIndex, column) {
                    $(column).appendTo($('#db-column-'+(columnIndex%2)));
                });

                // drop
                $('#dashboard #db-column-2').remove();
            } else if (0 == $('#dashboard #db-column-2').length && 3 == cols) {
                // create
                $('#dashboard').append('<div id="db-column-2" class="db-column"></div>');
                // init selectable again
                mkSortableColumn('#db-column-2');
            }
            $('#dashboard').attr('class', selected);
            saveState();
        }
        return false;
    });
});
