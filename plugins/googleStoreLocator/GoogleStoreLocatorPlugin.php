<?php
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
namespace ZenMagick\plugins\googleStoreLocator;

use ZenMagick\apps\store\plugins\Plugin;
use ZenMagick\Base\Runtime;
use ZenMagick\Base\Toolbox;

/**
 * Plugin adding a Google Maps based store locator.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class GoogleStoreLocatorPlugin extends Plugin {

    /**
     * Event callback to add required JS.
     */
    public function onFinaliseContent($event) {
        $request = $event->get('request');

        if ('store_locator' == $request->getRequestId()) {
            $storeKey = $this->get('storeKey');
            $location = $this->get('location');
            $zoom = $this->get('zoom');
            $markerText = $this->get('marker_text');
            $controls = Toolbox::asBoolean($this->get('controls'));

            $script = '
<script type="text/javascript" src="http://www.google.com/jsapi?key='.$storeKey.'"></script>
<script type="text/javascript">
  google.load("maps", "2.x");
  function load_locator_map() {
    var map = new GMap2(document.getElementById("locator_map"));
    '.($controls ? 'map.addControl(new GLargeMapControl());/*map.addControl(new GMapTypeControl());*/' : '').'
    map.setCenter(new GLatLng('.$location.'), '.$zoom.');
    var marker = new GMarker(map.getCenter());
    map.addOverlay(marker);
    '.(!empty($markerText) ? 'marker.openInfoWindowHtml("'.$markerText.'")' : '').'
  }
  google.setOnLoadCallback(load_locator_map);
</script>
';
            $content = $event->get('content');
            $content = preg_replace('/<\/body>/', $script.'</body>', $content, 1);
            $event->set('content', $content);
        }
    }

}
