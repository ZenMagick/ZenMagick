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

use ZenMagick\Base\Runtime;
use ZenMagick\Base\ZMObject;
use ZenMagick\Base\Database\QueryDetails;
use ZenMagick\Base\Database\SqlAware;

/**
 * EZ-pages.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.services
 */
class ZMEZPages extends ZMObject implements SqlAware {

    /**
     * {@inheritDoc}
     */
    public function getQueryDetails($method=null, $args=array()) {
        $methods = array('getAllPages');
        if (in_array($method, $methods)) {
            return call_user_func_array(array($this, $method.'QueryDetails'), $args);
        }
        return null;
    }

    /**
     * Get all pages for the given language.
     *
     * @param int languageId The languageId.
     * @param string mode Optional mode to define what to load - <em>all</em>, <em>pages</em> or <em>static</em>; default is <em>pages</em>.
     * @return array List of <code>ZMEZPage</code> instances.
     */
    protected function getAllPagesQueryDetails($languageId, $mode='pages') {
        $sql = "SELECT *
                FROM %table.ezpages%";
        $sql .= " WHERE languages_id = :languageId";
        switch ($mode) {
        case 'all':
            break;
        case 'static':
            $sql .= " AND status_toc = 2";
            break;
        case 'pages':
        default:
            $sql .= " AND status_toc < 2";
            break;
        }
        $sql .= " ORDER BY toc_sort_order, pages_title";
        $args = array('languageId' => $languageId);
        return new QueryDetails(ZMRuntime::getDatabase(), $sql, $args, array('ezpages'), 'ZMEZPage', 'pages_id');
    }

    /**
     * Get all pages for the given language.
     *
     * @param int languageId The languageId.
     * @param string mode Optional mode to define what to load - <em>all</em>, <em>pages</em> or <em>static</em>; default is <em>pages</em>.
     * @return array List of <code>ZMEZPage</code> instances.
     */
    public function getAllPages($languageId, $mode='pages') {
        $details = $this->getAllPagesQueryDetails($languageId, $mode);
        return $details->query();
    }

    /**
     * Get page for id.
     *
     * @param int pageId The page id.
     * @param int languageId The languageId.
     * @return ZMEZPage A new instance or <code>null</code>.
     */
    public function getPageForId($pageId, $languageId) {
        $sql = "SELECT *
                FROM %table.ezpages%
                WHERE pages_id = :id";
        $sql .= " AND languages_id = :languageId";
        return ZMRuntime::getDatabase()->querySingle($sql, array('id' => $pageId, 'languageId' => $languageId), 'ezpages', 'ZMEZPage');
    }

    /**
     * Get page for name.
     *
     * @param string name The page name.
     * @param int languageId The languageId.
     * @return ZMEZPage A new instance or <code>null</code>.
     */
    public function getPageForName($name, $languageId) {
        $sql = "SELECT *
                FROM %table.ezpages%
                WHERE pages_title = :title";
        $sql .= " AND languages_id = :languageId";
        return ZMRuntime::getDatabase()->querySingle($sql, array('title' => $name, 'languageId' => $languageId), 'ezpages', 'ZMEZPage');
    }

    /**
     * Get all pages for for a given chapter.
     *
     * @param int chapterId The chapter id.
     * @param int languageId The languageId.
     * @return array List of <code>ZMEZPage</code> instances.
     */
    public function getPagesForChapterId($chapterId, $languageId) {
        $sql = "SELECT *
                FROM %table.ezpages%
                WHERE ((status_toc = 1 AND toc_sort_order <> 0) AND toc_chapter= :tocChapter)
                AND alt_url_external = '' AND alt_url = ''";
        $sql .= " AND languages_id = :languageId";
        $sql .= " ORDER BY toc_sort_order, pages_title";
        return ZMRuntime::getDatabase()->fetchAll($sql, array('tocChapter' => $chapterId, 'languageId' => $languageId), 'ezpages', 'ZMEZPage');
    }

    /**
     * Get all header pages.
     *
     * @param int languageId The languageId.
     * @return array List of <code>ZMEZPage</code> instances.
     */
    public function getPagesForHeader($languageId) {
        $sql = "SELECT *
                FROM %table.ezpages%
                WHERE status_header = 1
                  AND header_sort_order > 0";
        $sql .= " AND languages_id = :languageId";
        $sql .= " ORDER BY header_sort_order, pages_title";
        return ZMRuntime::getDatabase()->fetchAll($sql, array('languageId' => $languageId), 'ezpages', 'ZMEZPage');
    }

    /**
     * Get all sidebar pages.
     *
     * @param int languageId The languageId.
     * @return array List of <code>ZMEZPage</code> instances.
     */
    public function getPagesForSidebar($languageId) {
        $sql = "SELECT *
                FROM %table.ezpages%
                WHERE status_sidebox = 1
                  AND sidebox_sort_order > 0";
        $sql .= " AND languages_id = :languageId";
        $sql .= " ORDER BY sidebox_sort_order, pages_title";
        return ZMRuntime::getDatabase()->fetchAll($sql, array('languageId' => $languageId), 'ezpages', 'ZMEZPage');
    }

    /**
     * Get all footer pages.
     *
     * @param int languageId The languageId.
     * @return array List of <code>ZMEZPage</code> instances.
     */
    public function getPagesForFooter($languageId) {
        $sql = "SELECT *
                FROM %table.ezpages%
                WHERE status_footer = 1
                  AND footer_sort_order > 0";
        $sql .= " AND languages_id = :languageId";
        $sql .= " ORDER BY footer_sort_order, pages_title";
        return ZMRuntime::getDatabase()->fetchAll($sql, array('languageId' => $languageId), 'ezpages', 'ZMEZPage');
    }

    /**
     * Create a new page.
     *
     * @param ZMEZPage page The page to create.
     * @return ZMEZPage The updated (keys, etc) instance.
     */
    public function createPage($page) {
        return ZMRuntime::getDatabase()->createModel('ezpages', $page);
    }

    /**
     * Update an existing page.
     *
     * @param ZMEZPage page The page to update.
     * @return boolean <code>true</code> for success.
     */
    public function updatePage($page) {
        ZMRuntime::getDatabase()->updateModel('ezpages', $page);
        return true;
    }

    /**
     * Delete an existing page.
     *
     * @param ZMEZPage page The page to delete.
     * @return boolean <code>true</code> for success.
     */
    public function removePage($page) {
        ZMRuntime::getDatabase()->removeModel('ezpages', $page);
        return true;
    }

}
