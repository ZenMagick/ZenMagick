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
 * Info for a single tax class.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.model
 */
class ZMTaxClass extends ZMObject {
    private $taxClassId_;
    private $title_;
    private $description_;
    private $lastModified_;
    private $dateAdded_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->taxClassId_ = 0;
        $this->title_ = '';
        $this->description_ = '';
        $this->lastModified_ = null;
        $this->dateAdded_ = null;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Set the id.
     *
     * @param int id The id.
     */
    public function setTaxClassId($id) { $this->taxClassId_ = $id; }

    /**
     * Set the title.
     *
     * @param string title The title.
     */
    public function setTitle($title) { $this->title_ = $title; }

    /**
     * Set the description.
     *
     * @param string description The description.
     */
    public function setDescription($description) { $this->description_ = $description; }

    /**
     * Set the date the class was added.
     *
     * @param string dateAdded The added date.
     */
    public function setDateAdded($dateAdded) { $this->dateAdded_ = $dateAdded; }

    /**
     * Set the last modified date.
     *
     * @param string lastModified The last modified date.
     */
    public function setLastModified($lastModified) { $this->lastModified_ = $lastModified; }

    /**
     * Get the id.
     *
     * @return int The id.
     */
    public function getTaxClassId() { return $this->taxClassId_; }

    /**
     * Get the title.
     *
     * @return string The title.
     */
    public function getTitle() { return $this->title_; }

    /**
     * Get the description.
     *
     * @return string The description.
     */
    public function getDescription() { return $this->description_; }

    /**
     * Get the date the class was added.
     *
     * @return string The added date.
     */
    public function getDateAdded() { return $this->dateAdded_; }

    /**
     * Get the last modified date.
     *
     * @return string The last modified date.
     */
    public function getLastModified() { return $this->lastModified_; }

}
