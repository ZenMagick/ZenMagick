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
 */
?>
<?php


/**
 * A single review.
 *
 * @author mano
 * @package net.radebatz.zenmagick.model
 * @version $Id$
 */
class ZMReview {
    var $id_;
    var $rating_;
    var $productId_;
    var $productName_;
    var $productImage_;
    var $text_;
    var $dateAdded_;
    var $author_;


    /**
     * Default c'tor.
     */
    function ZMReview() {
        $this->id_ = 0;
        $this->rating_ = 0;
        $this->productId_ = 0;
        $this->productName_ = '';
        $this->productImage_ = '';
        $this->text_ = '';
        $this->dateAdded_ = '';
        $this->author_ = '';
    }

    // create new instance
    function __construct() {
        $this->ZMReview();
    }

    function __destruct() {
    }


    // getter/setter
    function getId() { return $this->id_; }
    function getRating() { return $this->rating_; }
    function getProductId() { return $this->productId_; }
    function getProductName() { return $this->productName_; }
    function getProductImage() { return $this->productImage_; }
    function getText() { return $this->text_; }
    function getDateAdded() { return $this->dateAdded_; }
    function getAuthor() { return $this->author_; }

}

?>
