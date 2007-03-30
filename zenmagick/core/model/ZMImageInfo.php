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
 * Image information.
 *
 * @author mano
 * @package net.radebatz.zenmagick.model
 * @version $Id$
 */
class ZMImageInfo extends ZMModel {
    var $imageDefault_;
    var $imageMedium_;
    var $imageLarge_;


    /**
     * Create new image info.
     *
     * @param string image The image name.
     */
    function ZMImageInfo($image) {
        parent::__construct();

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

    /**
     * Create new image info.
     *
     * @param string image The image name.
     */
    function __construct($product) {
        $this->ZMImageInfo($product);
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Check if there is an image.
     *
     * @return bool <code>true</code> if there is an image, <code>false</code> if not.
     */
    function hasImage() { return '' != $this->imageDefault_; }

    /**
     * Get the default image.
     *
     * @return string The default image.
     */
    function getDefaultImage() { return $this->imageDefault_; }

    /**
     * Check if there is a medium image.
     *
     * @return bool <code>true</code> if there is a medium image, <code>false</code> if not.
     */
    function hasMediumImage() { return $this->imageMedium_ != $this->imageDefault_; }

    /**
     * Get the medium image.
     *
     * @return string The medium image.
     */
    function getMediumImage() { return $this->imageMedium_; }

    /**
     * Check if there is a large image.
     *
     * @return bool <code>true</code> if there is a large image, <code>false</code> if not.
     */
    function getLargeImage() { return $this->imageLarge_; }

    /**
     * Get the large image.
     *
     * @return string The large image.
     */
    function hasLargeImage() { return $this->imageLarge_ != $this->imageMedium_; }

}

?>
