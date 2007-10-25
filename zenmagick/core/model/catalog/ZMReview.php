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
 * A single review.
 *
 * @author mano
 * @package org.zenmagick.model.catalog
 * @version $Id$
 */
class ZMReview extends ZMModel {
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
        parent::__construct();

        $this->id_ = 0;
        $this->rating_ = 0;
        $this->productId_ = 0;
        $this->productName_ = '';
        $this->productImage_ = null;
        $this->text_ = '';
        $this->dateAdded_ = '';
        $this->author_ = '';
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMReview();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Populate all available fields from the given request.
     *
     * @param array req A request; if <code>null</code>, use the current <code>ZMRequest</code> instead.
     */
    function populate($req=null) {
    global $zm_request;

        $this->rating_ = $zm_request->getParameter('rating', 0);
        $this->productId_ = $zm_request->getParameter('products_id', 0);
        $this->text_ = $zm_request->getParameter('review_text', '');
        return;
    }

    /**
     * Get the review id.
     *
     * @return int The review id.
     */
    function getId() { return $this->id_; }

    /**
     * Get the rating.
     *
     * @return int The review rating.
     */
    function getRating() { return $this->rating_; }

    /**
     * Get the review product id.
     *
     * @return int The review product id.
     */
    function getProductId() { return $this->productId_; }

    /**
     * Get the review product name.
     *
     * @return int The review product name.
     */
    function getProductName() { return $this->productName_; }

    /**
     * Get the review product image.
     *
     * @return string The review product image.
     */
    function getProductImage() { return $this->productImage_; }

    /**
     * Get the review product image info.
     *
     * @return ZMProductInfo The product image info.
     */
    function getProductImageInfo() { return $this->create("ImageInfo", $this->productImage_, $this->productname_); }

    /**
     * Get the review text.
     *
     * @return string The review text.
     */
    function getText() { return $this->text_; }

    /**
     * Get the date the review was added.
     *
     * @return string The added date.
     */
    function getDateAdded() { return $this->dateAdded_; }

    /**
     * Get the review author.
     *
     * @return string The name of the author.
     */
    function getAuthor() { return $this->author_; }

}

?>
