<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
<?php

use zenmagick\base\Toolbox;

/**
 * Plugin adding a Google Maps based store locator.
 *
 * @author mano
 * @package org.zenmagick.plugins.googleStoreLocator
 */
class ZMGoogleStoreLocatorPlugin extends Plugin {

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct('Google Store Locator', 'Google Maps Store Locator.', '${plugin.version}');
    }


    /**
     * {@inheritDoc}
     */
    public function install() {
        parent::install();

        $this->addConfigValue('Google Maps storefront key', 'storeKey', '', 'Your Google Maps key for the storefront',
              'widget@ZMTextFormWidget#name=storeKey&default=&size=24&maxlength=255');
        $this->addConfigValue('Google Maps admin key', 'adminKey', '', 'Your Google Maps key for the admin page',
              'widget@ZMTextFormWidget#name=adminKey&default=&size=24&maxlength=255');
        $this->addConfigValue('Store Location', 'location', '37.4419, -122.1419', 'The store location (Lat,Lng)');
        $this->addConfigValue('Zoom Level', 'zoom', '13', 'The initial zoom level');
        $this->addConfigValue('Marker Text', 'marker_text', ZMSettings::get('storeName'), 'Optional text for the store marker',
            'widget@ZMTextAreaFormWidget#name=marker_text');
        $this->addConfigValue('Add Controls', 'controls', 'true', 'Enable/disable map controls',
            'widget@ZMBooleanFormWidget#name=controls&default=true&label=Enable controls&style=checkbox');
    }

    /**
     * {@inheritDoc}
     */
    public function init() {
        parent::init();
        zenmagick\base\Runtime::getEventDispatcher()->listen($this);
        // add admin page
        $this->addMenuItem2(_zm('Google Store Locator'), 'googleStoreLocatorAdmin');
    }

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
