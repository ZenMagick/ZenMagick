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
 * @package org.zenmagick.model.rss
 * @version $Id$
 */
class ZMRssItem extends ZMObject {
    var $item_;


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
    function getTitle() { return $this->getProperty('title'); }

    /**
     * Get the item link.
     *
     * @return string The item link.
     */
    function getLink() { return $this->getProperty('link'); }

    /**
     * Get the item description.
     *
     * @return string The item description.
     */
    function getDescription() { return $this->getProperty('description'); }

    /**
     * Get the item category.
     *
     * @return string The item category.
     */
    function getCategory() { return $this->getProperty('category'); }

    /**
     * Get the item publish date.
     *
     * @return string The item publish date.
     */
    function getPubDate() { return $this->getProperty('pubDate'); }

    /**
     * Set the item title.
     *
     * @param string title The item title.
     */
    function setTitle($title) { $this->item_['title'] = $title; }

    /**
     * Set the item link.
     *
     * @param string link The item link.
     */
    function setLink($link) { $this->item_['link'] = $link; }

    /**
     * Set the item description.
     *
     * @param string description The item description.
     */
    function setDescription($description) { $this->item_['description'] = $description; }

    /**
     * set the item category.
     *
     * @param string category The item category.
     */
    function setCategory($category) { $this->item_['category'] = $category; }

    /**
     * Set the item publish date.
     *
     * @param string date The item publish date.
     */
    function setPubDate($date) { $this->item_['pubDate'] = $date; }

    /**
     * Custom set method for properties that do not have a dedicated
     * access method.
     *
     * @param string name The property name.
     * @param string value The value.
     */
    function setProperty($name, $value) { $this->item_[$name] = $value; }

    /**
     * Custom get method for properties that do not have a dedicated
     * access method.
     *
     * @param string name The property name.
     * @return string value The value or <code>null</code>.
     */
    function getProperty($name) { return isset($this->item_[$name]) ? $this->item_[$name] : null; }

    /**
     * Get all properties.
     *
     * @return array Name/value map.
     */
    function getProperties() { return $this->item_; }

}

?>
