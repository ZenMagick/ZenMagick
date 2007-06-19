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
 * Container for all data/information related to a plugin admin page.
 *
 * @author mano
 * @package net.radebatz.zenmagick.plugins
 * @version $Id$
 */
class ZMPluginPage extends ZMObject {
    var $id_;
    var $title_;
    var $contents_;
    var $css_;
    var $javascript_;


    /**
     * Create a new plugin page.
     *
     * @param string id The id.
     * @param string title The title.
     * @param string configtens The page contents.
     * @param string css Optional CSS; default is <code>null</code>.
     * @param string javascript Optional JavaScript; default is <code>null</code>.
     */
    function ZMPluginPage($Id, $title, $contents, $css=null, $javascript=null) {
        parent::__construct();

        $this->id_ = $id;
        $this->title_ = $title;
        $this->contents_ = $contents;
        $this->css_ = $css;
        $this->javascript_ = $javascript;
    }

    /**
     * Create a new plugin page.
     *
     * @param string id The id.
     * @param string title The title.
     * @param string configtens The page contents.
     * @param string css Optional CSS; default is <code>null</code>.
     * @param string javascript Optional JavaScript; default is <code>null</code>.
     */
    function __construct($id, $title, $contents, $css=null, $javascript=null) {
        $this->ZMPluginPage($id, $title, $contents, $css, $javascript);
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the id.
     *
     * @return string The page id.
     */
    function getId() { return $this->id_; }

    /**
     * Get the title.
     *
     * @return string The page title.
     */
    function getTitle() { return $this->title_; }

    /**
     * Get the contents.
     *
     * @return string The page contents.
     */
    function getContents() { return $this->contents_; }

    /**
     * Get the CSS.
     *
     * @return string The page css.
     */
    function getCSS() { return $this->css_; }

    /**
     * Get the javascript.
     *
     * @return string The page javascript.
     */
    function getJavaScript() { return $this->javascript_; }

}

?>
