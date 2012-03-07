<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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

use zenmagick\base\Runtime;

/**
 * Image information implementation for ImageHandler2 support.
 *
 * @author mano
 * @package org.zenmagick.plugins.imageHandler2
 */
class ImageInfo extends ZMImageInfo {
    private $plugin_;
    private $image_;
    private $formattedParameter_;
    private $zoomSmallImages_;


    /**
     * Create new image info.
     *
     * @param string image The image name; default is <code>null</code>.
     * @param string alt The alt text; default is an empty string.
     */
    public function __construct($image=null, $alt='') {
        parent::__construct($image, $alt);
        $this->image_ = $image;
        $this->formattedParameter_ = '';
        $this->plugin_ = Runtime::getContainer()->get('pluginService')->getPluginForId('productImages');
        $this->zoomSmallImages_ = null !== $this->plugin_ && $this->plugin_->get('zoomSmallImages');
    }


    /**
     * {@inheritDoc}
     */
    public function setDefaultImage($image) {
        parent::setDefaultImage($image);
        $this->image_ = $image;
    }

    /**
     * Get the default image.
     *
     * @return string The default image.
     */
    public function getDefaultImage() {
        $comp = ZMImageInfo::splitImageName($this->image_);
        $subdir = $comp[0];
        $ext = $comp[1];
        $imageBase = $comp[2];

        $newimg = $this->handle_image('images/'.$this->image_, $this->altText_, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, '');
        if ($this->zoomSmallImages_) {
            $this->formattedParameter_ = $newimg[4];
        }
        return $newimg[0];
    }

    /**
     * Check if there is a medium image.
     *
     * @return boolean <code>true</code> if there is a medium image, <code>false</code> if not.
     */
    public function hasMediumImage() { return true; }

    /**
     * Get the medium image.
     *
     * @return string The medium image.
     */
    public function getMediumImage() {
        $comp = ZMImageInfo::splitImageName($this->image_);
        $subdir = $comp[0];
        $ext = $comp[1];
        $imageBase = $comp[2];

        $medium = $imageBase.ZMSettings::get('imgSuffixMedium').$ext;
        $newimg = $this->handle_image('images/'.$medium, $this->altText_, MEDIUM_IMAGE_WIDTH, MEDIUM_IMAGE_HEIGHT, '');
        if ($this->zoomSmallImages_) {
            $this->formattedParameter_ = $newimg[4];
        }
        return $newimg[0];
    }

    /**
     * Get the large image.
     *
     * @return string The large image.
     */
    public function getLargeImage() {
        $comp = ZMImageInfo::splitImageName($this->image_);
        $subdir = $comp[0];
        $ext = $comp[1];
        $imageBase = $comp[2];

        $large = $imageBase.ZMSettings::get('imgSuffixLarge').$ext;
        $newimg = $this->handle_image('images/'.$large, $this->altText_, '', '', '');
        if ($this->zoomSmallImages_) {
            $this->formattedParameter_ = $newimg[4];
        }
        return $newimg[0];
    }

    /**
     * Check if there is a large image.
     *
     * @return boolean <code>true</code> if there is a large image, <code>false</code> if not.
     */
    public function hasLargeImage() { return true; }

    /**
     * Get the parameter formatted as <code>key="value" </code>.
     *
     * @return string HTML formatted parameter.
     */
    public function getFormattedParameter() {
        return $this->formattedParameter_.parent::getFormattedParameter();
    }

    /**
     * The actual IH2 function.
     *
     * @todo assimilate
     */
    private function handle_image($src, $alt, $width, $height, $parameters) {
        if ($this->plugin_->get('resize')) {
            $ih2Image = new ZMIh2Image($src, $width, $height);
            $src = $ih2Image->get_local();
            $parameters = $ih2Image->get_additional_parameters($alt, $ih2Image->canvas['width'], $ih2Image->canvas['height'], $parameters);
        } else {
            // default to standard Zen-Cart fallback behavior for large -> medium -> small images
            $image_ext = substr($src, strrpos($src, '.'));
            $image_base = substr($src, strlen('images/'), -strlen($image_ext));
            if (strrpos($src, IMAGE_SUFFIX_LARGE) && !is_file(ZC_INSTALL_PATH . $src)) {
                //large image wanted but not found
                $image_base = '/medium' . substr($image_base, strlen('/large'), -strlen(IMAGE_SUFFIX_LARGE)) . IMAGE_SUFFIX_MEDIUM;
                $src = 'images/' . $image_base . $image_ext;
            }
            if (strrpos($src, IMAGE_SUFFIX_MEDIUM) && !is_file(ZC_INSTALL_PATH . $src)) {
                //medium image wanted but not found
                $image_base = substr($image_base, strlen('/medium'), -strlen(IMAGE_SUFFIX_MEDIUM));
                $src = 'images/' . $image_base . $image_ext;
            }
        }
        return array($src, $alt, intval($width), intval($height), $parameters);
    }

}
