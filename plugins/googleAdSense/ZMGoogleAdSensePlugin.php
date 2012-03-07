<?php
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
 *
 */


/**
 * Plugin providing functionallity to manage Google AdSense content, including four sideboxes.
 *
 * @author mano
 * @package org.zenmagick.plugins.googleAdSense
 */
class ZMGoogleAdSensePlugin extends Plugin {
    const ADSENSE_PREFIX = 'adsense-';
    private $totalAds_;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct('Google AdSense', 'Plugin to manage Google AdSense JS (including four sideboxes).', '${plugin.version}');
        $this->totalAds_ = ZMSettings::get('plugins.googleAdSense.totalAds', 6);
    }


    /**
     * Install this plugin.
     */
    public function install() {
        parent::install();

        for ($ii=1; $ii <= $this->totalAds_; ++$ii) {
            $name = self::ADSENSE_PREFIX.$ii;
            $this->addConfigValue('Google AdSense JavaScript #'.$ii, $name, '',
              'The JavaScript provided by Google to display your ads for box #'.$ii,
              'widget@ZMTextAreaFormWidget#name='.$name);
        }
    }

    /**
     * Get Google AdSense (JavaScript) code for the given index.
     *
     * @param int index The ad index.
     * @return string The configured AdSense JavaScript.
     */
    public function getAd($index) {
        $js = stripslashes($this->get(self::ADSENSE_PREFIX.$index));
        return $js;
    }

}
