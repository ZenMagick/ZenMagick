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
 * EZ-pages.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.services
 * @version $Id: ZMEZPages.php 1966 2009-02-14 10:52:50Z dermanomann $
 */
class ZMEZPages extends ZMObject {

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
     * Get instance.
     */
    public static function instance() {
        return ZMObject::singleton('EZPages');
    }


    /**
     * Get page for id.
     *
     * @param int pageId The page id.
     * @param int languageId The languageId; default is <code>null</code> for session language.
     * @return ZMEZPage A new instance or <code>null</code>.
     */
    public function getPageForId($pageId, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        $sql = "SELECT * 
                FROM " . TABLE_EZPAGES . "
                WHERE pages_id = :id";
        if (ZMSettings::get('isEZPagesLangSupport')) {
            $sql .= " AND languages_id = :languageId";
        }
        return Runtime::getDatabase()->querySingle($sql, array('id' => $pageId, 'languageId' => $languageId), TABLE_EZPAGES, 'EZPage');
    }

    /**
     * Get all pages for for a given chapter.
     *
     * @param int chapterId The chapter id.
     * @param int languageId The languageId; default is <code>null</code> for session language.
     * @return array List of <code>ZMEZPage</code> instances.
     */
    public function getPagesForChapterId($chapterId, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        $sql = "SELECT *
                FROM " . TABLE_EZPAGES . " 
                WHERE ((status_toc = 1 AND toc_sort_order <> 0) AND toc_chapter= :tocChapter)
                AND alt_url_external = '' AND alt_url = ''";
        if (ZMSettings::get('isEZPagesLangSupport')) {
            $sql .= " AND languages_id = :languageId";
        }
        $sql .= " ORDER BY toc_sort_order, pages_title";
        return Runtime::getDatabase()->query($sql, array('tocChapter' => $chapterId, 'languageId' => $languageId), TABLE_EZPAGES, 'EZPage');
    }

    /**
     * Get all header pages.
     *
     * @param int languageId The languageId; default is <code>null</code> for session language.
     * @return array List of <code>ZMEZPage</code> instances.
     */
    public function getPagesForHeader($languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        $sql = "SELECT * 
                FROM " . TABLE_EZPAGES . "
                WHERE status_header = 1
                  AND header_sort_order > 0";
        if (ZMSettings::get('isEZPagesLangSupport')) {
            $sql .= " AND languages_id = :languageId";
        }
        $sql .= " ORDER BY header_sort_order, pages_title";
        return Runtime::getDatabase()->query($sql, array('languageId' => $languageId), TABLE_EZPAGES, 'EZPage');
    }

    /**
     * Get all sidebar pages.
     *
     * @param int languageId The languageId; default is <code>null</code> for session language.
     * @return array List of <code>ZMEZPage</code> instances.
     */
    public function getPagesForSidebar($languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        $sql = "SELECT * 
                FROM " . TABLE_EZPAGES . "
                WHERE status_sidebox = 1
                  AND sidebox_sort_order > 0";
        if (ZMSettings::get('isEZPagesLangSupport')) {
            $sql .= " AND languages_id = :languageId";
        }
        $sql .= " ORDER BY sidebox_sort_order, pages_title";
        return Runtime::getDatabase()->query($sql, array('languageId' => $languageId), TABLE_EZPAGES, 'EZPage');
    }

    /**
     * Get all footer pages.
     *
     * @param int languageId The languageId; default is <code>null</code> for session language.
     * @return array List of <code>ZMEZPage</code> instances.
     */
    public function getPagesForFooter($languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        $sql = "SELECT * 
                FROM " . TABLE_EZPAGES . "
                WHERE status_footer = 1
                  AND footer_sort_order > 0";
        if (ZMSettings::get('isEZPagesLangSupport')) {
            $sql .= " AND languages_id = :languageId";
        }
        $sql .= " ORDER BY footer_sort_order, pages_title";
        return Runtime::getDatabase()->query($sql, array('languageId' => $languageId), TABLE_EZPAGES, 'EZPage');
    }

}

?>
