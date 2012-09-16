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

namespace ZenMagick\StoreBundle\Entity;

use ZenMagick\Base\Toolbox;
use ZenMagick\Base\ZMObject;

use Doctrine\ORM\Mapping AS ORM;

/**
 * EZ-page.
 *
 * @author DerManoMann
 * @ORM\Table(name="ezpages",
 *  indexes={
 *      @ORM\Index(name="idx_lang_id_zen", columns={"languages_id"}),
 *      @ORM\Index(name="idx_ezp_status_header_zen", columns={"status_header"}),
 *      @ORM\Index(name="idx_ezp_status_sidebox_zen", columns={"status_sidebox"}),
 *      @ORM\Index(name="idx_ezp_status_footer_zen", columns={"status_footer"}),
 *      @ORM\Index(name="idx_ezp_status_toc_zen", columns={"status_toc"})
 *  })
 * @ORM\Entity
 */
class EZPage extends ZMObject {
    /**
     * @var integer $id
     *
     * @ORM\Column(name="pages_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer $languageId
     *
     * @ORM\Column(name="languages_id", type="integer", nullable=false)
     */
    private $languageId;

    /**
     * @var string $title
     *
     * @ORM\Column(name="pages_title", type="string", length=64, nullable=false)
     */
    private $title;

    /**
     * @var string $altUrl
     *
     * @ORM\Column(name="alt_url", type="string", length=255, nullable=false)
     */
    private $altUrl;

    /**
     * @var string $altUrlExternal
     *
     * @ORM\Column(name="alt_url_external", type="string", length=255, nullable=false)
     */
    private $altUrlExternal;

    /**
     * @var text $htmlText
     *
     * @ORM\Column(name="pages_html_text", type="text", nullable=true)
     */
    private $htmlText;

    /**
     * @var boolean $header
     *
     * @ORM\Column(name="status_header", type="boolean", nullable=false)
     */
    private $header;

   /**
     * @var integer $sidebox
     *
     * @ORM\Column(name="status_sidebox", type="boolean", nullable=false)
     */
    private $sidebox;
    /**
     * @var boolean $footer
     *
     * @ORM\Column(name="status_footer", type="boolean", nullable=false)
     */
    private $footer;
    /**
     * @var boolean $toc
     *
     * @ORM\Column(name="status_toc", type="boolean", nullable=false)
     */
    private $toc;

    /**
     * @var integer $headerSort
     *
     * @ORM\Column(name="header_sort_order", type="smallint", nullable=false)
     */
    private $headerSort;
     /**
     * @var integer $sideboxSort
     *
     * @ORM\Column(name="sidebox_sort_order", type="smallint", nullable=false)
     */
    private $sideboxSort;
    /**
     * @var integer $footerSort
     *
     * @ORM\Column(name="footer_sort_order", type="smallint", nullable=false)
     */
    private $footerSort;

    /**
     * @var integer $tocSort
     *
     * @ORM\Column(name="toc_sort_order", type="smallint", nullable=false)
     */
    private $tocSort;

    /**
     * @var boolean $newWin
     *
     * @ORM\Column(name="page_open_new_window", type="boolean", nullable=false)
     */
    private $newWin;

    /**
     * @var boolean $ssl
     *
     * @ORM\Column(name="page_is_ssl", type="boolean", nullable=false)
     */
    private $ssl;

    /**
     * @var integer $tocChapter
     *
     * @ORM\Column(name="toc_chapter", type="integer", nullable=false)
     */
    private $tocChapter;



    /**
     * Create new page.
     *
     * @param int id The page id; default is <em>0</em>.
     * @param string title The page title; default is <em>null</em>.
     */
    public function __construct($id=0, $title=null) {
        parent::__construct();

        $this->id = $id;
        $this->languageId = 1;
        $this->title = $title;
        $this->altUrl = '';
        $this->altUrlExternal = '';
        $this->htmlText = '';
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


    public function getId() { return $this->id; }
    public function getLanguageId() { return $this->languageId; }
    public function getTitle() { return $this->title; }
    public function getAltUrl() { return $this->altUrl; }
    public function getAltUrlExternal() { return $this->altUrlExternal; }

    /**
     * Get the actual content.
     *
     * @param boolean php Optional flag to allow/disable PHP exection in the contents; default is <code>true</code>.
     * @return string The page contents.
     */
    public function getHtmlText($php=false) {
        $text = $this->htmlText;
        if ($php) {
            ob_start();
            eval('?>'.$text);
            $text = ob_get_clean();
        }
        return $text;
    }

    public function isHeader() { return $this->header; }
    public function getHeader() { return $this->header; }
    public function isSidebox() { return $this->sidebox; }
    public function getSidebox() { return $this->sidebox; }
    public function isFooter() { return $this->footer; }
    public function getFooter() { return $this->footer; }
    public function getToc() { return $this->toc; }
    public function isToc() { return 1 == $this->toc; }
    public function isStatic() { return 2 == $this->toc; }
    public function getHeaderSort() { return $this->headerSort; }
    public function getSideboxSort() { return $this->sideboxSort; }
    public function getFooterSort() { return $this->footerSort; }
    public function getTocSort() { return $this->tocSort; }
    public function isNewWin() { return $this->newWin; }
    public function getNewWin() { return $this->newWin; }
    public function isSsl() { return $this->ssl; }
    public function getSsl() { return $this->ssl; }
    public function getTocChapter() { return $this->tocChapter; }

    public function setId($id) { $this->id = $id; }
    public function setLanguageId($languageId) { $this->languageId = $languageId; }
    public function setTitle($title) { $this->title = $title; }
    public function setAltUrl($url) { $this->altUrl = $url; }
    public function setAltUrlExternal($url) { $this->altUrlExternal = $url; }
    public function setHtmlText($text) { $this->htmlText = $text; }
    public function setHeader($value) { $this->header = Toolbox::asBoolean($value); }
    public function setSidebox($value) { $this->sidebox = Toolbox::asBoolean($value); }
    public function setFooter($value) { $this->footer = Toolbox::asBoolean($value); }
    public function setToc($value) { $this->toc = (int)$value; }
    public function setStatic($value) { $this->toc = $value ? 2: 0; }
    public function setHeaderSort($sortOrder) { $this->headerSort = $sortOrder; }
    public function setSideboxSort($sortOrder) { $this->sideboxSort = $sortOrder; }
    public function setFooterSort($sortOrder) { $this->footerSort = $sortOrder; }
    public function setTocSort($value) { $this->tocSort = $value; }
    public function setNewWin($value) { $this->newWin = Toolbox::asBoolean($value); }
    public function setSsl($value) { $this->ssl = Toolbox::asBoolean($value); }
    public function setTocChapter($chapter) { $this->tocChapter = $chapter; }
}
