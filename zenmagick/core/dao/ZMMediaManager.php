<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
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
 * @package net.radebatz.zenmagick.dao
 * @version $Id$
 */
class ZMMediaManager extends ZMDao {


    /**
     * Default c'tor.
     */
    function ZMMediaManager() {
        parent::__construct();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMMediaManager();
    }

    /**
     * Default d'tor.
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
        $sql = "select * from " . TABLE_MEDIA_TO_PRODUCTS . "
                where product_id = :productId";
        $sql = $this->db_->bindVars($sql, ":productId", $productId, "integer");
        $productMedia = $this->db_->Execute($sql);

        $collections = array();
        while (!$productMedia->EOF) {
            // all media collections
            $sql = "select * from " . TABLE_MEDIA_MANAGER . "
                    where media_id = :mediaId";
            $sql = $this->db_->bindVars($sql, ":mediaId", $productMedia->fields['media_id'], "integer");
            $mediaManager = $this->db_->Execute($sql);

            while (!$mediaManager->EOF) {
                $collection = $this->_newMediaCollection($mediaManager->fields);

                // all clips per collection
                $sql = "select * from " . TABLE_MEDIA_CLIPS . "
                        where media_id = :mediaId";
                $sql = $this->db_->bindVars($sql, ":mediaId", $mediaManager->fields['media_id'], "integer");
                $clip = $this->db_->Execute($sql);

                while (!$clip->EOF) {
                    // plus clip types
                    $sql = "select * from " . TABLE_MEDIA_TYPES . "
                            where type_id = :typeId";
                    $sql = $this->db_->bindVars($sql, ":typeId", $clip->fields['clip_type'], "integer");
                    $clipType = $this->db_->Execute($sql);

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


    function _newMediaCollection($fields) {
        $collection =& $this->create("MediaCollection");
        $collection->name_ = $fields['media_name'];

        return $collection;
    }


    function _newMedia($clip, $type) {
        $media =& $this->create("Media");
        $media->id_ = $clip['clip_id'];
        $media->filename_ = $clip['clip_filename'];
        $media->dateAdded_ = $clip['date_added'];
        $mediaType =& $this->create("MediaType");
        $mediaType->id_ = $type['type_id'];
        $mediaType->name_ = $type['type_name'];
        $mediaType->extension_ = $type['type_ext'];
        $media->type_ = $mediaType;

        return $media;
    }

}

?>
