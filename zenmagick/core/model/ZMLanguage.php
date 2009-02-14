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
 * A single language.
 *
 * @author DerManoMann
 * @package org.zenmagick.model
 * @version $Id$
 */
class ZMLanguage extends ZMModel {
    private $name_;
    private $image_;
    private $code_;
    private $directory_;


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
    public function getId() { return $this->get('languageId'); }

    /**
     * Get the language name.
     *
     * @return string The language name.
     */
    public function getName() { return $this->name_; }

    /**
     * Get the language image.
     *
     * @return string The language image.
     */
    public function getImage() { return $this->image_; }

    /**
     * Get the language code.
     *
     * @return string The language code.
     */
    public function getCode() { return $this->code_; }

    /**
     * Get the language directory name.
     *
     * @return string The language directory name.
     */
    public function getDirectory() { return $this->directory_; }

    /**
     * Set the language id.
     *
     * @param int id The language id.
     */
    public function setId($id) { $this->set('lanugageId', $id); }

    /**
     * Set the language name.
     *
     * @param string name The language name.
     */
    public function setName($name) { $this->name_ = $name; }

    /**
     * Set the language image.
     *
     * @param string image The language image.
     */
    public function setImage($image) { $this->image_ = $image; }

    /**
     * Set the language code.
     *
     * @param string code The language code.
     */
    public function setCode($code) { $this->code_ = $code; }

    /**
     * Set the language directory name.
     *
     * @param string directory The language directory name.
     */
    public function setDirectory($directory) { $this->directory_ = $directory; }

}

?>
