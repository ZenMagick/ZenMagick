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
 */
?>
<?php

/**
 * Allow geo IP mapping and actions based on that.
 *
 * @package org.zenmagick.plugins.zm_geo_ip
 * @author mano
 * @version $Id$
 */
class zm_geo_ip extends Plugin {
    private $gi;
    private $type;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Geo IP mapping', 'Allow to resolve a users IP address to a geographic location', '${plugin.version}');
        $this->setLoaderPolicy(ZMPlugin::LP_FOLDER);
        $this->gi = null;
        $this->type = null;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Install this plugin.
     */
    function install() {
        parent::install();

        $this->addConfigValue('Database', 'database', 'GeoIP.dat', 'Database filename; can be either relative to the plugin or absolute');
        $this->addConfigValue('Licence Key', 'licenceKey', '', 'Optional licence key for realtime lookups');
        $this->addConfigValue('Shared Memory', 'shm', 'false', 'Enable/disable use of shared memory', 'zen_cfg_select_option(array(\'true\',\'false\'),');
    }


    /**
     * {@inheritDoc}
     */
    public function init() {
        parent::init();

        ZMEvents::instance()->attach($this);

        $database = $this->get('database');
        if (!file_exists($database)) {
            $database = $this->getConfigPath($database);
        }
        if (file_exists($database)) {
            // decide type based on filename
            $this->type = basename($database);

            $flags = GEOIP_STANDARD | GEOIP_MEMORY_CACHE;
            if (ZMLangUtils::asBoolean($this->get('shm'))) {
                geoip_load_shared_mem($database);
                $flags = GEOIP_SHARED_MEMORY;
            }
            $this->gi = geoip_open($database, $flags);
        }

        // register tests
        if (null != ($tests = ZMPlugins::instance()->getPluginForId('zm_tests'))) {
            // add class path only now to avoid errors due to missing ZMTestCase
            ZMLoader::instance()->addPath($this->getPluginDirectory().'tests/');
            $tests->addTest('TestGeoIP');
        }
    }

    /**
     * Shutdown handler.
     *
     * @param array args Optional parameter.
     */
    public function onZMAllDone($args=array()) {
        if (null !== $this->gi) {
            geoip_close($this->gi);
        }
    }


    /**
     * Query IP address.
     *
     * @param string ip The ip address.
     * @return ZMObject Data object or <code>null</code>.
     */
    public function lookup($ip) {
        if (!$this->isEnabled() || null === $this->gi) {
            return null;
        }

        //TODO: make type detection smarter
        $result = new ZMObject();
        if ('GeoIP.dat' == $this->type) {
            $code = geoip_country_code_by_addr($this->gi, $ip);
            $name = geoip_country_name_by_addr($this->gi, $ip);
            $result->set('countryCode', $code);
            $result->set('country', $name);
        } else if (false !== strpos($this->type, 'City')) {
            $r = geoip_record_by_addr($this->gi, $ip);
            $result->set('countryCode', $r->country_code);
            $result->set('countryCode3', $r->country_code3);
            $result->set('country', $r->country_name);
            $result->set('region', $GEOIP_REGION_NAME[$r->country_code][$r->region]);
            $result->set('city', $r->city);
            $result->set('postcode', $r->postal_code);
            $result->set('latitude', $r->latitude);
            $result->set('longitude', $r->longitude);
            $result->set('dmaCode', $r->dma_code);
            $result->set('areaCode', $r->area_code);
        } else if (false !== strpos($this->type, 'Region')) {
            list ($code3, $region) = geoip_region_by_addr($this->gi, $ip);
            $result->set('code3', $code3);
            $result->set('regionCode', $region);
            $result->set('region', $GEOIP_REGION_NAME[$code3][$region]);
        } else if (!ZMLangUtils::isEmpty($this->get('licenceKey'))) {
            $str = getdnsattributes($this->get('licenceKey'), $ip);
            $r = getrecordwithdnsservice($str);
            $result->set('countryCode', $r->country_code);
            $result->set('countryCode3', $r->country_code3);
            $result->set('country', $r->country_name);
            $result->set('region', $r->regionname);
            $result->set('regionCode', $r->region);
            $result->set('city', $r->city);
            $result->set('postcode', $r->postal_code);
            $result->set('latitude', $r->latitude);
            $result->set('longitude', $r->longitude);
            $result->set('dmaCode', $r->dma_code);
            $result->set('areaCode', $r->area_code);
            $result->set('isp', $r->isp);
            $result->set('org', $r->org);
        }

        return $result;
    }

}

?>
