<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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
 * Store block provider.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.provider
 */
class ZMStoreBlockProvider implements ZMBlockProvider {

    /**
     * {@inheritDoc}
     */
    public function getBlockList($args=array()) {
        $blocks = array();

        // sideboxes
        if (ZMTemplateManager::instance()->isLeftColEnabled()) {
            foreach (ZMTemplateManager::instance()->getLeftColBoxNames() as $boxName) {
                // avoid duplicates by using $box as key
                $blocks[$boxName] = 'BlockWidget#template=boxes/'.$boxName.'&title='.ucwords(str_replace(array('.php', '_'), array('', ' '), $boxName));
            }
        }
        if (ZMTemplateManager::instance()->isRightColEnabled()) {
            foreach (ZMTemplateManager::instance()->getRightColBoxNames() as $boxName) {
                // avoid duplicates by using $box as key
                $blocks[$boxName] = 'BlockWidget#template=boxes/'.$boxName.'&title='.ucwords(str_replace(array('.php', '_'), array('', ' '), $boxName));
            }
        }

        // banners
        $blocks['BannerBlockWidget'] = 'BannerBlockWidget';

        return $blocks;
    }

}
