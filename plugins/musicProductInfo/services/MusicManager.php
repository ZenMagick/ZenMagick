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
namespace ZenMagick\plugins\musicProductInfo\services;

use ZenMagick\Base\Runtime;
use ZenMagick\Base\ZMObject;
use ZenMagick\plugins\musicProductInfo\model\Artist;
use ZenMagick\plugins\musicProductInfo\model\MediaCollection;
use ZenMagick\plugins\musicProductInfo\model\MediaItem;
use ZenMagick\plugins\musicProductInfo\model\MediaType;
use ZenMagick\plugins\musicProductInfo\model\RecordCompany;


/**
 * Music manager.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class MusicManager extends ZMObject {

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $mappings = array(
            'media_clips' => array(
                'mediaItemId' => array('column' => 'clip_id', 'type' => 'integer', 'key' => true, 'auto' => true),
                'mediaId' => array('column' => 'media_id', 'type' => 'integer'),
                'mediaTypeId' => array('column' => 'clip_type', 'type' => 'integer'),
                'filename' => array('column' => 'clip_filename', 'type' => 'string'),
                'dateAdded' => array('column' => 'date_added', 'type' => 'datetime'),
                'lastModified' => array('column' => 'last_modified', 'type' => 'datetime')
            ),
            'media_manager' => array(
                'collectionId' => array('column' => 'media_id', 'type' => 'integer', 'key' => true, 'auto' => true),
                'name' => array('column' => 'media_name', 'type' => 'string'),
                'lastModified' => array('column' => 'last_modified', 'type' => 'datetime'),
                'dateAdded' => array('column' => 'date_added', 'type' => 'datetime')
            ),
            'media_to_products' => array(
                'mediaId' => array('column' => 'media_id', 'type' => 'integer'),
                'productId' => array('column' => 'product_id', 'type' => 'integer')
            ),
            'media_types' => array(
                'mediaTypeId' => array('column' => 'type_id', 'type' => 'integer', 'key' => true, 'auto' => true),
                'name' => array('column' => 'type_name', 'type' => 'string'),
                'extentsion' => array('column' => 'type_ext', 'type' => 'string')
            ),
            'music_genre' => array(
                'genreId' => array('column' => 'music_genre_id', 'type' => 'integer', 'key' => true, 'auto' => true),
                'name' => array('column' => 'music_genre_name', 'type' => 'string'),
                'dateAdded' => array('column' => 'date_added', 'type' => 'datetime'),
                'lastModified' => array('column' => 'last_modified', 'type' => 'datetime')
            ),
            'product_music_extra' => array(
                'productId' => array('column' => 'products_id', 'type' => 'integer', 'key' => true),
                'artistId' => array('column' => 'artists_id', 'type' => 'integer'),
                'recordCompanyId' => array('column' => 'record_company_id', 'type' => 'integer'),
                'genreId' => array('column' => 'music_genre_id', 'type' => 'integer')
            ),
            'record_artists' => array(
                'artistId' => array('column' => 'artists_id', 'type' => 'integer', 'key' => true, 'auto' => true),
                'name' => array('column' => 'artists_name', 'type' => 'string'),
                'image' => array('column' => 'artists_image', 'type' => 'string'),
                'dateAdded' => array('column' => 'date_added', 'type' => 'datetime'),
                'lastModified' => array('column' => 'last_modified', 'type' => 'datetime')
            ),
            'record_artists_info' => array(
                'artistId' => array('column' => 'artists_id', 'type' => 'integer', 'key' => true),
                'languageId' => array('column' => 'languages_id', 'type' => 'integer', 'key' => true),
                'url' => array('column' => 'artists_url', 'type' => 'string'),
                'urlClicked' => array('column' => 'url_clicked', 'type' => 'integer'),
                'dateLastClick' => array('column' => 'date_last_click', 'type' => 'datetime')
            ),
            'record_company' => array(
                'recordCompanyId' => array('column' => 'record_company_id', 'type' => 'integer', 'key' => true, 'auto' => true),
                'name' => array('column' => 'record_company_name', 'type' => 'string'),
                'image' => array('column' => 'record_company_image', 'type' => 'string'),
                'dateAdded' => array('column' => 'date_added', 'type' => 'datetime'),
                'lastModified' => array('column' => 'last_modified', 'type' => 'datetime')
            ),
            'record_company_info' => array(
                'recordCompanyId' => array('column' => 'record_company_id', 'type' => 'integer', 'key' => true),
                'languageId' => array('column' => 'languages_id', 'type' => 'integer', 'key' => true),
                'url' => array('column' => 'record_company_url', 'type' => 'string'),
                'urlClicked' => array('column' => 'url_clicked', 'type' => 'integer'),
                'dateLastClick' => array('column' => 'date_last_click', 'type' => 'datetime')
            )
        );
        foreach ($mappings as $table => $mapping) {
            \ZMRuntime::getDatabase()->getMapper()->setMappingForTable($table, $mapping);
        }
    }


    /**
     * Get all available media for the given product (id).
     *
     * @param int productId The product id.
     * @return array A list of <code>MediaCollection</code> instances.
     */
    public function getMediaCollectionsForProductId($productId) {
        // get all media for the given product (id)
        $sql = "SELECT media_id FROM %table.media_to_products% WHERE product_id = :productId";
        $productMediaIdList = \ZMRuntime::getDatabase()->fetchAll($sql, array('productId' => $productId), 'media_to_products');

        $collections = array();
        foreach ($productMediaIdList as $mediaId) {
            // all media collections
            $sql = "SELECT * FROM %table.media_manager% WHERE media_id = :collectionId";
            $args = array('collectionId' => $mediaId['mediaId']);
            foreach (\ZMRuntime::getDatabase()->fetchAll($sql, $args, 'media_manager', 'ZenMagick\plugins\musicProductInfo\model\MediaCollection') as $collection) {
                // populate collection
                $sql = "SELECT * FROM %table.media_clips% WHERE media_id = :mediaId";
                foreach (\ZMRuntime::getDatabase()->fetchAll($sql, array('mediaId' => $mediaId['mediaId']), 'media_clips', 'ZenMagick\plugins\musicProductInfo\model\MediaItem') as $mediaItem) {
                    // plus clip types
                    $sql = "SELECT * FROM %table.media_types% WHERE type_id = :mediaTypeId";
                    $args = array('mediaTypeId' => $mediaItem->getMediaTypeId());
                    // maybe null
                    $mediaType = \ZMRuntime::getDatabase()->querySingle($sql, $args, 'media_types', 'ZenMagick\plugins\musicProductInfo\model\MediaType');
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
     * @return Artist Artist information or <code>null</code>.
     */
    public function getArtistForProductId($productId, $languageId) {
        $sql = "SELECT * FROM %table.product_music_extra% WHERE products_id = :productId";
        $extraInfo = \ZMRuntime::getDatabase()->querySingle($sql, array('productId' => $productId), 'product_music_extra');

        $sql = "SELECT * FROM %table.record_artists% WHERE artists_id = :artistId";
        $artist = \ZMRuntime::getDatabase()->querySingle($sql, array('artistId' => $extraInfo['artistId']), 'record_artists', 'ZenMagick\plugins\musicProductInfo\model\Artist');

        if (null == $artist) {
            return null;
        }

        $sql = "SELECT * FROM %table.record_artists_info% WHERE artists_id = :artistId AND languages_id = :languageId";
        $args = array('artistId' => $artist->getArtistId(), 'languageId' => $languageId);
        $artistInfo = \ZMRuntime::getDatabase()->querySingle($sql, $args, 'record_artists_info');
        $artist->setUrl($artistInfo['url']);

        $sql = "SELECT * FROM %table.record_company% WHERE record_company_id = :recordCompanyId";
        $args = array('recordCompanyId' => $extraInfo['recordCompanyId']);
        $recordCompany = \ZMRuntime::getDatabase()->querySingle($sql, $args, 'record_company', 'ZenMagick\plugins\musicProductInfo\model\RecordCompany');

        if (null != $recordCompany) {
            $sql = "SELECT * FROM %table.record_company_info% WHERE record_company_id = :recordCompanyId AND languages_id = :languageId";
            $args = array('recordCompanyId' => $recordCompany->getRecordCompanyId(), 'languageId' => $languageId);
            $recordCompanyInfo = \ZMRuntime::getDatabase()->querySingle($sql, $args, 'record_company_info');
            $recordCompany->setUrl($recordCompanyInfo['url']);
            $artist->setRecordCompany($recordCompany);
        }

        $sql = "SELECT * FROM %table.music_genre% WHERE music_genre_id = :genreId";
        $genre = \ZMRuntime::getDatabase()->querySingle($sql, array('genreId' => $extraInfo['genreId']), 'music_genre');
        if ($genre) {
            $artist->setGenre($genre['name']);
        }

        return $artist;
    }

}
