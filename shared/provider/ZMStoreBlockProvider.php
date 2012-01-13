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

use zenmagick\base\ZMObject;

use zenmagick\http\blocks\BlockProvider;

/**
 * Store block provider.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.provider
 */
class ZMStoreBlockProvider extends ZMObject implements BlockProvider {

    /**
     * {@inheritDoc}
     */
    public function getBlockList($args=array()) {
        $blocks = array();

        $templateManager = $this->container->get('templateManager');
        // sideboxes
        if ($templateManager->isLeftColEnabled()) {
            foreach ($templateManager->getLeftColBoxNames() as $boxName) {
                // avoid duplicates by using $box as key
                $blocks[$boxName] = 'blockWidget#template=boxes/'.$boxName.'&title='.ucwords(str_replace(array('.php', '_'), array('', ' '), $boxName));
            }
        }
        if ($templateManager->isRightColEnabled()) {
            foreach ($templateManager->getRightColBoxNames() as $boxName) {
                // avoid duplicates by using $box as key
                $blocks[$boxName] = 'blockWidget#template=boxes/'.$boxName.'&title='.ucwords(str_replace(array('.php', '_'), array('', ' '), $boxName));
            }
        }

        // banners
        $blocks['ZMBannerBlockWidget'] = 'ZMBannerBlockWidget';

        // paypal ec button
        $blocks['ZMPayPalECButtonBlockWidget'] = 'ZMPayPalECButtonBlockWidget';

        return $blocks;
    }

}
