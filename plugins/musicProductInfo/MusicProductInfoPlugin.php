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
namespace ZenMagick\plugins\musicProductInfo;

use ZenMagick\Base\Plugins\Plugin;
use ZenMagick\Base\Runtime;
use ZenMagick\Base\ZMObject;

/**
 * Plugin for <em>product_music_info</em> product template.
 *
 * <p>This plugin contains code that previously was part of the core package.</p>
 *
 * <p>Please see the <em>Readme.txt</em> file for information on how to use the
 * included classes and code.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class MusicProductInfoPlugin extends Plugin {

    /**
     * {@inheritDoc}
     */
    public function remove($keepSettings=false) {
        //parent::remove($keepSettings);
        $conn = \ZMRuntime::getDatabase();
        $sm = $conn->getSchemaManager();
        $sm->dropTable($conn->getPrefix().'media_clips');
        $sm->dropTable($conn->getPrefix().'media_manager');
        $sm->dropTable($conn->getPrefix().'media_to_products');
        $sm->dropTable($conn->getPrefix().'media_types');
        $sm->dropTable($conn->getPrefix().'music_genre');
        $sm->dropTable($conn->getPrefix().'product_music_extra');
        $sm->dropTable($conn->getPrefix().'record_artists');
        $sm->dropTable($conn->getPrefix().'record_artists_info');
        $sm->dropTable($conn->getPrefix().'record_company');
        $sm->dropTable($conn->getPrefix().'record_company_info');
    }

    /**
     * Attach mediaUrl method to toolbox.
     */
    public function onContainerReady($event) {
        // attach mediaUrl method to the $net toolbox tool
        ZMObject::attachMethod('mediaUrl', 'ZenMagick\StoreBundle\Toolbox\ToolboxNet',
            array($this, 'mediaUrl'));
    }

    /**
     * Update route.
     */
    public function onDispatchStart($event) {
        if (null != ($route = $this->container->get('routeResolver')->getRouteForId('product_info'))) {
            $route->addOptions(array('view:product_music_info' => 'views/product_music_info.php'));
        }
    }

    /**
     * Event handler.
     */
    public function onViewStart($event) {
        $view = $event->get('view');
        $request = $event->get('request');
        if ('product_info' == $request->getRequestId()) {
            $musicManager = $this->container->get('musicManager');
            // artist information
            $artist = $musicManager->getArtistForProductId($request->query->get('productId'), $request->getSession()->getLanguageId());
            // music collections for this product/artist
            $collections = $musicManager->getMediaCollectionsForProductId($request->query->get('productId'));
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
        $url = 'media/'.$filename;
        return $url;
    }

}
