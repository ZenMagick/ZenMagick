<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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

use zenmagick\base\ZMObject;

/**
 * Plugin for <em>product_music_info</em> product template.
 *
 * <p>This plugin contains code that previously was part of the core package.</p>
 *
 * <p>Please see the <em>Readme.txt</em> file for information on how to use the
 * included classes and code.</p>
 *
 * @author mano
 * @package org.zenmagick.plugins.musicProductInfo
 */
class ZMMusicProductInfoPlugin extends Plugin {

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct('Music Product Extras', 'Code for product_music_info product template.', '${plugin.version}');
    }


    /**
     * {@inheritDoc}
     */
    public function install() {
        parent::install();
        ZMDbUtils::executePatch(file(ZMDbUtils::resolveSQLFilename($this->getPluginDirectory()."/sql/install.sql")), $this->messages_);

        //TODO:
        // * configure product type
    }

    /**
     * {@inheritDoc}
     */
    public function remove($keepSettings=false) {
        parent::remove($keepSettings);
        ZMDbUtils::executePatch(file(ZMDbUtils::resolveSQLFilename($this->getPluginDirectory()."/sql/remove.sql")), $this->messages_);
    }

    /**
     * {@inheritDoc}
     */
    public function init() {
        parent::init();

        zenmagick\base\Runtime::getEventDispatcher()->listen($this);

        // attach mediaUrl method to the $net toolbox tool
        ZMObject::attachMethod('mediaUrl', 'ZMToolboxNet',
            array($this, 'mediaUrl'));

        // add mapping for this product type
        ZMUrlManager::instance()->setMapping('product_info', array(
            'product_music_info' => array('template' => 'views/product_music_info.php')
          ), false);
    }

    /**
     * Event handler.
     */
    public function onViewStart($event) {
        $view = $event->get('view');
        $request = $event->get('request');
        if ('product_music_info' == $request->getRequestId()) {
            $musicManager = ZMMusicManager::instance();
            // artist information
            $artist = $musicManager->getArtistForProductId($request->getProductId(), $request->getSession()->getLanguageId());
            // musc collections for this product/artist
            $collections = $musicManager->getMediaCollectionsForProductId($request->getProductId());
            $view->setVariable('musicManager', $musicManager);
            $view->setVariable('artist', $artist);
            $view->setVariable('collections', $collections);
        }
    }

    /**
     * Build a media URL.
     *
     * @param string filename The media filename, relative to the media folder.
     * @return A URL.
     */
    public function mediaUrl($tool, $filename) {
        $url = DIR_WS_MEDIA.$filename;
        return $url;
    }

}
