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
 * Container for all data/information related to a plugin admin page.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.services.plugins
 * @version $Id: ZMPluginPage.php 1966 2009-02-14 10:52:50Z dermanomann $
 */
class ZMPluginPage extends ZMObject {
    private $id_;
    private $title_;
    private $contents_;
    private $header_;


    /**
     * Create a new plugin page.
     *
     * @param string id The id.
     * @param string title The title.
     * @param string contents The page contents.
     * @param string header Optional code to be injected into the header; default is <code>null</code>.
     */
    function __construct($id, $title, $contents=null, $header='') {
        parent::__construct();
        $this->id_ = $id;
        $this->title_ = $title;
        $this->contents_ = $contents;
        $this->header_ = $header;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the id.
     *
     * @return string The page id.
     */
    public function getId() { return $this->id_; }

    /**
     * Get the title.
     *
     * @return string The page title.
     */
    public function getTitle() { return $this->title_; }

    /**
     * Get the contents.
     *
     * @return string The page contents.
     */
    public function getContents() { return $this->contents_; }

    /**
     * Get the header code.
     *
     * @return string The header code.
     */
    public function getHeader() { return $this->header_; }

    /**
     * Set the id.
     *
     * @param string id The page id.
     */
    public function setId($id) { $this->id_ = $id; }

    /**
     * Set the title.
     *
     * @param string title The page title.
     */
    public function setTitle($title) { $this->title_ = $title; }

    /**
     * Set the contents.
     *
     * @param string contents The page contents.
     */
    public function setContents($contents) { $this->contents_ = $contents; }

    /**
     * Set the header code.
     *
     * @param string header The header code.
     */
    public function setHeader($header) { $this->header_ = $header; }

}

?>
