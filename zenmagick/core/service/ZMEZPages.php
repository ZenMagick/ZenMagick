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
 * EZ-pages.
 *
 * @author mano
 * @package org.zenmagick.service
 * @version $Id$
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
        return parent::instance('EZPages');
    }


    /**
     * Get page for id.
     *
     * @param int pageId The page id.
     * @param int languageId The languageId; default is <code>null</code> for session language.
     * @return ZMEZPage A new instance or <code>null</code>.
     */
    function getPageForId($pageId, $languageId=null) {
    global $zm_request;

        if (null === $languageId) {
            $session = $zm_request->getSession();
            $languageId = $session->getLanguageId();
        }

        $db = ZMRuntime::getDB();
        $sql = "select * from " . TABLE_EZPAGES . "
                where pages_id = :pageId";
        $sql = $db->bindVars($sql, ':pageId', $pageId, 'integer');
        if (zm_setting('isEZPagesLangSupport')) {
            $sql = $db->bindVars($sql . " and languages_id = :languageId", ':languageId', $languageId, 'integer');
        }
        $results = $db->Execute($sql);

        $page = null;
        if (0 < $results->RecordCount()) {
            $page = $this->_newPage($results->fields);
        }
        return $page;
    }

    /**
     * Get all pages for for a given chapter.
     *
     * @param int chapterId The chapter id.
     * @param int languageId The languageId; default is <code>null</code> for session language.
     * @return array List of <code>ZMEZPage</code> instances.
     */
    function getPagesForChapterId($chapterId, $languageId=null) {
    global $zm_request;

        if (null === $languageId) {
            $session = $zm_request->getSession();
            $languageId = $session->getLanguageId();
        }

        $db = ZMRuntime::getDB();
        $sql = "SELECT *
                FROM " . TABLE_EZPAGES . " 
                WHERE ((status_toc = 1 and toc_sort_order <> 0) and toc_chapter= :chapterID)
                AND alt_url_external = '' and alt_url = ''";
        if (zm_setting('isEZPagesLangSupport')) {
            $sql = $db->bindVars($sql . " and languages_id = :languageId", ':languageId', $languageId, 'integer');
        }
        $sql .= " ORDER BY toc_sort_order, pages_title";
        $sql = $db->bindVars($sql, ':chapterID', $chapterId, 'integer');
        $results = $db->Execute($sql);

        $pages = array();
        while (!$results->EOF) {
            $page = $this->_newPage($results->fields);
            array_push($pages, $page);
            $results->MoveNext();
        }

        return $pages;
    }

    /**
     * Get all header pages.
     *
     * @param int languageId The languageId; default is <code>null</code> for session language.
     * @return array List of <code>ZMEZPage</code> instances.
     */
    function getPagesForHeader($languageId=null) {
    global $zm_request;

        if (null === $languageId) {
            $session = $zm_request->getSession();
            $languageId = $session->getLanguageId();
        }

        $db = ZMRuntime::getDB();
        $sql = "select * from " . TABLE_EZPAGES . "
                where status_header = 1
                and header_sort_order > 0";
        if (zm_setting('isEZPagesLangSupport')) {
            $sql = $db->bindVars($sql . " and languages_id = :languageId", ':languageId', $languageId, 'integer');
        }
        $sql .= " order by header_sort_order, pages_title";
        $results = $db->Execute($sql);

        $pages = array();
        while (!$results->EOF) {
            $page = $this->_newPage($results->fields);
            array_push($pages, $page);
            $results->MoveNext();
        }

        return $pages;
    }

    /**
     * Get all sidebar pages.
     *
     * @param int languageId The languageId; default is <code>null</code> for session language.
     * @return array List of <code>ZMEZPage</code> instances.
     */
    function getPagesForSidebar($languageId=null) {
    global $zm_request;

        if (null === $languageId) {
            $session = $zm_request->getSession();
            $languageId = $session->getLanguageId();
        }

        $db = ZMRuntime::getDB();
        $sql = "select * from " . TABLE_EZPAGES . "
                where status_sidebox = 1
                and sidebox_sort_order > 0";
        if (zm_setting('isEZPagesLangSupport')) {
            $sql = $db->bindVars($sql . " and languages_id = :languageId", ':languageId', $languageId, 'integer');
        }
        $sql .= " order by sidebox_sort_order, pages_title";
        $results = $db->Execute($sql);

        $pages = array();
        while (!$results->EOF) {
            $page = $this->_newPage($results->fields);
            array_push($pages, $page);
            $results->MoveNext();
        }

        return $pages;
    }

    /**
     * Get all footer pages.
     *
     * @param int languageId The languageId; default is <code>null</code> for session language.
     * @return array List of <code>ZMEZPage</code> instances.
     */
    function getPagesForFooter($languageId=null) {
    global $zm_request;

        if (null === $languageId) {
            $session = $zm_request->getSession();
            $languageId = $session->getLanguageId();
        }

        $db = ZMRuntime::getDB();
        $sql = "select * from " . TABLE_EZPAGES . "
                where status_footer = 1
                and footer_sort_order > 0";
        if (zm_setting('isEZPagesLangSupport')) {
            $sql = $db->bindVars($sql . " and languages_id = :languageId", ':languageId', $languageId, 'integer');
        }
        $sql .= " order by footer_sort_order, pages_title";
        $results = $db->Execute($sql);

        $pages = array();
        while (!$results->EOF) {
            $page = $this->_newPage($results->fields);
            array_push($pages, $page);
            $results->MoveNext();
        }

        return $pages;
    }


    function _newPage($fields) {
        $page = $this->create("EZPage", $fields['pages_id'], $fields['pages_title']);
        $page->altUrl_ = $fields['alt_url'];
        $page->altUrlExternal_ = $fields['alt_url_external'];
        $page->htmlText_ = $fields['pages_html_text'];
        $page->isHeader_ = 1 == $fields['status_header'];
        $page->isSidebox_ = 1 == $fields['status_sidebox'];
        $page->isFooter_ = 1 == $fields['status_footer'];
        $page->isToc_ = 1 == $fields['status_toc'];
        $page->headerSort_ = $fields['header_sort_order'];
        $page->sidebarSort_ = $fields['sidebox_sort_order'];
        $page->footerSort_ = $fields['footer_sort_order'];
        $page->tocSort_ = $fields['toc_sort_order'];
        $page->isNewWin_ = 1 == $fields['page_open_new_window'];
        $page->isSSL_ = 1 == $fields['page_is_ssl'];
        $page->tocChapter_ = $fields['toc_chapter'];
        return $page;
    }

}

?>
