<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 * @author DerManoMann
 * @package org.zenmagick.store.model.catalog
 * @version $Id: ZMImageInfo.php 2124 2009-03-31 09:23:51Z dermanomann $
 */
class ZMImageInfo extends ZMObject {
    protected $imageDefault_;
    protected $imageMedium_;
    protected $imageLarge_;
    protected $altText_;
    protected $parameter_;


    /**
     * Create new image info.
     *
     * @param string image The image name.
     * @param string alt The alt text.
     */
    function __construct($image, $alt='') {
        parent::__construct();
        $this->altText_ = $alt;
        $this->parameter_ = array();

        $comp = ZMImageInfo::splitImageName($image);
        $subdir = $comp[0];
        $ext = $comp[1];
        $imageBase = $comp[2];

        // set default image
        if (empty($image) || !file_exists(DIR_FS_CATALOG.DIR_WS_IMAGES.$image) || !is_file(DIR_FS_CATALOG.DIR_WS_IMAGES.$image)) {
            $this->imageDefault_ = ZMToolbox::instance()->net->image(ZMSettings::get('imgNotFound'), false);
        } else {
            $this->imageDefault_ = ZMToolbox::instance()->net->image($image, false);
        }

        // evaluate optional medium image
        $medium = $imageBase.ZMSettings::get('imgSuffixMedium').$ext;
        if (!file_exists(DIR_FS_CATALOG.DIR_WS_IMAGES.'medium/'.$medium)) {
            // default to next smaller version
            $this->imageMedium_ = $this->imageDefault_;
        } else {
            $this->imageMedium_ = ZMToolbox::instance()->net->image('medium/'.$medium, false);
        }

        // evaluate optional large image
        $large = $imageBase.ZMSettings::get('imgSuffixLarge').$ext;
        if (!file_exists(DIR_FS_CATALOG.DIR_WS_IMAGES.'large/'.$large)) {
            // default to next smaller version
            $this->imageLarge_ = $this->imageMedium_;
        } else {
            $this->imageLarge_ = ZMToolbox::instance()->net->image('large/'.$large, false);
        }
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Check if there is an image.
     *
     * @return boolean <code>true</code> if there is an image, <code>false</code> if not.
     */
    public function hasImage() { return '' != $this->imageDefault_; }

    /**
     * Get the default image.
     *
     * @return string The default image.
     */
    public function getDefaultImage() { return $this->imageDefault_; }

    /**
     * Check if there is a medium image.
     *
     * @return boolean <code>true</code> if there is a medium image, <code>false</code> if not.
     */
    public function hasMediumImage() { return $this->imageMedium_ != $this->imageDefault_; }

    /**
     * Get the medium image.
     *
     * @return string The medium image.
     */
    public function getMediumImage() { return $this->imageMedium_; }

    /**
     * Get the large image.
     *
     * @return string The large image.
     */
    public function getLargeImage() { return $this->imageLarge_; }

    /**
     * Check if there is a large image.
     *
     * @return boolean <code>true</code> if there is a large image, <code>false</code> if not.
     */
    public function hasLargeImage() { return $this->imageLarge_ != $this->imageMedium_; }

    /**
     * Get the alt text.
     *
     * @return string The alt text.
     */
    public function getAltText() { return $this->altText_; }

    /**
     * Set the parameter.
     *
     * @param mixed parameter Additional parameter for the <code>&lt;mg&gt;</code> tag; can be either
     *  a query string style list of name/value pairs or a map.
     */
    public function setParameter($parameter) {
        if (is_array($parameter)) {
            $this->parameter_ = $parameter;
        } else if (!empty($parameter)) {
            parse_str($parameter, $this->parameter_);
        }
    }

    /**
     * Get the parameter.
     *
     * @return array Map of key/value pairs.
     */
    public function getParameter() { return $this->parameter_; }

    /**
     * Get the parameter formatted as <code>key="value" </code>.
     *
     * @return string HTML formatted parameter.
     */
    public function getFormattedParameter() { 
        $html = '';
        foreach ($this->parameter_ as $attr => $value) {
            $html .= ' '.$attr.'="'.$value.'"';
        }

        return $html;
    }


    /**
     * Split image name into components that we need to process it.
     *
     * @package org.zenmagick
     * @param string image The image.
     * @return array An array consisting of [optional subdirectory], [file extension], [basename]
     */
    public static function splitImageName($image) {
        // optional subdir on all levels
        $subdir = dirname($image);
        $subdir = "." == $subdir ? "" : $subdir."/";

        // the file extension
        $ext = substr($image, strrpos($image, '.'));

        // filename without suffix
        $basename = '';
        if ('' != $image) {
            $basename = preg_replace('/'.$ext.'/', '', $image);
        }

        return array($subdir, $ext, $basename);
    }

    /**
     * Look up additional product images.
     *
     * @package org.zenmagick
     * @param string image The image to look up.
     * @return array An array of <code>ZMImageInfo</code> instances.
     */
    public static function getAdditionalImages($image) {
        $comp = ZMImageInfo::splitImageName($image);
        $subdir = $comp[0];
        $ext = $comp[1];
        $realImageBase = basename($comp[2]);

        // directory to scan
        $dirname = DIR_FS_CATALOG.DIR_WS_IMAGES.$subdir;

        $imageList = array();
        if ($dir = @dir($dirname)) {
            while ($file = $dir->read()) {
                if (!is_dir($dirname . $file)) {
                    if (ZMTools::endsWith($file, $ext)) {
                        if (1 == preg_match("/" . $realImageBase . "/i", $file)) {
                            if ($file != basename($image)) {
                                if ($realImageBase . preg_replace('/'.$realImageBase.'/', '', $file) == $file) {
                                    array_push($imageList, $file);
                                }
                            }
                        }
                    }
                }
            }
            $dir->close();
            sort($imageList);
        }

        // create ZMImageInfo list...
        $imageInfoList = array();
        foreach ($imageList as $aimg) {
            array_push($imageInfoList, ZMLoader::make("ImageInfo", $subdir.$aimg));
        }

        return $imageInfoList;
    }

}

?>
