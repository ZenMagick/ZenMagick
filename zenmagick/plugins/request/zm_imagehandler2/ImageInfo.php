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
 * Image information implementation for ImageHandler2 support.
 *
 * @author mano
 * @package net.radebatz.zenmagick.plugins.zm_imagehandler2
 * @version $Id$
 */
class ImageInfo extends ZMImageInfo {
    var $image_;
    var $formattedParameter_;

    /**
     * Create new image info.
     *
     * @param string image The image name.
     * @param string alt The alt text.
     */
    function ImageInfo($image, $alt='') {
        parent::__construct($image, $alt);

        $this->image_ = $image;
        $this->formattedParameter_ = '';
    }

    /**
     * Create new image info.
     *
     * @param string image The image name.
     * @param string alt The alt text.
     */
    function __construct($image, $alt='') {
        $this->ImageInfo($image, $alt);
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the default image.
     *
     * @return string The default image.
     */
    function getDefaultImage() { 
        $comp = _zm_split_image_name($this->image_);
        $subdir = $comp[0];
        $ext = $comp[1];
        $imageBase = $comp[2];

        $newimg = handle_image(DIR_WS_IMAGES.$this->image_, $this->altText_, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, '');
        $this->formattedParameter_ = $newimg[4];
        return $newimg[0];
    }

    /**
     * Check if there is a medium image.
     *
     * @return boolean <code>true</code> if there is a medium image, <code>false</code> if not.
     */
    function hasMediumImage() { return true; }

    /**
     * Get the medium image.
     *
     * @return string The medium image.
     */
    function getMediumImage() {
        $comp = _zm_split_image_name($this->image_);
        $subdir = $comp[0];
        $ext = $comp[1];
        $imageBase = $comp[2];

        $medium = $imageBase.zm_setting('imgSuffixMedium').$ext;
        $newimg = handle_image(DIR_WS_IMAGES.$medium, $this->altText_, MEDIUM_IMAGE_WIDTH, MEDIUM_IMAGE_HEIGHT, '');
        $this->formattedParameter_ = $newimg[4];
        return $newimg[0];
    }

    /**
     * Get the large image.
     *
     * @return string The large image.
     */
    function getLargeImage() {
        $comp = _zm_split_image_name($this->image_);
        $subdir = $comp[0];
        $ext = $comp[1];
        $imageBase = $comp[2];

        $large = $imageBase.zm_setting('imgSuffixLarge').$ext;
        $newimg = handle_image(DIR_WS_IMAGES.$large, $this->altText_, '', '', '');
        $this->formattedParameter_ = $newimg[4];
        return $newimg[0];
    }

    /**
     * Check if there is a large image.
     *
     * @return boolean <code>true</code> if there is a large image, <code>false</code> if not.
     */
    function hasLargeImage() { return true; }

    /**
     * Get the parameter formatted as <code>key="value" </code>.
     *
     * @return string HTML formatted parameter.
     */
    function getFormattedParameter() { 
        return $this->formattedParameter_;
    }

}

?>
