<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 * $Id$
 */
?>

<p>This is the second demo page illustrating checkout Ajax options in <em>ZenMagick</em>. For more details about Ajax in ZenMagick 
check out the <a href="<?php $net->url('ajax_demo') ?>">main Ajax demo</a>.</p>

<?php $utils->jsNow('jquery.js') ?>
<?php $utils->jsNow('jquery.form.js') ?>
<?php $utils->jsNow('json2.js') ?>

<label for="msgbox"><strong>Messages</strong></label>
<div id="msgbox" style="height:1.8em;border:1px solid gray;margin:5px 0 12px;padding:3px;color:red"></div>

<form action="#" style="margin:32px 0;">
    <fieldset>
        <legend>Shipping Estimator</legend>
        <p>Calculates and displays available shipping options based on the current cart contents and the provided address details.
        (will default to cart address details if none given)</p>
        <p><label for="countryId">CountryId </label> <input type="text" name="countryId" id="countryId" value="153"></p>
        
        <div id="methodList" style="margin:6px 2px;border-top:1px solid gray;border-bottom:1px solid gray;padding:2px;">
        </div>
        <div id="address" style="margin:4px 0;">
        </div>
        <input type="button" value="Calculate shipping" onclick="calculateShipping();" />
    </fieldset>
</form>
<script type="text/javascript">
    var msgboxElem = document.getElementById('msgbox');
    var methodListElem = document.getElementById('methodList');
    var addressElem = document.getElementById('address');

    // update shipping method list
    function updateShippingInfoSuccess(msg) {
        msgboxElem.innerHTML += "got response ...";

        var info = msg.parseJSON();

        if (0 == info.length) {
            msgboxElem.innerHTML += "no shipping available ... ";
            methodListElem.innerHTML = '<strong>No Shipping Available (cart empty??)</strong><br>';
        } else {
            methodListElem.innerHTML = '<strong>Available methods:</strong><br>';
        }

        for (var ii=0; ii < info.length; ++ii) {
            var method = info[ii];
            methodListElem.innerHTML += method.id + ' ' + method.provider.name + ' ' + method.name + ' ' + method.cost + '<br>';
            methodListElem.innerHTML += method.provider.errors + '<br>';
        }

        if (undefined !== info.address) {
            addressElem.innerHTML = '<strong>Address:</strong> ';
            addressElem.innerHTML += info.address.firstName + ' ' + info.address.lastName;
        }

        msgboxElem.innerHTML += "DONE!";
    };

    function updateShippingInfoFailure(msg) {
        msgboxElem.innerHTML += " update shipping failed!";
    };

    // calculate shipping for current customer/cart
    function calculateShipping() {
        msgboxElem.innerHTML = "Getting shipping methods ... ";
        methodListElem.innerHTML = '';
        addressElem.innerHTML = '';
        $.ajax({
            type: "GET",
            url: "<?php $net->ajax('checkout', 'getShippingMethods') ?>",
            data: "countryId="+document.getElementById("countryId").value,
            success: function(msg) { updateShippingInfoSuccess(msg); },
            error: function(msg) { updateShippingInfoFailure(msg); }
        });
    }
</script>
