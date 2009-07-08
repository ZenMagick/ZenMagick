<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 * Optional music stuff.
 *
 * @author mano
 * @package org.zenmagick.plugins.zm_product_music_info.service
 * @version $Id$
 */
class ZMMusic extends ZMObject {

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
     * Retreive artist information for the given product.
     *
     * @param int productId The product id.
     * @return Artist Artist information.
     */
    function getArtistForProductId($productId) {
        $session = ZMRequest::instance()->getSession();

        $db = Runtime::getDB();
        $sql = "select * from " . TABLE_PRODUCT_MUSIC_EXTRA . "
                where products_id = :productId";
        $sql = $db->bindVars($sql, ":productId", $productId, "integer");
        $extra = $db->Execute($sql);

        $sql = "select * from " . TABLE_RECORD_ARTISTS . "
                where artists_id = :artistId";
        $sql = $db->bindVars($sql, ":artistId", $extra->fields['artists_id'], "integer");
        $artist = $db->Execute($sql);

        $sql = "select * from " . TABLE_RECORD_ARTISTS_INFO . "
                where artists_id = :artistId
                and languages_id = :languageId";
        $sql = $db->bindVars($sql, ":artistId", $extra->fields['artists_id'], "integer");
        $sql = $db->bindVars($sql, ":languageId", $session->getLanguageId(), "integer");
        $artistInfo = $db->Execute($sql);

        $sql = "select * from " . TABLE_RECORD_COMPANY . "
                where record_company_id = :recordCompanyId";
        $sql = $db->bindVars($sql, ":recordCompanyId", $extra->fields['record_company_id'], "integer");
        $recordCompany = $db->Execute($sql);

        $sql = "select * from " . TABLE_RECORD_COMPANY_INFO . "
                where record_company_id = :recordCompanyId
                and languages_id = :languageId";
        $sql = $db->bindVars($sql, ":recordCompanyId", $extra->fields['record_company_id'], "integer");
        $sql = $db->bindVars($sql, ":languageId", $session->getLanguageId(), "integer");
        $recordCompanyInfo = $db->Execute($sql);

        $sql = "select * from " . TABLE_MUSIC_GENRE . "
                where music_genre_id = :genreId";
        $sql = $db->bindVars($sql, ":genreId", $extra->fields['music_genre_id'], "integer");
        $musicGenre = $db->Execute($sql);

        $theArtist = ZMLoader::make("Artist");
        $theArtist->id_ = $artist->fields['artists_id'];
        $theArtist->name_ = $artist->fields['artists_name'];
        $theArtist->genre_ = $musicGenre->fields['music_genre_name'];
        $theArtist->image_ = $artist->fields['artists_image'];
        $theArtist->url_ = $artistInfo->fields['artists_url'];

        $theRecordCompany = ZMLoader::make("RecordCompany");
        $theRecordCompany->id_ = $extra->fields['record_company_id'];
        $theRecordCompany->name_ = $recordCompanyInfo->fields['record_company_name'];
        $theRecordCompany->url_ = $recordCompanyInfo->fields['record_company_url'];

        $theArtist->recordCompany_ = $theRecordCompany;

        return $theArtist;
    }

}

?>
