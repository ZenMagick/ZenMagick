<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
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
 * Plugin for <em>product_music_info</em> product template.
 *
 * <p>This plugin contains code that previously was part of the core package.</p>
 *
 * <p>Please see the <em>Readme.txt</em> file for information on how to use the
 * included classes and code.</p>
 *
 * @author mano
 * @package org.zenmagick.plugins.musicProductInfo
 * @version $Id$
 */
class ZMMusicProductInfoPlugin extends Plugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Music Product Extras', 'Code for product_music_info product template.', '${plugin.version}');
        $this->setLoaderPolicy(ZMPlugin::LP_ALL);
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * {@inheritDoc}
     */
    public function install() {
        parent::install();
        ZMDbUtils::executePatch(file(ZMDbUtils::resolveSQLFilename($this->getPluginDirectory()."sql/install.sql")), $this->messages_);

        //TODO:
        // * configure product type
    }

    /**
     * {@inheritDoc}
     */
    public function remove($keepSettings=false) {
        parent::remove($keepSettings);
        ZMDbUtils::executePatch(file(ZMDbUtils::resolveSQLFilename($this->getPluginDirectory()."sql/remove.sql")), $this->messages_);
    }

    /**
     * {@inheritDoc}
     */
    public function init() {
        parent::init();

        ZMEvents::instance()->attach($this);

        //TODO: uhg!
        $this->addMenuItem('artists', 'Record Artists', 'record_artists.php', ZMAdminMenu::MENU_EXTRAS);
        $this->addMenuItem('company', 'Record Companies', 'record_company.php', ZMAdminMenu::MENU_EXTRAS);
        $this->addMenuItem('genre', 'Music Genre', 'music_genre.php', ZMAdminMenu::MENU_EXTRAS);
        $this->addMenuItem('mediaManager', 'Media Manager', 'medias_manager.php', ZMAdminMenu::MENU_EXTRAS);
        $this->addMenuItem('mediaTypes', 'Media Types', 'media_types.php', ZMAdminMenu::MENU_EXTRAS);

        // attach mediaUrl method to the $net toolbox tool
        ZMObject::attachMethod('mediaUrl', 'ZMToolboxNet', 
            array($this, 'mediaUrl'));

        // add mapping for this product type
        ZMUrlManager::instance()->setMapping('product_info', array(
            'product_music_info' => array('template' => 'product_music_info')
          ), false);
    }

    /**
     * {@inheritDocs}
     */
    public function onZMViewStart($args) {
        $view = $args['view'];
        if ('product_music_info' == $view->getViewId()) {
            $request = $args['request'];
            $musicManager = ZMMusicManager::instance();
            // artist information
            $artist = $musicManager->getArtistForProductId($request->getProductId(), $request->getSession()->getLanguageId());
            // musc collections for this product/artist
            $collections = $musicManager->getMediaCollectionsForProductId($request->getProductId());
            $view->setVar('musicManager', $musicManager);
            $view->setVar('artist', $artist);
            $view->setVar('collections', $collections);
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
