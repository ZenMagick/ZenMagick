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
 * EZ-pages.
 *
 * @author mano
 * @package net.radebatz.zenmagick.service
 * @version $Id$
 */
class ZMEZPages extends ZMService {

    /**
     * Default c'tor.
     */
    function ZMEZPages() {
        parent::__construct();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMEZPages();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    // get page for id
    function &getPageForId($pageId) {
    global $zm_runtime;

        $db = $this->getDB();
        $sql = "select * from " . TABLE_EZPAGES . "
                where pages_id = :pageId";
        $sql = $db->bindVars($sql, ':pageId', $pageId, 'integer');
        if (zm_setting('isEZPagesLangSupport')) {
            $sql = $db->bindVars($sql . " and languages_id = :languageId", ':languageId', $zm_runtime->getLanguageId(), 'integer');
        }
        $results = $db->Execute($sql);

        $page = null;
        if (0 < $results->RecordCount()) {
            $page = $this->_newPage($results->fields);
        }
        return $page;
    }


    // get pages for chapter id
    function getPagesForChapterId($chapterId) {
    global $zm_runtime;

        $db = $this->getDB();
        $sql = "SELECT *
                FROM " . TABLE_EZPAGES . " 
                WHERE ((status_toc = 1 and toc_sort_order <> 0) and toc_chapter= :chapterID)
                AND alt_url_external = '' and alt_url = ''";
        if (zm_setting('isEZPagesLangSupport')) {
            $sql = $db->bindVars($sql . " and languages_id = :languageId", ':languageId', $zm_runtime->getLanguageId(), 'integer');
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


    // get pages for header
    function getPagesForHeader() {
    global $zm_runtime;

        $db = $this->getDB();
        $sql = "select * from " . TABLE_EZPAGES . "
                where status_header = 1
                and header_sort_order > 0";
        if (zm_setting('isEZPagesLangSupport')) {
            $sql = $db->bindVars($sql . " and languages_id = :languageId", ':languageId', $zm_runtime->getLanguageId(), 'integer');
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


    // get pages for sidebar
    function getPagesForSidebar() {
    global $zm_runtime;

        $db = $this->getDB();
        $sql = "select * from " . TABLE_EZPAGES . "
                where status_sidebox = 1
                and sidebox_sort_order > 0";
        if (zm_setting('isEZPagesLangSupport')) {
            $sql = $db->bindVars($sql . " and languages_id = :languageId", ':languageId', $zm_runtime->getLanguageId(), 'integer');
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


    // get pages for footer
    function getPagesForFooter() {
    global $zm_runtime;

        $db = $this->getDB();
        $sql = "select * from " . TABLE_EZPAGES . "
                where status_footer = 1
                and footer_sort_order > 0";
        if (zm_setting('isEZPagesLangSupport')) {
            $sql = $db->bindVars($sql . " and languages_id = :languageId", ':languageId', $zm_runtime->getLanguageId(), 'integer');
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


    function &_newPage($fields) {
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
