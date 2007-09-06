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
 * @version $Id$
 */
?>
<?php

    /**
     * Store locator view.
     *
     * @package net.radebatz.zenmagick.plugins.zm_google_store_locator
     */
    function zm_view_store_locator() {
    global $zm_google_store_locator;

        $key = $zm_google_store_locator->get('key');
        $location = $zm_google_store_locator->get('location');
        $zoom = $zm_google_store_locator->get('zoom');
        $markerText = $zm_google_store_locator->get('marker_text');
        $controls = zm_boolean($zm_google_store_locator->get('controls'));

        $script = '
<script type="text/javascript" src="http://www.google.com/jsapi?key='.$key.'"></script>
<script type="text/javascript">
  google.load("maps", "2");
  function load_map() {
    if (GBrowserIsCompatible()) {
      var map = new google.maps.Map2(document.getElementById("locator_map"));
      '.($controls ? 'map.addControl(new google.maps.LargeMapControl());/*map.addControl(new google.maps.MapTypeControl());*/' : '').'
      map.setCenter(new google.maps.LatLng('.$location.'), '.$zoom.');
      var marker = new google.maps.Marker(map.getCenter());
      map.addOverlay(marker);
      '.(!zm_is_empty($markerText) ? 'marker.openInfoWindowHtml("'.$markerText.'")' : '').'
    }
  }
  google.setOnLoadCallback(load_map);
</script>
';
        $map = <<<EOT
<div id="locator_map" style="width:400px;height:400px;"></div>
<div id="stores">
  <a href="#">Store1</a>
  <a href="#">Store2</a>
</div>
EOT;
        echo $script . '<h2>' . zm_l10n_get("Find our stores using Google Maps!") . '</h2>'.$map;
    }

    /**
     * Store locator admin.
     *
     * @package net.radebatz.zenmagick.plugins.zm_google_store_locator
     */
    function zm_store_locator_admin() {
        $contents = "foo";
        return new ZMPluginPage('store_locator_admin', zm_l10n_get('Store Locator'), $contents);
    }

?>
