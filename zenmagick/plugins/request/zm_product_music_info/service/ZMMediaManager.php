<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
 * Media manager.
 *
 * @author mano
 * @package org.zenmagick.plugins.zm_product_music_info.service
 * @version $Id: ZMMediaManager.php 177 2007-05-19 12:01:32Z DerManoMann $
 */
class ZMMediaManager extends ZMObject {


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get all available media for the given product (id).
     *
     * @param int productId The product id.
     * @return array A list of <code>ZMMedia</code> instances.
     */
    function getMediaCollectionsForProductId($productId) {
        // media for product
        $db = ZMRuntime::getDB();
        $sql = "select * from " . TABLE_MEDIA_TO_PRODUCTS . "
                where product_id = :productId";
        $sql = $db->bindVars($sql, ":productId", $productId, "integer");
        $productMedia = $db->Execute($sql);

        $collections = array();
        while (!$productMedia->EOF) {
            // all media collections
            $sql = "select * from " . TABLE_MEDIA_MANAGER . "
                    where media_id = :mediaId";
            $sql = $db->bindVars($sql, ":mediaId", $productMedia->fields['media_id'], "integer");
            $mediaManager = $db->Execute($sql);

            while (!$mediaManager->EOF) {
                $collection = $this->_newMediaCollection($mediaManager->fields);

                // all clips per collection
                $sql = "select * from " . TABLE_MEDIA_CLIPS . "
                        where media_id = :mediaId";
                $sql = $db->bindVars($sql, ":mediaId", $mediaManager->fields['media_id'], "integer");
                $clip = $db->Execute($sql);

                while (!$clip->EOF) {
                    // plus clip types
                    $sql = "select * from " . TABLE_MEDIA_TYPES . "
                            where type_id = :typeId";
                    $sql = $db->bindVars($sql, ":typeId", $clip->fields['clip_type'], "integer");
                    $clipType = $db->Execute($sql);

                    $media = $this->_newMedia($clip->fields, $clipType->fields);
                    Array_push($collection->items_, $media);
                    $clip->MoveNext();
                }
                $mediaManager->MoveNext();
                array_push($collections, $collection);
            }

            $productMedia->MoveNext();
        }

        return $collections;
    }


    /**
     * Create new media collection instance.
     */
    function _newMediaCollection($fields) {
        $collection = ZMLoader::make("MediaCollection");
        $collection->name_ = $fields['media_name'];

        return $collection;
    }

    /**
     * Create new media instance.
     */
    function _newMedia($clip, $type) {
        $media = ZMLoader::make("Media");
        $media->id_ = $clip['clip_id'];
        $media->filename_ = $clip['clip_filename'];
        $media->dateAdded_ = $clip['date_added'];
        $mediaType = ZMLoader::make("MediaType");
        $mediaType->id_ = $type['type_id'];
        $mediaType->name_ = $type['type_name'];
        $mediaType->extension_ = $type['type_ext'];
        $media->type_ = $mediaType;

        return $media;
    }

}

?>
