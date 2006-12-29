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
 * Image information.
 *
 * @author mano
 * @package net.radebatz.zenmagick.model
 * @version $Id$
 */
class ZMImageInfo {
    var $imageDefault_;
    var $imageMedium_;
    var $imageLarge_;


    /**
     * Create new image info.
     *
     * @param string image The image name.
     */
    function ZMImageInfo($image) {
        $comp = _zm_split_image_name($image);
        $subdir = $comp[0];
        $ext = $comp[1];
        $imageBase = $comp[2];

        // set default image
        $this->imageDefault_ = zm_image_href($image, false);

        // evaluate optional medium image
        $medium = $imageBase.zm_setting('imgSuffixMedium').$ext;
        $medium = zm_image_href('medium/'.$medium, false);
        if (!file_exists($medium)) {
            // default to next smaller version
            $this->imageMedium_ = $this->imageDefault_;
        } else {
            $this->imageMedium_ = $medium;
        }

        // evaluate optional large image
        $large = $imageBase.zm_setting('imgSuffixLarge').$ext;
        $large = zm_image_href('large/'.$large, false);
        if (!file_exists($large)) {
            // default to next smaller version
            $this->imageLarge_ = $this->imageMedium_;
        } else {
            $this->imageLarge_ = $large;
        }
    }


    // create new instance
    function __construct($product) {
        $this->ZMImageInfo($product);
    }

    function __destruct() {
    }


    // getter/setter
    function hasImage() { return '' != $this->imageDefault_; }
    function getDefaultImage() { return $this->imageDefault_; }
    function hasMediumImage() { return $this->imageMedium_ != $this->imageDefault_; }
    function getMediumImage() { return $this->imageMedium_; }
    function getLargeImage() { return $this->imageLarge_; }
    function hasLargeImage() { return $this->imageLarge_ != $this->imageMedium_; }

}

?>
