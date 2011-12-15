<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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

use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;

/**
 * A single category
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.model.catalog
 */
class ZMCategory extends ZMObject {
    private $parentId_;
    private $name_;
    private $active_;
    private $childrenIds_;
    private $description_;
    private $sortOrder_;
    private $image_;
    private $languageId_;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->setId(0);
        $this->parentId_ = 0;
        $this->name_ = null;
        $this->active_ = false;
        $this->childrenIds_ = array();
        $this->image_ = null;
        $this->languageId_ = 0;
        $this->setDateAdded(null);
        $this->setLastModified(null);
    }


    /**
     * Get the id.
     *
     * @return int The category id.
     */
    public function getId() { return $this->get('categoryId'); }

    /**
     * Get the parent category (if any).
     *
     * @return ZMCategory The parent category or <code>null</code>.
     */
    public function getParent() {
        return 0 != $this->parentId_ ? $this->container->get('categoryService')->getCategoryForId($this->parentId_, $this->languageId_) : null;
    }

    /**
     * Get the parent category id (if any).
     *
     * @return int The parent category id or <code>0</code>.
     */
    public function getParentId() { return $this->parentId_; }

    /**
     * Checks if the catgory has a parent.
     *
     * @return boolean <code>true</code> if this category has a parent, <code>false</code> if not.
     */
    public function hasParent() { return 0 != $this->parentId_; }

    /**
     * Get the category name.
     *
     * @return string The category name.
     */
    public function getName() { return $this->name_; }

    /**
     * Checks if this category is active; ie. visible in the storefront.
     *
     * @return boolean <code>true</code> if this category is active, <code>false</code> if not.
     */
    public function isActive() { return $this->active_; }

    /**
     * Set the active flag.
     *
     * @param boolean active <code>true</code> if this category is active, <code>false</code> if not.
     */
    public function setActive($active) { $this->active_ = $active; }

    /**
     * Checks if this category has children.
     *
     * @return boolean <code>true</code> if this category has children, <code>false</code> if not.
     */
    public function hasChildren() { return 0 < count($this->childrenIds_); }

    /**
     * Get the child categories of this category.
     *
     * @return array A list of <code>ZMCategory</code> instances.
     */
    public function getChildren() {
        return $this->container->get('categoryService')->getCategories($this->languageId_, $this->childrenIds_);
    }

    /**
     * Add a child category.
     *
     * @param mixed child Either a category or category id.
     */
    public function addChild($child) {
        $id = ($child instanceof ZMCategory)  ? $child->getId() : $child;
        if (!in_array($id, $this->childrenIds_)) {
            $this->childrenIds_[] = $id;
        }
    }

    /**
     * Remove a child category.
     *
     * @param mixed child Either a category or category id.
     */
    public function removeChild($child) {
        $cid = ($child instanceof ZMCategory)  ? $child->getId() : $child;
        $tmp = array();
        foreach ($this->childrenIds_ as $id) {
            if ($id != $cid) {
                $tmp[] = $id;
            }
        }
        $this->childrenIds_ = $tmp;
    }

    /**
     * Get the category description.
     *
     * @return string The description.
     */
    public function getDescription() { return $this->description_; }

    /**
     * Get the sort order.
     *
     * @return int The sort order.
     */
    public function getSortOrder() { return $this->sortOrder_; }

    /**
     * Get the category image (if any).
     *
     * @return string The image name.
     */
    public function getImage() { return $this->image_; }

    /**
     * Get the categories image ino instance (if any).
     *
     * @return ZMImageInfo The <code>ZMImageInfo</code> for this categorie's image, or <code>null</code>.
     */
    public function getImageInfo() {
        if (null == $this->image_) {
            return null;
        }

        $imageInfo = Runtime::getContainer()->get("ZMImageInfo");
        $imageInfo->setAltText($this->name_);
        $imageInfo->setDefaultImage($this->image_);
        return $imageInfo;
    }

    /**
     * Get the category path array.
     *
     * @return array The category path as array of categories with the last element being the products category.
     */
    public function getPathArray() {
        $path = array();
        array_push($path, $this->properties_['categoryId']);
        $parent = $this->getParent();
        while (null !== $parent) {
            array_push($path, $parent->getId());
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
    public function getPath() {
        $path = implode('_', $this->getPathArray());
        return "cPath=".$path;
    }

    /**
     * Set the id.
     *
     * @param int id The category id.
     */
    public function setId($id) { $this->set('categoryId', $id); }

    /**
     * Set the parent category id.
     *
     * @param int parentId The parent category id.
     */
    public function setParentId($parentId) { $this->parentId_ = $parentId; }

    /**
     * Set the category name.
     *
     * @param string name The category name.
     */
    public function setName($name) { $this->name_ = $name; }

    /**
     * Set the category description.
     *
     * @param string description The description.
     */
    public function setDescription($description) { $this->description_ = $description; }

    /**
     * Set the sort order.
     *
     * @param int sortOrder The sort order.
     */
    public function setSortOrder($sortOrder) { $this->sortOrder_ = $sortOrder; }

    /**
     * Set the category image (if any).
     *
     * @param string image The image name.
     */
    public function setImage($image) { $this->image_ = $image; }

    /**
     * Get the language id.
     *
     * @return int The language id.
     */
    public function getLanguageId() { return $this->languageId_; }

    /**
     * Set the language id.
     *
     * @param int languageId The language id.
     */
    public function setLanguageId($languageId) { $this->languageId_ = $languageId; }

    /**
     * Get a list of <strong>all</strong> decendant category ids.
     *
     * <p>This is a recursive function. If you only want the direct children use <code>getChildren()</code>.</p>
     *
     * @param boolean includeSelf Optional flag to include this category in the list; default is <code>true</code>.
     * @return array A list of category ids.
     */
    public function getDecendantIds($includeSelf=true) {
        $ids = array();
        if ($includeSelf) {
            $ids[] = $this->properties_['categoryId'];
        }
        foreach ($this->getChildren() as $child) {
            $childIds = $child->getDecendantIds(true);
            $ids = array_merge($ids, $childIds);
        }
        return $ids;
    }

    /**
     * Get a list of allowed product types.
     *
     * <p>An empty list means no restrictions.</p>
     *
     * @return array List of allowed product type ids (might be empty).
     */
    public function getProductTypeIds() {
        return $this->container->get('categoryService')->getProductTypeIds($this->getId());
    }

    /**
     * Get meta tag details if available.
     *
     * @param int languageId The language id.
     * @return ZMMetaTagDetails The details or <code>null</code>.
     */
    public function getMetaTagDetails($languageId) {
        return $this->container->get('categoryService')->getMetaTagDetailsForId($this->getId(), $languageId);
    }

}
