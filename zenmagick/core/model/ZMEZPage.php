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
 * EZ-page.
 *
 * @author DerManoMann
 * @package org.zenmagick.model
 * @version $Id$
 */
class ZMEZPage extends ZMModel {
    private $id_;
    private $title_;
    private $altUrl_;
    private $altUrlExternal_;
    private $htmlText_;
    private $isHeader_;
    private $isSidebox_;
    private $isFooter_;
    private $isToc_;
    private $headerSort_;
    private $sidebarSort_;
    private $footerSort_;
    private $tocSort_;
    private $isNewWin_;
    private $isSSL_;
    private $tocChapter_;


    /**
     * Create new page.
     *
     * @param int id The page id; default is <em>0</em>.
     * @param string title The page title; default is <em>''</em>.
     */
    function __construct($id=0, $title='') {
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


    public function getId() { return $this->id_; }
    public function getTitle() { return $this->title_; }
    public function getAltUrl() { return $this->altUrl_; }
    public function getAltUrlExternal() { return $this->altUrlExternal_; }
    public function getHtmlText() { return $this->htmlText_; }
    public function isHeader() { return $this->isHeader_; }
    public function isSidebox() { return $this->isSidebox_; }
    public function isFooter() { return $this->isFooter_; }
    public function isToc() { return $this->isToc_; }
    public function getHeaderSort() { return $this->headerSort_; }
    public function getSidebarSort() { return $this->sidebarSort_; }
    public function getFooterSort() { return $this->footerSort_; }
    public function getTocSort() { return $this->tocSort_; }
    public function isNewWin() { return $this->isNewWin_; }
    public function isSSL() { return $this->isSSL_; }
    public function getTocChapter() { return $this->tocChapter_; }

    public function setId($id) { $this->id_ = $id; }
    public function setTitle($title) { $this->title_ = $title; }
    public function setAltUrl($url) { $this->altUrl_ = $url; }
    public function setAltUrlExternal($url) { $this->altUrlExternal_ = $url; }
    public function setHtmlText($text) { $this->htmlText_ = $text; }
    public function setHeader($value) { $this->isHeader_ = $value; }
    public function setSidebox($value) { $this->isSidebox_ = $value; }
    public function setFooter($value) { $this->isFooter_ = $value; }
    public function setToc($value) { $this->isToc_ = $value; }
    public function setHeaderSort($sortOrder) { $this->headerSort_ = $sortOrder; }
    public function setSidebarSort($sortOrder) { $this->sidebarSort_ = $sortOrder; }
    public function setFooterSort($sortOrder) { $this->footerSort_ = $sortOrder; }
    public function setTocSort($value) { $this->tocSort_ = $value; }
    public function setNewWin($value) { $this->isNewWin_ = $value; }
    public function setSSL($value) { $this->isSSL_ = $value; }
    public function setTocChapter($chapter) { $this->tocChapter_ = $chapter; }

}

?>
