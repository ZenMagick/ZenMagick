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

use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;


/**
 * Music manager.
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.zm_product_music_info.service
 */
class ZMMusicManager extends ZMObject {


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $mappings = array(
            'media_clips' => array(
                'mediaItemId' => 'column=clip_id;type=integer;key=true;auto=true',
                'mediaId' => 'column=media_id;type=integer',
                'mediaTypeId' => 'column=clip_type;type=integer',
                'filename' => 'column=clip_filename;type=string',
                'dateAdded' => 'column=date_added;type=datetime',
                'lastModified' => 'column=last_modified;type=datetime'
            ),
            'media_manager' => array(
                'collectionId' => 'column=media_id;type=integer;key=true;auto=true',
                'name' => 'column=media_name;type=string',
                'lastModified' => 'column=last_modified;type=datetime',
                'dateAdded' => 'column=date_added;type=datetime'
            ),
            'media_to_products' => array(
                'mediaId' => 'column=media_id;type=integer',
                'productId' => 'column=product_id;type=integer'
            ),
            'media_types' => array(
                'mediaTypeId' => 'column=type_id;type=integer;key=true;auto=true',
                'name' => 'column=type_name;type=string',
                'extentsion' => 'column=type_ext;type=string'
            ),
            'music_genre' => array(
                'genreId' => 'column=music_genre_id;type=integer;key=true;auto=true',
                'name' => 'column=music_genre_name;type=string',
                'dateAdded' => 'column=date_added;type=datetime',
                'lastModified' => 'column=last_modified;type=datetime'
            ),
            'product_music_extra' => array(
                'productId' => 'column=products_id;type=integer;key=true',
                'artistId' => 'column=artists_id;type=integer',
                'recordCompanyId' => 'column=record_company_id;type=integer',
                'genreId' => 'column=music_genre_id;type=integer'
            ),
            'record_artists' => array(
                'artistId' => 'column=artists_id;type=integer;key=true;auto=true',
                'name' => 'column=artists_name;type=string',
                'image' => 'column=artists_image;type=string',
                'dateAdded' => 'column=date_added;type=datetime',
                'lastModified' => 'column=last_modified;type=datetime'
            ),
            'record_artists_info' => array(
                'artistId' => 'column=artists_id;type=integer;key=true',
                'languageId' => 'column=languages_id;type=integer;key=true',
                'url' => 'column=artists_url;type=string',
                'urlClicked' => 'column=url_clicked;type=integer',
                'dateLastClick' => 'column=date_last_click;type=datetime'
            ),
            'record_company' => array(
                'recordCompanyId' => 'column=record_company_id;type=integer;key=true;auto=true',
                'name' => 'column=record_company_name;type=string',
                'image' => 'column=record_company_image;type=string',
                'dateAdded' => 'column=date_added;type=datetime',
                'lastModified' => 'column=last_modified;type=datetime'
            ),
            'record_company_info' => array(
                'recordCompanyId' => 'column=record_company_id;type=integer;key=true',
                'languageId' => 'column=languages_id;type=integer;key=true',
                'url' => 'column=record_company_url;type=string',
                'urlClicked' => 'column=url_clicked;type=integer',
                'dateLastClick' => 'column=date_last_click;type=datetime'
            )
        );
        foreach ($mappings as $table => $mapping) {
            ZMDbTableMapper::instance()->setMappingForTable($table, $mapping);
        }
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Get instance.
     */
    public static function instance() {
        return Runtime::getContainer()->get('musicManager');
    }


