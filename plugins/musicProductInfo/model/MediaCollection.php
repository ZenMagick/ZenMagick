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
namespace ZenMagick\plugins\musicProductInfo\model;

use ZenMagick\base\ZMObject;

/**
 * A collection of media items.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class MediaCollection extends ZMObject {
    private $collectionId_;
    private $name_;
    private $items_;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->collectionId_ = 0;
        $this->name_ = '';
        $this->items_ = array();
    }


    /**
     * Get the collection id.
     *
     * @return int The collection id.
     */
    public function getCollectionId() { return $this->collectionId_; }

    /**
     * Get the collection name.
     *
     * @return string The collection name.
     */
    public function getName() { return $this->name_; }

    /**
     * Get the media items.
     *
     * @return array A list of <code>Media</code> objects.
     */
    public function getItems() { return $this->items_; }

    /**
     * Set the collection id.
     *
     * @param int collectionId The collection id.
     */
    public function setCollectionId($collectionId) { $this->collectionId_ = $collectionId; }

    /**
     * Set the collection name.
     *
     * @param string name The collection name.
     */
    public function setName($name) { $this->name_ = $name; }

    /**
     * Set the media items.
     *
     * @param array items A list of <code>Media</code> objects.
     */
    public function setItems($items) { $this->items_ = $items; }

    /**
     * Add a single media items.
     *
     * @param Media item A single <code>Media</code> object.
     */
    public function addItem($item) { $this->items_[] = $item; }

}
