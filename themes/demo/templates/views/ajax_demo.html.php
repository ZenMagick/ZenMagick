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

<p>This is a demo page illustrating Ajax in <em>ZenMagick</em>. The examples use <a href="http://www.json.org">JSON</a>
data format. If you want to use anything else, like XML, just write your own methods and you are good to go.</p>
<p>The controller is using <a href="http://pear.php.net/pepr/pepr-proposal-show.php?id=198">PEAR Json</a> for the JSON encoding.
Depending on your server configuration you might be better of using something different (possibly something already installed).</p>

<p>The actual Ajax bits are implemented using <a href="http://www.jquery.com/">jQuery</a> and <a href="http://www.json.org/">json</a>.</p>

<p>Some things to keep in mind:</p>
<ul>
  <li>This is demo code and kept very simple</li>
  <li>The zones will be loaded when selecting a country (Austria and Canada are at the top of countries that have zones configured...)</li>
  <li>The JSON generating code in the Ajax controller is probably not the best (yet)</li>
  <li>The HTML formatting of the results is intentionally *very* simple</li>
  <li>There is a lot more that could be implemented as Ajax controller; reviews, etc...</li>
</ul>

<?php $resourceManager->jsFile('js/jquery.js', $resourceManager::NOW) ?>
<?php $resourceManager->jsFile('js/jquery.form.js', $resourceManager::NOW) ?>
<?php $resourceManager->jsFile('js/json2.js', $resourceManager::NOW) ?>

<label for="msgbox"><strong>Messages</strong></label>
<div id="msgbox" style="height:1.8em;border:1px solid gray;margin:5px 0 12px;padding:3px;color:red"></div>

<form action="#" style="margin:32px 0;">
    <fieldset>
        <legend>Shopping Cart</legend>
        <p>Simple display of the cart contents. This area is also being updated by the Ajax Cart demo further down the page.</p>
        <div id="cart" style="margin:6px 2px;border-top:1px solid gray;border-bottom:1px solid gray;padding:2px;">
            Cart is empty.
        </div>
        <input type="button" value="Refresh cart" onclick="refreshCart();" />
    </fieldset>
</form>
<script type="text/javascript">
    var msgboxElem = document.getElementById('msgbox');
    var cartElem = document.getElementById('cart');

    // update cart content
    function updateCartContent(msg) {
        if (!msg) {
            cartElem.innerHTML += 'Cart (still) empty!';
            return;
        }
        //var json = eval('(' + msg + ')');
        var cartInfo =JSON.parse(msg);
        for (var ii=0; ii < cartInfo.items.length; ++ii) {
            var item = cartInfo.items[ii];
            cartElem.innerHTML += "Id: "+item.id+", Name: "+item.name + ', qty: ' + item.qty + ', line total: ' + item.itemTotal + '<br>';
        }
        cartElem.innerHTML += '# of items in cart: ' + cartInfo.items.length + '<br>';
        cartElem.innerHTML += 'Total: ' + cartInfo.total + '<br>';
    }

    // refresh cart
    function refreshCart() {
        msgboxElem.innerHTML = "Refreshing cart ... ";
        cartElem.innerHTML = '';

        $.ajax({
            type: "GET",
            url: "<?php echo $net->ajax('shopping_cart', 'getContents') ?>",
            success: function(msg) {
                msgboxElem.innerHTML += "got response ... ";
                updateCartContent(msg);
                msgboxElem.innerHTML += "done!";
            }
        });
    }

    // load on document ready
    $(document).ready(function() { refreshCart(); });
</script>


<form action="#" id="productForm" style="margin:32px 0;">
    <fieldset>
        <legend>Simple Ajax Shopping Cart</legend>
        <p>Allows to add/update/remove items to/from the shopping cart. The updated cart contents is
        displayed.</p>
        <p>
            <label for="productId">ProductId</label>
            <input type="text" id="productId" name="productId" value="34" size="6" />
            <label for="quantity">Qty</label><input type="text" id="quantity" name="quantity" value="1" size="4" />
              <input type="button" value="Load product details" onclick="loadProduct();" />
              <input type="button" value="Clear product details" onclick="clearProduct();" />
            <br />
            <div id="productDetails" style="margin:6px 2px;border-top:1px solid gray;border-bottom:1px solid gray;padding:2px;">
            </div>
            <input type="button" value="Add to cart" onclick="sc_add();" />
            <input type="button" value="Remove from cart" onclick="sc_remove();" />
            <input type="button" value="Update quantity" onclick="sc_update();" />
        </p>
    </fieldset>
</form>


