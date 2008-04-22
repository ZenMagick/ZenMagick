<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
     * @package org.zenmagick.plugins.zm_google_store_locator
     */
    function zm_view_store_locator() {
    global $zm_google_store_locator;

        $storeKey = $zm_google_store_locator->get('storeKey');
        $location = $zm_google_store_locator->get('location');
        $zoom = $zm_google_store_locator->get('zoom');
        $markerText = $zm_google_store_locator->get('marker_text');
        $controls = ZMTools::asBoolean($zm_google_store_locator->get('controls'));

        $script = '
<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key='.$storeKey.'" type="text/javascript"></script>
<script type="text/javascript">
  function load_locator_map() {
      if (GBrowserIsCompatible()) {
        var map = new GMap2(document.getElementById("locator_map"));
        '.($controls ? 'map.addControl(new GLargeMapControl());/*map.addControl(new GMapTypeControl());*/' : '').'
        map.setCenter(new GLatLng('.$location.'), '.$zoom.');
        var marker = new GMarker(map.getCenter());
        map.addOverlay(marker);
        '.(!empty($markerText) ? 'marker.openInfoWindowHtml("'.$markerText.'")' : '').'
      }
  }
  //google.setOnLoadCallback(load_locator_map);
</script>
';
        $map = <<<EOT
<div id="locator_map" style="width:400px;height:400px;border:1px solid #ccc;"><?php zm_l10n("Loading map...") ?></div>
<div id="stores">
</div>
EOT;
        echo $script . '<h2>' . zm_l10n_get("Find our store using Google Maps!") . '</h2>'.$map;
    }

    /**
     * Store locator admin.
     *
     * @package org.zenmagick.plugins.zm_google_store_locator
     */
    function zm_store_locator_admin() {
    global $zm_google_store_locator;

        if ('POST' == ZMRequest::getMethod()) {
            $values = ZMRequest::getParameter('configuration', array());
            foreach ($values as $name => $value) {
                $zm_google_store_locator->set($name, $value);
            }
            ZMRequest::redirect(zm_plugin_admin_url());
        }

        $adminKey = $zm_google_store_locator->get('adminKey');
        $location = $zm_google_store_locator->get('location');
        $zoom = $zm_google_store_locator->get('zoom');

        $script = '
<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key='.$adminKey.'" type="text/javascript"></script>
<script type="text/javascript">
  var marker = null;

  function copy_location() {
      var inputs = document.getElementsByTagName("input");
      for (var ii=0; ii < inputs.length; ++ii) {
        if (inputs[ii].name == "configuration[LOCATION]") {
          if (marker && marker.getPoint) {
            var point = new String(marker.getPoint());
            point = point.substring(1, point.length-1);
            inputs[ii].value = point;
          }
        }
      }
  }

  function load_locator_map() {
      if (GBrowserIsCompatible()) {
        var map = new GMap2(document.getElementById("locator_map"));
        map.addControl(new GLargeMapControl());
        map.setCenter(new GLatLng('.$location.'), '.$zoom.');
        marker = new GMarker(map.getCenter(), {draggable: true});
        GEvent.addListener(marker, "dragstart", function() { map.closeInfoWindow(); });
        GEvent.addListener(marker, "dragend", function() { });
        map.addOverlay(marker);
        marker.openInfoWindowHtml("Drag me to mark a point<br>to mark your store.<br>Then click \'copy location\' to set your store location.");
        GEvent.addListener(map, "moveend", function() { marker.setPoint(map.getCenter()); });
      }
  }
  window.onload = load_locator_map;
</script>
';
        $map = <<<EOT
<div style="margin:10px;"><a href="#" onclick='copy_location(); return false;">Copy Location</a></div>
<div id="locator_map" style="width:400px;height:400px;border:1px solid #ccc;margin:10px;"><?php zm_l10n("Loading map...") ?></div>
EOT;

        $pluginPage = zm_simple_config_form($zm_google_store_locator, 'zm_store_locator_admin', 'Store Locator Setup');
        $contents = $pluginPage->getContents() . $map;
        $pluginPage->setContents($contents);
        $pluginPage->setHeader($script);

        return $pluginPage;
    }

?>
