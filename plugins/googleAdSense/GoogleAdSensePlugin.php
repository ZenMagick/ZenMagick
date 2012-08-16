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
 *
 */
namespace zenmagick\plugins\googleAdSense;

use zenmagick\apps\store\plugins\Plugin;

/**
 * Plugin providing functionallity to manage Google AdSense content, including four sideboxes.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class GoogleAdSensePlugin extends Plugin {
    const ADSENSE_PREFIX = 'adsense-';

    /**
     * Install this plugin.
     */
    public function install() {
        parent::install();
        $totalAds = $this->container->get('settingsService')->get('plugins.googleAdSense.totalAds', 6);
        for ($ii=1; $ii <= $totalAds; ++$ii) {
            $name = self::ADSENSE_PREFIX.$ii;
            $this->addConfigValue('Google AdSense JavaScript #'.$ii, $name, '',
              'The JavaScript provided by Google to display your ads for box #'.$ii,
              'widget@textAreaFormWidget#name='.$name);
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