<script type="text/javascript">
    var productIdElem = document.getElementById('productId');
    var productDetailsElem = document.getElementById('productDetails');
    var quantityElem = document.getElementById('quantity');

    // show attributes
    function showAttributesValues(attributes) {
        for (var ii=0; ii < attributes.length; ++ii) {
            var attribute = attributes[ii];
            productDetailsElem.innerHTML += '<b>attribute:</b> id: '+ attribute.id + " - " + attribute.name + ' (type: ' + attribute.type + ')<br>';
            for (var jj=0; jj < attribute.values.length; ++jj) {
                var value = attribute.values[jj];
                productDetailsElem.innerHTML += '&nbsp;&nbsp;id: '+ value.id + " - " + value.name + '<br>';
            }
        }
    }

    // show attributes
    // this is the JS equivalent to core/html/defaults/products.php
    function showAttributesForm(attributes) {
        for (var ii=0; ii < attributes.length; ++ii) {
            var attribute = attributes[ii];
            switch (attribute.type) {
            case <?php echo PRODUCTS_OPTIONS_TYPE_SELECT ?>:
                productDetailsElem.innerHTML += '<b>'+attribute.name+'</b><br>';
                var html = '';
                html += '<select name="id['+attribute.id+']">';
                for (var jj=0; jj < attribute.values.length; ++jj) {
                    var value = attribute.values[jj];
                    html += '<option value="'+value.id+'">'+value.name+'</option>';
                }
                html += '</select>';
                html += '<br>';
                productDetailsElem.innerHTML += html;
                break;
            case <?php echo PRODUCTS_OPTIONS_TYPE_RADIO ?>:
                productDetailsElem.innerHTML += '<b>'+attribute.name+'</b><br>';
                var html = '';
                var name = 'id['+attribute.id+']';
                for (var jj=0; jj < attribute.values.length; ++jj) {
                    var value = attribute.values[jj];
                    var id = 'id_'+attribute.id+'_'+jj;
                    var checked = value.default ? ' checked="checked"' : '';
                    html += '<input type="radio" id="'+id+'" name="'+name+'" value="'+value.id+'"'+checked+'>';
                    html += '<label for="'+id+'">'+value.name+'</label>';
                }
                html += '<br>';
                productDetailsElem.innerHTML += html;
                break;
            case <?php echo PRODUCTS_OPTIONS_TYPE_CHECKBOX ?>:
                var html = '';
                var name = 'id['+attribute.id+']';
                for (var jj=0; jj < attribute.values.length; ++jj) {
                    var value = attribute.values[jj];
                    var id = 'id_'+attribute.id+'_'+jj;
                    var checked = value.default ? ' checked="checked"' : '';
                    html += '<input type="checkbox" id="'+id+'" name="'+name+'['+value.id+']" value="'+value.id+'"'+checked+'>';
                    html += '<label for="'+id+'">'+value.name+'</label>';
                }
                html += '<br>';
                productDetailsElem.innerHTML += html;
                break;
            case <?php echo PRODUCTS_OPTIONS_TYPE_TEXT ?>:
                productDetailsElem.innerHTML += '<b>'+attribute.name+'</b><br>';
                var html = '';
                for (var jj=0; jj < attribute.values.length; ++jj) {
                    var value = attribute.values[jj];
                    var id = 'id_'+attribute.id+'_'+jj;
                    var name = 'id[<?php echo $settingsService->get('textOptionPrefix') ?>'+attribute.id+']';
                    html += '<label for="'+id+'">'+value.name+'</label>';
                    html += '<input type="text" id="'+id+'" name="'+name+'" value=""/>';
                }
                html += '<br>';
                productDetailsElem.innerHTML += html;
                break;
            default:
                productDetailsElem.innerHTML += '<b>'+attribute.name+'</b>(not supported)<br>';
                for (var jj=0; jj < attribute.values.length; ++jj) {
                    var value = attribute.values[jj];
                    productDetailsElem.innerHTML += '&nbsp;&nbsp;id: '+ value.id + " - " + value.name + '<br>';
                }
            }
        }
    }

    // clear product
    function clearProduct() {
        productDetailsElem.innerHTML = '';
    }

    // load product information
    function loadProduct() {
        var productId = productIdElem.value;

        msgboxElem.innerHTML = "Loading product " + productId + " ... ";
        productDetailsElem.innerHTML = '';

        $.ajax({
            type: "GET",
            url: "<?php echo $net->ajax('catalog', 'getProductForId') ?>",
            data: "productId="+productId,
            success: function(msg) {
                msgboxElem.innerHTML += "got response ... ";

                //var product = eval('(' + msg + ')');
                var product = JSON.parse(msg);
                //productDetailsElem.innerHTML += 'id: ' + product.id + "<br>";
                productDetailsElem.innerHTML += 'name: ' + product.name + "<br>";
                productDetailsElem.innerHTML += 'model: ' + product.model + "<br>";
                //productDetailsElem.innerHTML += 'description: ' + product.description + "<br>";

                //showAttributesValues(product.attributes);
                showAttributesForm(product.attributes);

                msgboxElem.innerHTML += "done!";
            }
        });
    }

    function sc_add() {
        var productId = productIdElem.value;
        var queryString = $('#productForm').formSerialize();

        msgboxElem.innerHTML = "Adding product " + productId + " ... ";
        cartElem.innerHTML = '';

        $.ajax({
            type: "POST",
            url: "<?php echo $net->ajax('shopping_cart', 'addProduct') ?>",
            data: queryString,
            success: function(msg) {
                msgboxElem.innerHTML += "got response ... ";
                updateCartContent(msg);
                msgboxElem.innerHTML += "done!";
            }
        });
    }

    function sc_remove() {
        var productId = productIdElem.value;

        msgboxElem.innerHTML = "Removing product " + productId + " ... ";
        cartElem.innerHTML = '';

        $.ajax({
            type: "POST",
            url: "<?php echo $net->ajax('shopping_cart', 'removeProduct') ?>",
            data: "productId="+productId,
            success: function(msg) {
                msgboxElem.innerHTML += "got response ... ";
                updateCartContent(msg);
                msgboxElem.innerHTML += "done!";
            }
        });
    }

    function sc_update() {
        var productId = productIdElem.value;
        var quantity = quantityElem.value;

        msgboxElem.innerHTML = "Updating product " + productId + " ... ";
        cartElem.innerHTML = '';

        $.ajax({
            type: "POST",
            url: "<?php echo $net->ajax('shopping_cart', 'updateProduct') ?>",
            data: "productId="+productId+"&quantity="+quantity,
            success: function(msg) {
                msgboxElem.innerHTML += "got response ... ";
                updateCartContent(msg);
                msgboxElem.innerHTML += "done!";
            }
        });
    }
