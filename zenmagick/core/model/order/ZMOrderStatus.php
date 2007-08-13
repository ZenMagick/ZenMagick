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
 * Order status.
 *
 * @author mano
 * @package net.radebatz.zenmagick.model.order
 * @version $Id$
 */
class ZMOrderStatus extends ZMModel {
    var $id_;
    var $name_;
    var $dateAdded_;
    var $comment_;


    /**
     * Create new status.
     *
     * @param int id The order status id.
     * @param string name The status name/text.
     * @param date dateAdded The date the status was added to the order.
     * @param string comment An optional comment.
     */
    function ZMOrderStatus($id, $name, $dateAdded, $comment=null) {
        parent::__construct();

		    $this->id_ = $id;
		    $this->name_ = $name;
		    $this->dateAdded_ = $dateAdded;
		    $this->comment_ = $comment;
    }

    /**
     * Create new status.
     *
     * @param int id The order status id.
     * @param string name The status name/text.
     * @param date dateAdded The date the status was added to the order.
     * @param string comment An optional comment.
     */
    function __construct($id, $name, $dateAdded, $comment=null) {
        $this->ZMOrderStatus($id, $name, $dateAdded, $comment);
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the order status id.
     *
     * @return int The order status id.
     */
    function getId() { return $this->id_; }

    /**
     * Get the order status name.
     *
     * @return string The order status name.
     */
    function getName() { return $this->name_; }

    /**
     * Get the date it was added.
     *
     * @return string The date the attribute was added.
     */
    function getDateAdded() { return $this->dateAdded_; }

    /**
     * Checks if a comment exists for this status.
     *
     * @return boolean </code>true</code> if a comment exist, <code>false</code> if not.
     */
    function hasComment() { return zm_not_null($this->comment_); }

    /**
     * Get the comment.
     *
     * @return string The comment (might be empty).
     */
    function getComment() { return $this->comment_; }

}

?>
