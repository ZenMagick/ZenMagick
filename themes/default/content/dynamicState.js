/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
 * $Id: dynamicState.js 2115 2009-03-27 04:17:41Z dermanomann $
 */

$(document).ready(function() {
    $('#countryId').change(updateState);
});

function updateState() {
    // show timer
    var zoneId = $('#zoneId');
    var state = $('#state');
    var countryId = $('#countryId').val();
    var sz = 0 < zoneId.size() ? zoneId : state;
    if (all_zones[countryId]) {
        var state_value = $('#state').val();
        var state_select = '<select id="zoneId" name="zoneId">';
        state_select += '<option value=""><?php _vzm("-- Please select a state --") ?></option>';
        for (var zoneId in all_zones[countryId]) {
            var name = all_zones[countryId][zoneId];
            var selected = state_value == name ? ' selected="selected"' : '';
            state_select += '<option value="'+zoneId+'"'+selected+'>'+name+'</option>';
        }
        state_select += '</select>';

        // replace with dropdown
        sz.after(state_select).remove();
    } else {
        // free text
       sz.after('<input type="text" id="state" name="state" value="">').remove();
    }
};