</script>


<form action="#" style="margin:32px 0;">
    <fieldset>
        <legend>Simple Shipping Estimator</legend>
        <p>Calculates and displays available shipping options based on the current cart contents and
        shipping address.</p>
        <div id="methodList" style="margin:6px 2px;border-top:1px solid gray;border-bottom:1px solid gray;padding:2px;">
        </div>
        <div id="address" style="margin:4px 0;">
        </div>
        <input type="button" value="(Re-)Calculate shipping" onclick="calculateShipping();" />
    </fieldset>
</form>
<script type="text/javascript">
    var methodListElem = document.getElementById('methodList');
    var addressElem = document.getElementById('address');

    // update shipping method list
    function updateShippingInfoSuccess(msg) {
        msgboxElem.innerHTML += "got response ...";

        var info = JSON.parse(msg);

        if (0 == info.methods.length) {
            msgboxElem.innerHTML += "no shipping available ... ";
            methodListElem.innerHTML = '<strong>No Shipping Available (cart empty??)</strong><br>';
        } else {
            methodListElem.innerHTML = '<strong>Available methods:</strong><br>';
        }

        for (var ii=0; ii < info.methods.length; ++ii) {
            var method = info.methods[ii];
            methodListElem.innerHTML += method.id + ': ' + method.name + ' ' + method.cost + '<br>';
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
        msgboxElem.innerHTML = "Call shipping estimator ... ";
        methodListElem.innerHTML = '';
        addressElem.innerHTML = '';
        $.ajax({
            type: "GET",
            url: "<?php echo $net->ajax('shopping_cart', 'estimateShipping') ?>",
            success: function(msg) { updateShippingInfoSuccess(msg); },
            error: function(msg) { updateShippingInfoFailure(msg); }
        });
    }
</script>


<form action="#" style="margin:32px 0;">
    <fieldset>
        <legend>Country / Zone demo</legend>
        <p>Load the list of available countries and zones on demand.</p>
        <p>
            <label for="countries">Countries</label>
            <select id="countries" name="countries" onchange="loadZones()">
                <option value=""> --- </option>
            </select>
            <input type="button" value="Load Countries" onclick="loadCountries();" />
        </p>
        <p>
            <label for="zones">Zones</label>
            <select id="zones" name="zones">
                <option value=""> --- </option>
            </select>
        </p>
    </fieldset>
</form>
<script type="text/javascript">
    var countriesElem = document.getElementById('countries');
    var zonesElem = document.getElementById('zones');

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

    // load zones
    function loadZones() {
        var countryId = countriesElem.value;
        msgboxElem.innerHTML = "Loading zones for countryId="+countryId+" ... ";

        $.ajax({
            type: "GET",
            url: "<?php echo $net->ajax('country', 'getZonesForCountryId') ?>",
            data: "countryId="+countryId,
            success: function(msg) {
                msgboxElem.innerHTML += "got response ...";

                var zoneList = JSON.parse(msg);

                msgboxElem.innerHTML += "updating ... ";

                zonesElem.length = 0;
                var zone = new Option('-- Select Zone --', '', false, false);
                zonesElem.options[zonesElem.length] = zone;

                // zones are stored under their id
                for (var ii=0; ii < zoneList.length; ++ii) {
                    var zone = new Option(zoneList[ii].name+' ('+zoneList[ii].id+')', zoneList[ii].id, false, false);
                    zonesElem.options[zonesElem.length] = zone;
                }

                msgboxElem.innerHTML += "done!";
            }
        });
    }
</script>
