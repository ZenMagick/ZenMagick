<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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

use zenmagick\base\Runtime;

/**
 * Image information implementation for ImageHandler2 support.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.plugins.imageHandler2
 */
class ImageInfo extends ZMImageInfo {
    private $image_;
    private $formattedParameter_;
    private $disableIH2Attributes_;


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
        $plugin = Runtime::getContainer()->get('pluginService')->getPluginForId('imageHandler2');
        $this->disableIH2Attributes_ = null !== $plugin && $plugin->get('disableIH2Attributes');
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

        if (function_exists('handle_image')) {
            $newimg = handle_image('images/'.$this->image_, $this->altText_, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, '');
        } else {
            $newimg = array('images/'.$this->image_, $this->altText_, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, '');
        }

        if (!$this->disableIH2Attributes_) {
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

        $medium = $imageBase.$this->container->get('settingsService')->get('imgSuffixMedium').$ext;

        if (function_exists('handle_image')) {
            $newimg = handle_image('images/'.$medium, $this->altText_, MEDIUM_IMAGE_WIDTH, MEDIUM_IMAGE_HEIGHT, '');
        } else {
            $newimg = array('images/'.$medium, $this->altText_, MEDIUM_IMAGE_WIDTH, MEDIUM_IMAGE_HEIGHT, '');
        }

        if (!$this->disableIH2Attributes_) {
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

        $large = $imageBase.$this->container->get('settingsService')->get('imgSuffixLarge').$ext;
        $newimg = handle_image('images/'.$large, $this->altText_, '', '', '');
        if (!$this->disableIH2Attributes_) {
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

}
