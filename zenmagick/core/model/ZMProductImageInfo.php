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
 * Product images.
 *
 * @author mano
 * @package net.radebatz.zenmagick.model
 * @version $Id$
 */
class ZMProductImageInfo {
    var $product_;
    var $ext_;
    var $imageBase_;
    var $imageDefault_;
    var $imageMedium_;
    var $imageLarge_;


    // create new instance
    function ZMProductImageInfo($product) {
        $this->product_ = $product;
        $image = $product->image_;
        $this->ext_ = substr($image, strrpos($image, '.'));
        if ('' != $image) {
            $this->imageBase_ = ereg_replace($this->ext_, '', $image);
        } else {
            $this->imageBase_ = '';
        }
        $this->imageMedium_ = $this->imageBase_ . IMAGE_SUFFIX_MEDIUM . $this->ext_;
        $this->imageLarge_ = $this->imageBase_ . IMAGE_SUFFIX_LARGE . $this->ext_;

        // validate files
        $this->imageDefault_ = DIR_WS_IMAGES . $image;

        if (!file_exists(DIR_WS_IMAGES . 'medium/' . $this->imageMedium_)) {
            $this->imageMedium_ = DIR_WS_IMAGES . $image;
        } else {
            $this->imageMedium_ = DIR_WS_IMAGES . 'medium/' . $this->imageMedium_;
        }

        if (!file_exists(DIR_WS_IMAGES . 'large/' . $this->imageLarge_)) {
            $this->imageLarge_ = $this->imageMedium_;
        } else {
            $this->imageLarge_ = DIR_WS_IMAGES . 'large/' . $this->imageLarge_;
        }
    }

    // create new instance
    function __construct($product) {
        $this->ZMProductImageInfo($product);
    }

    function __destruct() {
    }


    // getter/setter
    function hasImage() { return '' != $this->product_->getDefaultImage(); }
    function getDefaultImage() { return $this->imageDefault_; }
    function hasMediumImage() { return $this->imageMedium_ != $this->imageDefault_; }
    function getMediumImage() { return $this->imageMedium_; }
    function getLargeImage() { return $this->imageLarge_; }
    function hasLargeImage() { return $this->imageLarge_ != $this->imageMedium_; }

}

?>
