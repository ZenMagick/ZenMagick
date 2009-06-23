<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
 *
 * Portions Copyright (c)      Vojtech Semecky, webmaster @ webdot . cz
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
 * A RSS feed item.
 *
 * <p>ZenMagick wrapper around <code>lastRSS</code>.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.model.rss
 * @version $Id: ZMRssItem.php 2121 2009-03-31 01:56:56Z dermanomann $
 */
class ZMRssItem extends ZMObject {
    private $item_;


    /**
     * Create new RSS item.
     *
     * @param array Array of item data.
     */
    function __construct($item=null) {
        parent::__construct();
        $this->item_ = null !== $item ? $item : array();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the item title.
     *
     * @return string The item title.
     */
    public function getTitle() { return $this->getProperty('title'); }

    /**
     * Get the item link.
     *
     * @return string The item link.
     */
    public function getLink() { return $this->getProperty('link'); }

    /**
     * Get the item description.
     *
     * @return string The item description.
     */
    public function getDescription() { return $this->getProperty('description'); }

    /**
     * Get the item category.
     *
     * @return string The item category.
     */
    public function getCategory() { return $this->getProperty('category'); }

    /**
     * Get the item publish date.
     *
     * @return string The item publish date.
     */
    public function getPubDate() { return $this->getProperty('pubDate'); }

    /**
     * Set the item title.
     *
     * @param string title The item title.
     */
    public function setTitle($title) { $this->item_['title'] = $title; }

    /**
     * Set the item link.
     *
     * @param string link The item link.
     */
    public function setLink($link) { $this->item_['link'] = $link; }

    /**
     * Set the item description.
     *
     * @param string description The item description.
     */
    public function setDescription($description) { $this->item_['description'] = $description; }

    /**
     * set the item category.
     *
     * @param string category The item category.
     */
    public function setCategory($category) { $this->item_['category'] = $category; }

    /**
     * Set the item publish date.
     *
     * @param string date The item publish date.
     */
    public function setPubDate($date) { $this->item_['pubDate'] = $date; }

    /**
     * Custom set method for properties that do not have a dedicated
     * access method.
     *
     * @param string name The property name.
     * @param string value The value.
     */
    public function setProperty($name, $value) { $this->item_[$name] = $value; }

    /**
     * Custom get method for properties that do not have a dedicated
     * access method.
     *
     * @param string name The property name.
     * @return string value The value or <code>null</code>.
     */
    public function getProperty($name) { return isset($this->item_[$name]) ? $this->item_[$name] : null; }

    /**
     * Get all properties.
     *
     * @return array Name/value map.
     */
    public function getProperties() { return $this->item_; }

}

?>
