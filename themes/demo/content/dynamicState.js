/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
 */

$(document).ready(function() {
    $('#countryId').change(updateState);
});

function updateState() {
    // show timer
    var zoneId = $('#zoneId');
    var state = $('#state');
    var sz = ((0 < zoneId.size()) ? zoneId : state);
    sz.after('<span id="state_timer"><img src="<?php echo $this->asUrl('images/circle-ball-dark-antialiased.gif') ?>"> <?php _vzm("Loading...") ?></span>');

    var countryId = $('#countryId').val();
    $.ajax({
        type: "GET",
        url: "<?php echo $net->ajax('country', 'getZonesForCountryId') ?>",
        data: "countryId="+countryId,
        success: function(msg) {
            // remove timer
            $('#state_timer').remove();
            var zoneList = JSON.parse(msg);
            if (0 < zoneList.length) {
                var state_value = $('#state').val();
                var state_select = '<select id="zoneId" name="zoneId">';
                state_select += '<option value=""><?php _vzm("-- Please select a state --") ?></option>';
                for (var ii=0; ii < zoneList.length; ++ii) {
                    var option = zoneList[ii];
                    var selected = state_value == option ? ' selected="selected"' : '';
                    state_select += '<option value="'+option.id+'"'+selected+'>'+option.name+'</option>';
                }
                state_select += '</select>';

                // replace with dropdown
                sz.after(state_select).remove();
            } else {
                // free text
               sz.after('<input type="text" id="state" name="state" value="">').remove();
            }
        }
    });
}
