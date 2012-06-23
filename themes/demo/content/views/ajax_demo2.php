<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
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
?>

<p>This is the second demo page illustrating checkout Ajax options in <em>ZenMagick</em>. For more details about Ajax in ZenMagick
check out the <a href="<?php echo $net->url('ajax_demo') ?>">main Ajax demo</a>.</p>

<?php $resourceManager->jsFile('jquery.js', $resourceManager::NOW) ?>
<?php $resourceManager->jsFile('jquery.form.js', $resourceManager::NOW) ?>
<?php $resourceManager->jsFile('json2.js', $resourceManager::NOW) ?>

<label for="msgbox"><strong>Messages</strong></label>
<div id="msgbox" style="height:1.8em;border:1px solid gray;margin:5px 0 12px;padding:3px;color:red"></div>

<form action="#" style="margin:32px 0;">
    <fieldset>
        <legend>Shipping Estimator</legend>
        <p>Calculates and displays available shipping options based on the current cart contents and the provided address details.
        (will default to cart address details if none given)</p>
        <p>
            <label for="countryId">Countries</label>
            <select id="countryId" name="countryId">
                <option value=""> --- </option>
            </select>
            <input type="button" value="Load Countries" onclick="loadCountries();" />
        </p>

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

        var info =JSON.parse(msg);

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
            url: "<?php echo $net->ajax('checkout', 'getShippingMethods') ?>",
            data: "countryId="+document.getElementById("countryId").value,
            success: function(msg) { updateShippingInfoSuccess(msg); },
            error: function(msg) { updateShippingInfoFailure(msg); }
        });
    }

    var countriesElem = document.getElementById('countryId');

    // Load countries
    function loadCountries() {
        msgboxElem.innerHTML = "Loading countries ... ";

        $.ajax({
            type: "GET",
            url: "<?php echo $net->ajax('country', 'getCountryList') ?>",
            success: function(msg) {
                msgboxElem.innerHTML += "got response ...";

                var countryList = JSON.parse(msg);

                msgboxElem.innerHTML += "updating ... ";

                countriesElem.length = 0;
                var country = new Option('-- Select Country --', '', false, false);
                countriesElem.options[countriesElem.length] = country;

                for (var ii=0; ii < countryList.length; ++ii) {
                    var country = new Option(countryList[ii].name+' ('+countryList[ii].id+')', countryList[ii].id, false, false);
                    countriesElem.options[countriesElem.length] = country;
                }

                msgboxElem.innerHTML += "done!";
            }
        });
    }
</script>
