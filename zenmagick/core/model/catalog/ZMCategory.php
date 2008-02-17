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
 *
 * $Id$
 */
?>
<?php


/**
 * A single category
 *
 * @author mano
 * @package org.zenmagick.model.catalog
 * @version $Id$
 */
class ZMCategory extends ZMModel {
    var $id_;
    var $parentId_;
    var $name_;
    var $active_;
    var $childrenIds_;
    var $description_;
    var $sortOrder_;
    var $image_;


    /**
     * Default c'tor.
     */
    function __construct() {
        parent::__construct();

        $this->id_ = 0;
        $this->parentId_ = 0;
        $this->name_ = '';
        $this->active_ = false;
        $this->childrenIds_ = array();
        $this->image_ = null;
    }

    /**
     * Default c'tor.
     */
    function ZMCategory() {
        $this->__construct();
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
     * @return int The category id.
     */
    function getId() { return $this->id_; }

    /**
     * Get the parent category (if any).
     *
     * @return ZMCategory The parent category or <code>null</code>.
     */
    function getParent() { global $zm_categories; return 0 != $this->parentId_ ? $zm_categories->getCategoryForId($this->parentId_) : null; }

    /**
     * Get the parent category id (if any).
     *
     * @return int The parent category id or <code>0</code>.
     */
    function getParentId() { return $this->parentId_; }

    /**
     * Checks if the catgory has a parent.
     *
     * @return boolean <code>true</code> if this category has a parent, <code>false</code> if not.
     */
    function hasParent() { return 0 != $this->parentId_; }

    /**
     * Get the category name.
     *
     * @return string The category name.
     */
    function getName() { return $this->name_; }

    /**
     * Checks if this category is active; ie. in the category path.
     *
     * @return boolean <code>true</code> if this category is in the category path, <code>false</code> if not.
     */
    function isActive() { return $this->active_; }

    /**
     * Checks if this category has children.
     *
     * @return boolean <code>true</code> if this category has children, <code>false</code> if not.
     */
    function hasChildren() { return 0 < count($this->childrenIds_); }

    /**
     * Get the child categories of this category.
     *
     * @return array A list of <code>ZMcategory</code> instances.
     */
    function getChildren() { global $zm_categories; return $zm_categories->getCategories($this->childrenIds_); }

    /**
     * Get the category description.
     *
     * @return string The description.
     */
    function getDescription() { return $this->description_; }

    /**
     * Get the sort order.
     *
     * @return int The sort order.
     */
    function getSortOrder() { return $this->sortOrder_; }

    /**
     * Get the category image (if any).
     *
     * @return string The image name.
     */
    function getImage() { return $this->image_; }

    /**
     * Get the categories image ino instance (if any).
     *
     * @return ZMImageInfo The <code>ZMImageInfo</code> for this categorie's image, or <code>null</code>.
     */
    function getImageInfo() { 
        return !zm_is_empty($this->image_) ? $this->create("ImageInfo", $this->image_, $this->name_) : null;
    }

    /**
     * Get the category path array.
     *
     * @return array The category path as array of categories with the last element being the products category.
     */
    function getPathArray() {
        $path = array();
        array_push($path, $this->id_);
        $parent = $this->getParent();
        while (null !== $parent) {
            array_push($path, $parent->id_);
            $parent = $parent->getParent();
        }
        return array_reverse($path);
    }

    /**
     * Get the category path.
     *
     * <p>This method will return a value that can be used as <code>cPath</code> value in a URL
     * pointing to this category.</p>
     *
     * @return string The category path in the form <code>cPath=[PATH]</code>.
     */
    function getPath() {
        $path = implode('_', $this->getPathArray());
        return "cPath=".$path;
    }

    /**
     * Set the id.
     *
     * @param int id The category id.
     */
    function setId($id) { $this->id_ = $id; }

    /**
     * Set the parent category id.
     *
     * @param int parentId The parent category id.
     */
    function setParentId($parentId) { $this->parentId_ = $parentId; }

    /**
     * Set the category name.
     *
     * @param string name The category name.
     */
    function setName($name) { $this->name_ = $name; }

    /**
     * Set the category description.
     *
     * @param string description The description.
     */
    function setDescription($description) { $this->description_ = $description; }

    /**
     * Set the sort order.
     *
     * @param int sortOrder The sort order.
     */
    function setSortOrder($sortOrder ) { $this->sortOrder_ = $sortOrder; }

    /**
     * Set the category image (if any).
     *
     * @param string image The image name.
     */
    function setImage($image) { $this->image_ = $image; }
}

?>
