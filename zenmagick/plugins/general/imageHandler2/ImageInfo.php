<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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
 * @package org.zenmagick.plugins.imageHandler2
 */
class ImageInfo extends ZMImageInfo {
    private $image_;
    private $formattedParameter_;
    private $zoomSmallImages_;


    /**
     * Create new image info.
     *
     * @param string image The image name.
     * @param string alt The alt text.
     */
    function __construct($image, $alt='') {
        parent::__construct($image, $alt);
        $this->image_ = $image;
        $this->formattedParameter_ = '';
        $plugin = ZMPlugins::instance()->getPluginForId('imageHandler2');
        $this->zoomSmallImages_ = null !== $plugin && $plugin->get('zoomSmallImages');
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
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

        $newimg = $this->handle_image(DIR_WS_IMAGES.$this->image_, $this->altText_, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, '');
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
        $newimg = $this->handle_image(DIR_WS_IMAGES.$medium, $this->altText_, MEDIUM_IMAGE_WIDTH, MEDIUM_IMAGE_HEIGHT, '');
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
        $newimg = $this->handle_image(DIR_WS_IMAGES.$large, $this->altText_, '', '', '');
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
        return $this->formattedParameter_;
    }

    /**
     * The actual IH2 function.
     *
     * @todo assimilate
     */
    private function handle_image($src, $alt, $width, $height, $parameters) {
        global $ihConf;
        
        if ($ihConf['resize']) {
            $ih_image = new ZMIh2Image($src, $width, $height);
          // override image path, get local image from cache
          if ($ih_image) { 
            $src = $ih_image->get_local();
            $parameters = $ih_image->get_additional_parameters($alt, $ih_image->canvas['width'], $ih_image->canvas['height'], $parameters);
          }
        } else {
          // default to standard Zen-Cart fallback behavior for large -> medium -> small images
          $image_ext = substr($src, strrpos($src, '.'));
          $image_base = substr($src, strlen(DIR_WS_IMAGES), -strlen($image_ext));
          if (strrpos($src, IMAGE_SUFFIX_LARGE) && !is_file(DIR_FS_CATALOG . $src)) {
            //large image wanted but not found
            $image_base = $ihConf['medium']['prefix'] . substr($image_base, strlen($ihConf['large']['prefix']), -strlen($ihConf['large']['suffix'])) . $ihConf['medium']['suffix'];
            $src = DIR_WS_IMAGES . $image_base . $image_ext;
          }
          if (strrpos($src, IMAGE_SUFFIX_MEDIUM) && !is_file(DIR_FS_CATALOG . $src)) {
            //medium image wanted but not found
            $image_base = substr($image_base, strlen($ihConf['medium']['prefix']), -strlen($ihConf['medium']['suffix'])); 
            $src = DIR_WS_IMAGES . $image_base . $image_ext;
          }
        }
        return array($src, $alt, intval($width), intval($height), $parameters);
    }

}

?>
