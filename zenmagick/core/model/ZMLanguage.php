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
 * A single language.
 *
 * @author mano
 * @package org.zenmagick.model
 * @version $Id$
 */
class ZMLanguage extends ZMModel {
    var $id_;
    var $name_;
    var $image_;
    var $code_;
    var $directory_;


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
     * Get the language id.
     *
     * @return int The language id.
     */
    function getId() { return $this->id_; }

    /**
     * Get the language name.
     *
     * @return string The language name.
     */
    function getName() { return $this->name_; }

    /**
     * Get the language image.
     *
     * @return string The language image.
     */
    function getImage() { return $this->image_; }

    /**
     * Get the language code.
     *
     * @return string The language code.
     */
    function getCode() { return $this->code_; }

    /**
     * Get the language directory name.
     *
     * @return string The language directory name.
     */
    function getDirectory() { return $this->directory_; }

    /**
     * Set the language id.
     *
     * @param int id The language id.
     */
    function setId($id) { $this->id_ = $id; }

    /**
     * Set the language name.
     *
     * @param string name The language name.
     */
    function setName($name) { $this->name_ = $name; }

    /**
     * Set the language image.
     *
     * @param string image The language image.
     */
    function setImage($image) { $this->image_ = $image; }

    /**
     * Set the language code.
     *
     * @param string code The language code.
     */
    function setCode($code) { $this->code_ = $code; }

    /**
     * Set the language directory name.
     *
     * @param string directory The language directory name.
     */
    function setDirectory($directory) { $this->directory_ = $directory; }

}

?>
