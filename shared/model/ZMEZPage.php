<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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
 * @package zenmagick.store.shared.model
 * @Table(name="ezpages")
 * @Entity
 */
class ZMEZPage extends ZMObject {
    /**
     * @var integer $id
     *
     * @Column(name="pages_id", type="integer", nullable=false)
     * @Id
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer $languageId
     *
     * @Column(name="languages_id", type="integer", nullable=false)
     */
    private $languageId;

    /**
     * @var string $title
     *
     * @Column(name="pages_title", type="string", length=64, nullable=false)
     */
    private $title;

    /**
     * @var string $altUrl
     *
     * @Column(name="alt_url", type="string", length=255, nullable=false)
     */
    private $altUrl;

    /**
     * @var string $altUrlExternal
     *
     * @Column(name="alt_url_external", type="string", length=255, nullable=false)
     */
    private $altUrlExternal;

    /**
     * @var text $htmlText
     *
     * @Column(name="pages_html_text", type="text", nullable=true)
     */
    private $htmlText;

    /**
     * @var boolean $header
     *
     * @Column(name="status_header", type="integer", nullable=false)
     */
    private $header;
    /**
     * @var integer $headerSort
     *
     * @Column(name="header_sort_order", type="integer", nullable=false)
     */
    private $headerSort;
    /**
     * @var integer $sidebox
     *
     * @Column(name="status_sidebox", type="integer", nullable=false)
     */
    private $sidebox;
    /**
     * @var integer $sideboxSort
     *
     * @Column(name="sidebox_sort_order", type="integer", nullable=false)
     */
    private $sideboxSort;
    /**
     * @var boolean $footer
     *
     * @Column(name="status_footer", type="integer", nullable=false)
     */
    private $footer;
    /**
     * @var integer $footerSort
     *
     * @Column(name="footer_sort_order", type="integer", nullable=false)
     */
    private $footerSort;
    /**
     * @var boolean $toc
     *
     * @Column(name="status_toc", type="integer", nullable=false)
     */
    private $toc;

    /**
     * @var integer $tocChapter
     *
     * @Column(name="toc_chapter", type="integer", nullable=false)
     */
    private $tocChapter;
    /**
     * @var integer $tocSort
     *
     * @Column(name="toc_sort_order", type="integer", nullable=false)
     */
    private $tocSort;

    /**
     * @var boolean $newWin
     *
     * @Column(name="page_open_new_window", type="integer", nullable=false)
     */
    private $newWin;

    /**
     * @var integer $ssl
     *
     * @Column(name="page_is_ssl", type="integer", nullable=false)
     */
    private $ssl;


    /**
     * Create new page.
     *
     * @param int id The page id; default is <em>0</em>.
     * @param string title The page title; default is <em>null</em>.
     */
    function __construct($id=0, $title=null) {
        parent::__construct();

        $this->id = $id;
        $this->languageId = 0;
        $this->title = $title;
        $this->altUrl = null;
        $this->altUrlExternal = null;
        $this->htmlText = null;
        $this->header = false;
        $this->sidebox = false;
        $this->footer = false;
        $this->toc = false;
        $this->headerSort = 0;
        $this->sideboxSort = 0;
        $this->footerSort = 0;
        $this->newWin = false;
        $this->ssl = false;
        $this->tocChapter = 0;
        $this->tocSort = 0;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    public function getId() { return $this->id; }
    public function getLanguageId() { return $this->languageId; }
    public function getTitle() { return $this->title; }
    public function getAltUrl() { return $this->altUrl; }
    public function getAltUrlExternal() { return $this->altUrlExternal; }
    public function getHtmlText() { return $this->htmlText; }
    public function isHeader() { return $this->header; }
    public function isSidebox() { return $this->sidebox; }
    public function isFooter() { return $this->footer; }
    public function isToc() { return $this->toc; }
    public function getHeaderSort() { return $this->headerSort; }
    public function getSideboxSort() { return $this->sideboxSort; }
    public function getFooterSort() { return $this->footerSort; }
    public function getTocSort() { return $this->tocSort; }
    public function isNewWin() { return $this->newWin; }
    public function isSSL() { return $this->ssl; }
    public function getTocChapter() { return $this->tocChapter; }

    public function setId($id) { $this->id = $id; }
    public function setLanguageId($languageId) { $this->languageId = $languageId; }
    public function setTitle($title) { $this->title = $title; }
    public function setAltUrl($url) { $this->altUrl = $url; }
    public function setAltUrlExternal($url) { $this->altUrlExternal = $url; }
    public function setHtmlText($text) { $this->htmlText = $text; }
    public function setHeader($value) { $this->header = ZMLangUtils::asBoolean($value); }
    public function setSidebox($value) { $this->sidebox = ZMLangUtils::asBoolean($value); }
    public function setFooter($value) { $this->footer = ZMLangUtils::asBoolean($value); }
    public function setToc($value) { $this->toc = ZMLangUtils::asBoolean($value); }
    public function setHeaderSort($sortOrder) { $this->headerSort = $sortOrder; }
    public function setSideboxSort($sortOrder) { $this->sideboxSort = $sortOrder; }
    public function setFooterSort($sortOrder) { $this->footerSort = $sortOrder; }
    public function setTocSort($value) { $this->tocSort = $value; }
    public function setNewWin($value) { $this->newWin = ZMLangUtils::asBoolean($value); }
    public function setSSL($value) { $this->ssl = ZMLangUtils::asBoolean($value); }
    public function setTocChapter($chapter) { $this->tocChapter = $chapter; }

}