    /**
     * Get all available media for the given product (id).
     *
     * @param int productId The product id.
     * @return array A list of <code>ZMMediaCollection</code> instances.
     */
    public function getMediaCollectionsForProductId($productId) {
        // get all media for the given product (id)
        $sql = "SELECT media_id FROM " . TABLE_MEDIA_TO_PRODUCTS . " WHERE product_id = :productId";
        $productMediaIdList = ZMRuntime::getDatabase()->query($sql, array('productId' => $productId), TABLE_MEDIA_TO_PRODUCTS);

        $collections = array();
        foreach ($productMediaIdList as $mediaId) {
            // all media collections
            $sql = "SELECT * FROM " . TABLE_MEDIA_MANAGER . " WHERE media_id = :collectionId";
            $args = array('collectionId' => $mediaId['mediaId']);
            foreach (ZMRuntime::getDatabase()->query($sql, $args, TABLE_MEDIA_MANAGER, 'ZMMediaCollection') as $collection) {
                // populate collection
                $sql = "SELECT * FROM " . TABLE_MEDIA_CLIPS . " WHERE media_id = :mediaId";
                foreach (ZMRuntime::getDatabase()->query($sql, array('mediaId' => $mediaId['mediaId']), TABLE_MEDIA_CLIPS, 'ZMMediaItem') as $mediaItem) {
                    // plus clip types
                    $sql = "SELECT * FROM " . TABLE_MEDIA_TYPES . " WHERE type_id = :mediaTypeId";
                    $args = array('mediaTypeId' => $mediaItem->getMediaTypeId());
                    // maybe null
                    $mediaType = ZMRuntime::getDatabase()->querySingle($sql, $args, TABLE_MEDIA_TYPES, 'ZMMediaType');
                    $mediaItem->setType($mediaType);
                    $collection->addItem($mediaItem);
                }
                $collections[] = $collection;
            }
        }

        return $collections;
    }

    /**
     * Retrieve artist information for the given product.
     *
     * @param int productId The product id.
     * @param int languageId The language id.
     * @return ZMArtist Artist information or <code>null</code>.
     */
    public function getArtistForProductId($productId, $languageId) {
        $sql = "SELECT * FROM " . TABLE_PRODUCT_MUSIC_EXTRA . " WHERE products_id = :productId";
        $extraInfo = ZMRuntime::getDatabase()->querySingle($sql, array('productId' => $productId), TABLE_PRODUCT_MUSIC_EXTRA);

        $sql = "SELECT * FROM " . TABLE_RECORD_ARTISTS . " WHERE artists_id = :artistId";
        $artist = ZMRuntime::getDatabase()->querySingle($sql, array('artistId' => $extraInfo['artistId']), TABLE_RECORD_ARTISTS, 'ZMArtist');

        if (null == $artist) {
            return null;
        }

        $sql = "SELECT * FROM " . TABLE_RECORD_ARTISTS_INFO . " WHERE artists_id = :artistId AND languages_id = :languageId";
        $args = array('artistId' => $artist->getArtistId(), 'languageId' => $languageId);
        $artistInfo = ZMRuntime::getDatabase()->querySingle($sql, $args, TABLE_RECORD_ARTISTS_INFO);
        $artist->setUrl($artistInfo['url']);

        $sql = "SELECT * FROM " . TABLE_RECORD_COMPANY . " WHERE record_company_id = :recordCompanyId";
        $args = array('recordCompanyId' => $extraInfo['recordCompanyId']);
        $recordCompany = ZMRuntime::getDatabase()->querySingle($sql, $args, TABLE_RECORD_COMPANY, 'ZMRecordCompany');

        if (null != $recordCompany) {
            $sql = "SELECT * FROM " . TABLE_RECORD_COMPANY_INFO . " WHERE record_company_id = :recordCompanyId AND languages_id = :languageId";
            $args = array('recordCompanyId' => $recordCompany->getRecordCompanyId(), 'languageId' => $languageId);
            $recordCompanyInfo = ZMRuntime::getDatabase()->querySingle($sql, $args, TABLE_RECORD_COMPANY_INFO);
            $recordCompany->setUrl($recordCompanyInfo['url']);
            $artist->setRecordCompany($recordCompany);
        }

        $sql = "SELECT * FROM " . TABLE_MUSIC_GENRE . " WHERE music_genre_id = :genreId";
        $genre = ZMRuntime::getDatabase()->querySingle($sql, array('genreId' => $extraInfo['genreId']), TABLE_MUSIC_GENRE);
        if ($genre) {
            $artist->setGenre($genre['name']);
        }

        return $artist;
    }

}
