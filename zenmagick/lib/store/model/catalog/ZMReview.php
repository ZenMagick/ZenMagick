<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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
 * @author DerManoMann
 * @package org.zenmagick.store.model.catalog
 * @version $Id$
 */
class ZMReview extends ZMObject {
    private $rating_;
    private $productId_;
    private $text_;
    private $dateAdded_;
    private $author_;
    private $languageId_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->setId(0);
        $this->rating_ = 0;
        $this->productId_ = 0;
        $this->text_ = null;
        $this->dateAdded_ = '';
        $this->author_ = null;
        $this->languageId_ = 0;
        $this->setActive(true);
        $this->setViewCount(0);
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the review id.
     *
     * @return int The review id.
     */
    public function getId() { return $this->get('reviewId'); }

    /**
     * Get the rating.
     *
     * @return int The review rating.
     */
    public function getRating() { return $this->rating_; }

    /**
     * Get the view counter.
     *
     * @return int The view counter.
     */
    public function getViewCount() { return $this->get('viewCount'); }

    /**
     * Get the review product id.
     *
     * @return int The review product id.
     */
    public function getProductId() { return $this->productId_; }

    /**
     * Check if this review is active.
     *
     * @return boolean <code>true</code> if the review is active.
     */
    public function isActive() { return $this->get('status'); }

    /**
     * Get the review product name.
     *
     * @return string The review product name.
     */
    public function getProductName() { return $this->get('name'); }

    /**
     * Get the review product image.
     *
     * @return string The review product image.
     */
    public function getProductImage() { return $this->get('image'); }

    /**
     * Get the review product image info.
     *
     * @return ZMProductInfo The product image info.
     */
    public function getProductImageInfo() { return ZMProducts::instance()->getProductForId($this->productId_)->getImageInfo(); }

    /**
     * Get the review text.
     *
     * @return string The review text.
     */
    public function getText() { return $this->text_; }

    /**
     * Get the date the review was added.
     *
     * @return string The added date.
     */
    public function getDateAdded() { return $this->dateAdded_; }

    /**
     * Get the review author.
     *
     * @return string The name of the author.
     */
    public function getAuthor() { return $this->author_; }

    /**
     * Get the lanugage id.
     *
     * @return int The lanugage id.
     */
    public function getLanguageId() { return $this->languageId_; }

    /**
     * Set the review id.
     *
     * @param int id The review id.
     */
    public function setId($id) { $this->set('reviewId', $id); }

    /**
     * Set the rating.
     *
     * @param int rating The review rating.
     */
    public function setRating($rating) { $this->rating_ = $rating; }

    /**
     * Set the view counter.
     *
     * @param int viewCount The view counter.
     */
    public function setViewCount($viewCount) { $this->set('viewCount', $viewCount); }

    /**
     * Set the review product id.
     *
     * @param int productId The review product id.
     */
    public function setProductId($productId) { $this->productId_ = $productId; }

    /**
     * Set the reviews active state.
     *
     * @param boolean value <code>true</code> if the review is active.
     */
    public function setActive($value) { $this->set('status', $value); }

    /**
     * Set the review product name.
     *
     * @param string name The review product name.
     */
    public function setProductName($name) { $this->set('name', $name); }

    /**
     * Set the review product image.
     *
     * @param string image The review product image.
     */
    public function setProductImage($image) { $this->set('image', $image); }

    /**
     * Set the review text.
     *
     * @param string text The review text.
     */
    public function setText($text) { $this->text_ = $text; }

    /**
     * Set the date the review was added.
     *
     * @param string date The added date.
     */
    public function setDateAdded($date) { $this->dateAdded_ = $date; }

    /**
     * Set the review author.
     *
     * @param string author The name of the author.
     */
    public function setAuthor($author) { $this->author_ = $author; }

    /**
     * Set the lanugage id.
     *
     * @param int id The lanugage id.
     */
    public function setLanguageId($languageId) { $this->languageId_ = $languageId; }

}
