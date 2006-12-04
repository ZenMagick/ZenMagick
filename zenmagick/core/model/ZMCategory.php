<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
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
 * @package net.radebatz.zenmagick.model
 * @version $Id$
 */
class ZMCategory {
    var $id_;
    var $parent_;
    var $parentId_;
    var $name_;
    var $active_;
    var $children_;
    var $description_;
    var $sortOrder_;
    var $image_;


    // create new instance
    function ZMCategory($id, $parentId, $name, $active = false) {
        $this->id_ = $id;
        $this->parent_ = null;
        $this->parentId_ = $parentId;
        $this->name_ = $name;
        $this->active_ = $active;
        $this->children_ = array();
    }

    // create new instance
    function __construct($id, $parentId, $name, $active = false) {
        $this->ZMCategory($id, $parentId, $name, $active);
    }

    function __destruct() {
    }


    // simple getter/setter
    function getId() { return $this->id_; }
    // PHP5 only function getParent() { return $this->parent_; }
    function getParent() { global $zm_categories; return 0 != $this->parentId_ ? $zm_categories->getCategoryForId($this->parentId_) : null; }
    function getParentId() { return $this->parentId_; }
    function setParent($parent) { $this->parent_ = $parent; }
    function hasParent() { return 0 != $this->parentId_ && null != $this->parent_; }
    function getName() { return $this->name_; }
    function setActive($active) { $this->active_ = $active; }
    function isActive() { return $this->active_; }
    function hasChildren() { return 0 < count($this->children_); }
    function addChild($child) { array_push($this->children_, $child); }
    function getChildren() { return $this->children_; }
    function getDescription() { return $this->description_; }
    function getSortOrder() { return $this->sortOrder_; }
    function getImage() { return $this->image_; }

    // build path
    function getPath() {
        $path = $this->id_;
        $parent = $this->parent_;
        while (null != $parent) {
            $path = $parent->getId()."_".$path;
            $parent = $parent->getParent();
        }
        return "cPath=".$path;
    }

}

?>
