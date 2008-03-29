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
 * EZ-page.
 *
 * @author mano
 * @package org.zenmagick.model
 * @version $Id$
 */
class ZMEZPage extends ZMModel {
    var $id_;
    var $title_;
    var $altUrl_;
    var $altUrlExternal_;
    var $htmlText_;
    var $isHeader_;
    var $isSidebox_;
    var $isFooter_;
    var $isToc_;
    var $headerSort_;
    var $sidebarSort_;
    var $footerSort_;
    var $tocSort_;
    var $isNewWin_;
    var $isSSL_;
    var $tocChapter_;


    /**
     * Create new page.
     *
     * @param int id The page id.
     * @param string title The page title.
     */
    function __construct($id, $title) {
        parent::__construct();

        $this->id_ = $id;
        $this->title_ = $title;
        $this->altUrl_ = null;
        $this->altUrlExternal_ = null;
        $this->htmlText_ = '';
        $this->isHeader_ = false;
        $this->isSidebox_ = false;
        $this->isFooter_ = false;
        $this->isToc_ = false;
        $this->headerSort_ = 1;
        $this->sidebarSort_ = 1;
        $this->footerSort_ = 1;
        $this->isNewWin_ = false;
        $this->isSSL_ = false;
        $this->tocChapter_ = 0;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    // getter/setter
    function getId() { return $this->id_; }
    function getTitle() { return $this->title_; }
    function getAltUrl() { return $this->altUrl_; }
    function getAltUrlExternal() { return $this->altUrlExternal_; }
    function getHtmlText() { return $this->htmlText_; }
    function isHeader() { return $this->isHeader_; }
    function isSidebox() { return $this->isSidebox_; }
    function isFooter() { return $this->isFooter_; }
    function isToc() { return $this->isToc_; }
    function getHeaderSort() { return $this->headerSort_; }
    function getSidebarSort() { return $this->sidebarSort_; }
    function getFooterSort() { return $this->footerSort_; }
    function getTocSort() { return $this->tocSort_; }
    function isNewWin() { return $this->isNewWin_; }
    function isSSL() { return $this->isSSL_; }
    function getTocChapter() { return $this->tocChapter_; }

}

?>
