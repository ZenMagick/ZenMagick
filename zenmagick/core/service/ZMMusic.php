<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
 * @package net.radebatz.zenmagick.service
 * @version $Id$
 */
class ZMMusic extends ZMService {

    /**
     * Default c'tor.
     */
    function ZMMusic() {
        parent::__construct();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMMusic();
    }

    /**
     * Default d'tor.
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
    function &getArtistForProductId($productId) {
    global $zm_runtime;

        $sql = "select * from " . TABLE_PRODUCT_MUSIC_EXTRA . "
                where products_id = :productId";
        $sql = $this->getDB()->bindVars($sql, ":productId", $productId, "integer");
        $extra = $this->getDB()->Execute($sql);

        $sql = "select * from " . TABLE_RECORD_ARTISTS . "
                where artists_id = :artistId";
        $sql = $this->getDB()->bindVars($sql, ":artistId", $extra->fields['artists_id'], "integer");
        $artist = $this->getDB()->Execute($sql);

        $sql = "select * from " . TABLE_RECORD_ARTISTS_INFO . "
                where artists_id = :artistId
                and languages_id = :languageId";
        $sql = $this->getDB()->bindVars($sql, ":artistId", $extra->fields['artists_id'], "integer");
        $sql = $this->getDB()->bindVars($sql, ":languageId", $zm_runtime->getLanguageId(), "integer");
        $artistInfo = $this->getDB()->Execute($sql);

        $sql = "select * from " . TABLE_RECORD_COMPANY . "
                where record_company_id = :recordCompanyId";
        $sql = $this->getDB()->bindVars($sql, ":recordCompanyId", $extra->fields['record_company_id'], "integer");
        $recordCompany = $this->getDB()->Execute($sql);

        $sql = "select * from " . TABLE_RECORD_COMPANY_INFO . "
                where record_company_id = :recordCompanyId
                and languages_id = :languageId";
        $sql = $this->getDB()->bindVars($sql, ":recordCompanyId", $extra->fields['record_company_id'], "integer");
        $sql = $this->getDB()->bindVars($sql, ":languageId", $zm_runtime->getLanguageId(), "integer");
        $recordCompanyInfo = $this->getDB()->Execute($sql);

        $sql = "select * from " . TABLE_MUSIC_GENRE . "
                where music_genre_id = :genreId";
        $sql = $this->getDB()->bindVars($sql, ":genreId", $extra->fields['music_genre_id'], "integer");
        $musicGenre = $this->getDB()->Execute($sql);

        $theArtist =& $this->create("Artist");
        $theArtist->id_ = $artist->fields['artists_id'];
        $theArtist->name_ = $artist->fields['artists_name'];
        $theArtist->genre_ = $musicGenre->fields['music_genre_name'];
        $theArtist->image_ = $artist->fields['artists_image'];
        $theArtist->url_ = $artistInfo->fields['artists_url'];

        $theRecordCompany =& $this->create("RecordCompany");
        $theRecordCompany->id_ = $extra->fields['record_company_id'];
        $theRecordCompany->name_ = $recordCompanyInfo->fields['record_company_name'];
        $theRecordCompany->url_ = $recordCompanyInfo->fields['record_company_url'];

        $theArtist->recordCompany_ = $theRecordCompany;

        return $theArtist;
    }

}

?>
