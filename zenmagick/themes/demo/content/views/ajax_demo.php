<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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

<script type="text/javascript" src="<?php $zm_theme->themeURL("prototype15.js") ?>"></script>

<p>This is a demo page illustrating Ajax in <em>ZenMagick</em>. The examples use <a href="http://www.json.org">JSON</a> and <code>XML</code>
as data format. Not sure which one is better or whether to support both, though. The controller is using
 <a href="http://pear.php.net/pepr/pepr-proposal-show.php?id=198">PEAR Json</a> for the JSON encoding.
Depending on your server configuration you might be better of using something different (which might already be installed.</p>

<p>Ajax is implemented using <a href="http://www.prototypejs.org/">Prototype</a> and <a href="http://developer.yahoo.com/yui/">yui</a>.</p>

<p>Some things to keep in mind:</p>
<ul>
  <li>This is demo code and kept very simple (basically I just modified the demo code found for Prototype and YUI)</li>
  <li>The zones will be loaded when selecting a country (Austria and Canada are at the top of countries that have zones configured...)</li>
  <li>To me XML feels a bit faster - I haven't done any benchmarking, though</li>
  <li>The JSON generating code in the Ajax controller is probably not the best (yet)</li>
  <li>The HTML formatting of the results is intentionally *very* simple</li>
  <li>There is a lot more that could be implemented as Ajax controller; reviews, etc...</li>
</ul>

<label for="p_msgbox">Request Status</label>
<div id="p_msgbox" style="height:1.8em;border:1px solid gray;margin:5px 0 12px;padding:7px;color:red"></div>

<form action="#">
    <fieldset>
        <legend>Ajax country/zone demo using <em>Prototype and JSON</em></legend>
        <p>
            <label for="pj_countries">Countries</label>
            <select id="pj_countries" name="pj_countries" onchange="pj_loadZones()">
                <option value=""> --- </option>
            </select>
            <input type="button" value="Load Countries" onclick="pj_loadCountries();" />
        </p>
        <p>
            <label for="pj_zones">Zones</label>
            <select id="pj_zones" name="p_zones">
                <option value=""> --- </option>
            </select>
        </p>
    </fieldset>
</form>


<script type="text/javascript">

    var msgbox = document.getElementById('p_msgbox');
    var pj_countries = document.getElementById('pj_countries');
    var pj_zones = document.getElementById('pj_zones');


    function pj_loadCountries() {
        msgbox.innerHTML = "Loading countries(JSON) ... ";

        new Ajax.Request('<?php zm_ajax_href('country', 'getCountryListJSON') ?>', {
            method: 'get',
            onSuccess: function(transport, json) {
                msgbox.innerHTML += "got response ...";

                if (json && Object.inspect(json)) {
                    msgbox.innerHTML += "updating ... ";

                    pj_countries.length = 0;
                    var country = new Option('-- Select Country --', '', false, false);
                    pj_countries.options[pj_countries.length] = country;

                    for (var ii=0; ii < json.length; ++ii) {
                        var country = new Option(json[ii].name+' ('+json[ii].id+')', json[ii].id, false, false);
                        pj_countries.options[pj_countries.length] = country;
                    }
                }

                msgbox.innerHTML += "done!";
            }
        });
    }

    function pj_loadZones() {
        var countryId = pj_countries.value;
        msgbox.innerHTML = "Loading zones(JSON) for countryId="+countryId+" ... ";

        new Ajax.Request('<?php zm_ajax_href('country', 'getZonesForCountryIdJSON') ?>&countryId='+countryId, {
            method: 'get',
            onSuccess: function(transport, json) {
                msgbox.innerHTML += "got response ...";

                if (json && Object.inspect(json)) {
                    msgbox.innerHTML += "updating ... ";

                    pj_zones.length = 0;
                    var zone = new Option('-- Select Zone --', '', false, false);
                    pj_zones.options[pj_zones.length] = zone;

                    // zones are stored under their id
                    if (undefined === json.length) json = Object.values(json) 
                    for (var ii=0; ii < json.length; ++ii) {
                        var zone = new Option(json[ii].name+' ('+json[ii].id+')', json[ii].id, false, false);
                        pj_zones.options[pj_zones.length] = zone;
                    }
                }

                msgbox.innerHTML += "done!";
            }
        });
    }
</script>


<form action="#">
    <fieldset>
        <legend>Ajax country/zone demo using <em>Prototype and XML</em></legend>
        <p>
            <label for="px_countries">Countries</label>
            <select id="px_countries" name="px_countries" onchange="px_loadZones()">
                <option value=""> --- </option>
            </select>
            <input type="button" value="Load Countries" onclick="px_loadCountries();" />
        </p>
        <p>
            <label for="px_zones">Zones</label>
            <select id="px_zones" name="px_zones">
                <option value=""> --- </option>
            </select>
        </p>
    </fieldset>
</form>


<script type="text/javascript">

    var px_countries = document.getElementById('px_countries');
    var px_zones = document.getElementById('px_zones');


    function px_loadCountries() {
        msgbox.innerHTML = "Loading countries(XML) ... ";

        new Ajax.Request('<?php zm_ajax_href('country', 'getCountryListXML') ?>', {
            method: 'get',
            onSuccess: function(transport) {
                msgbox.innerHTML += "got response ...";

                var root = transport.responseXML.documentElement; 
                var list = root.childNodes;

                msgbox.innerHTML += "updating ... ";

                px_countries.length = 0;
                var country = new Option('-- Select Country --', '', false, false);
                px_countries.options[px_countries.length] = country;

                for (var ii=0; ii < list.length; ++ii) {
                    var id = list[ii].getAttribute('id');
                    var name = list[ii].getAttribute('name'); 
                    var country = new Option(name+' ('+id+')', id, false, false);
                    px_countries.options[px_countries.length] = country;
                }

                msgbox.innerHTML += "DONE!";
            }
        });
    }

    function px_loadZones() {
        var countryId = px_countries.value;
        msgbox.innerHTML = "Loading zones(XML) for countryId="+countryId+" ... ";

        new Ajax.Request('<?php zm_ajax_href('country', 'getZonesForCountryIdXML') ?>&countryId='+countryId, {
            method: 'get',
            onSuccess: function(transport) {
                msgbox.innerHTML += "got response ...";

                var root = transport.responseXML.documentElement; 
                var list = root.childNodes;

                msgbox.innerHTML += "updating ... ";

                px_zones.length = 0;
                var zone = new Option('-- Select Zone --', '', false, false);
                px_zones.options[px_zones.length] = zone;

                for (var ii=0; ii < list.length; ++ii) {
                    var id = list[ii].getAttribute('id');
                    var name = list[ii].getAttribute('name'); 
                    var zone = new Option(name+' ('+id+')', id, false, false);
                    px_zones.options[px_zones.length] = zone;
                }

                msgbox.innerHTML += "DONE!";
            }
        });
    }
</script>


<script type="text/javascript" src="<?php $zm_theme->themeURL("yui/build/yahoo/yahoo-min.js") ?>"></script> 
<script type="text/javascript" src="<?php $zm_theme->themeURL("yui/build/connection/connection-min.js") ?>"></script> 
<form action="#">
    <fieldset>
        <legend>Ajax shipping estimator <em>YUI and JSON</em></legend>
        <div id="methodList" style="margin:4px 0;">
        </div>
        <div id="address" style="margin:4px 0;">
        </div>
        <input type="button" value="(Re-)Calculate shipping" onclick="calculateShipping();" />
    </fieldset>
</form>


<script type="text/javascript">

    var country_id = document.getElementById('country_id');
    var state = document.getElementById('state');
    var methodList = document.getElementById('methodList');
    var address = document.getElementById('address');

    function loadStates() {
        var countryId = country_id.value;
        msgbox.innerHTML = "Loading zones for countryId="+countryId+" ... ";

        new Ajax.Request('<?php zm_ajax_href('country', 'getZonesForCountryIdXML') ?>&countryId='+countryId, {
            method: 'get',
            onSuccess: function(transport) {
                msgbox.innerHTML += "got response ...";

                var root = transport.responseXML.documentElement; 
                var list = root.childNodes;

                msgbox.innerHTML += "updating ... ";

                px_zones.length = 0;
                var zone = new Option('-- Select Zone --', '', false, false);
                state.options[state.length] = zone;

                for (var ii=0; ii < list.length; ++ii) {
                    var id = list[ii].getAttribute('id');
                    var name = list[ii].getAttribute('name'); 
                    var zone = new Option(name+' ('+id+')', id, false, false);
                    state.options[state.length] = zone;
                }

                msgbox.innerHTML += "DONE!";
            }
        });
    }

    var updateShippingInfoSuccess = function(o) {
        var json = o.getResponseHeader['X-JSON'];
        if (json !== undefined){
            var info = eval('('+json+')');
            if (0 == info.methods.length) {
		            msgbox.innerHTML += "no shipping available ... ";
                alert('No shipping available - did you put something into the shopping cart?');
            } else {
                methodList.innerHTML = '<strong>Available methods:</strong><br>';
            }

            for (var ii=0; ii < info.methods.length; ++ii) {
                var method = info.methods[ii];
                methodList.innerHTML += method.id + ' ' + method.name + ' ' + method.cost + '<br>';
            }

            if (undefined !== info.address) {
                address.innerHTML = '<strong>Address:</strong> ';
                address.innerHTML += info.address.firstName + ' ' + info.address.lastName;
            }

		        msgbox.innerHTML += "DONE!";
	      }
    };

    var updateShippingInfoFailure = function(o) {
        msgbox.innerHTML += "update shipping failed!";
    };

    var updateShippingInfo = {
        success: updateShippingInfoSuccess,
        failure: updateShippingInfoFailure,
        argument: {}
    };

    function calculateShipping() {
        msgbox.innerHTML = "Call shipping estimator ... ";
        methodList.innerHTML = '';
        address.innerHTML = '';
        var request = YAHOO.util.Connect.asyncRequest('GET', '<?php zm_ajax_href('shopping_cart', 'estimateShippingJSON') ?>', updateShippingInfo);
    }
</script>

<form action="#">
    <fieldset>
        <legend>Ajax shopping cart items <em>Prototype and JSON</em></legend>
        <div id="cart" style="margin:4px 0;">
            Cart is empty.
        </div>
        <input type="button" value="Refresh cart" onclick="refreshCart();" />
    </fieldset>
</form>

<script type="text/javascript">
    var cart = document.getElementById('cart');


    function refreshCart() {
        msgbox.innerHTML = "Loading cart(JSON) ... ";
        cart.innerHTML = '';

        new Ajax.Request('<?php zm_ajax_href('shopping_cart', 'getItems') ?>', {
            method: 'get',
            onSuccess: function(transport, json) {
                msgbox.innerHTML += "got response ...";

                if (json && Object.inspect(json)) {
                    msgbox.innerHTML += "updating ... ";

                    for (var ii=0; ii < json.items.length; ++ii) {
                        var item = json.items[ii];
                        cart.innerHTML += item.name + ' ' + item.qty + ' ' + item.itemTotal + '<br>';
                    }
                    cart.innerHTML += '# of items: ' + json.items.length + '<br>';
                    cart.innerHTML += 'Total: ' + json.total + '<br>';
                }

                msgbox.innerHTML += "done!";
            }
        });
    }
</script>

