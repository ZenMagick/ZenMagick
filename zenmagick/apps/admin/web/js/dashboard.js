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
$(function() {
    var lastState = '';

    function buildState() {
        var state = {};
        state.columns = 1;
        state.widgets = [];
        $('.db-column').each(function(columnIndex, column) {
            state.widgets[columnIndex] = [];
            $(column).find('.portlet').each(function(index, portlet) {
              var bean = portlet.getAttribute('id').substring(8)+'#';
              var open = 0 != $(portlet).find('.ui-icon-minusthick').length;
              bean += 'open='+(open?'true':'false');
              state.widgets[columnIndex].push(bean);
            });
            state.columns = columnIndex+1;
        });
        // convert to json
        var json = '{"columns":'+state.columns+',"widgets":[';
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
        json = escape(json);
        return json;
    }

    function saveState() {
        state = buildState();
        if (state == lastState) {
            // no change
            return;
        }
        lastState = state;
        $.ajax({
            type: "POST",
            //TODO: how to set this??
            url: "index.php?rid=ajax_dashboard&method=saveState",
            data: 'state='+state,
            success: function(msg) { 
                // TODO:
            },
            error: function(msg) { 
                alert(msg);
            }
        });
    }

    // set up dashboad
    $(".db-column").sortable({
        connectWith: '.db-column',
        handle: '.portlet-grip',
        update: function(event, ui) { saveState(); },
        cursor: 'move'
    });

    // inital setup
    $(".portlet").addClass("ui-widget ui-widget-content ui-helper-clearfix ui-corner-all")
        .find(".portlet-header")
            .addClass("ui-widget-header ui-corner-all")
            .html(function(index, oldhtml) { return '<div class="portlet-grip">'+oldhtml+'</div>'; })
            // add icons
            .prepend(function(index, oldhtml) {
                var oc = $(this).hasClass('open') ? 'minusthick' : 'plusthick';
                return '<span class="ui-icon ui-icon-closethick"></span><span class="ui-icon ui-icon-'+oc+'"></span></span><span class="ui-icon ui-icon-wrench"></span>'
            })
            .end()
        .find(".portlet-content")
    ;

    // track open/close
    $(".portlet-header .ui-icon-minusthick, .portlet-header .ui-icon-plusthick").click(function() {
        $(this).toggleClass("ui-icon-minusthick").toggleClass("ui-icon-plusthick");
        $(this).parents(".portlet:first").find(".portlet-content").toggle();
        saveState();
    });
    // track remove
    $(".portlet-header .ui-icon-closethick").click(function() {
        $(this).parents('.portlet').css('display', 'none');
        saveState();
    });

    // set cursor on grip
    $(".portlet-grip").hover(
        function() { $(this).css('cursor', 'move'); }, 
        function() { $(this).css('cursor', 'auto'); }
    );
});
