<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
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

use ZenMagick\Base\Beans;
use ZenMagick\Base\Runtime;
use ZenMagick\Base\Toolbox;
use ZenMagick\Base\ZMObject;

/**
 * Image information.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.model.catalog
 */
class ZMImageInfo extends ZMObject
{
    protected $imageDefault;
    protected $imageMedium;
    protected $imageLarge;
    protected $altText;
    protected $parameter;

    /**
     * Create new image info.
     *
     * @param string image The default image name; default is <code>null</code>.
     * @param string alt The alt text; default is an empty string.
     */
    public function __construct($image=null, $alt='')
    {
        parent::__construct();
        $this->altText = $alt;
        $this->parameter = array();
        $this->setDefaultImage($image);
    }

    /**
     * Set the default image.
     *
     * @param string image The default image.
     */
    public function setDefaultImage($image)
    {
        if (null != $image) {
            $comp = ZMImageInfo::splitImageName($image);
            $subdir = $comp[0];
            $ext = $comp[1];
            $imageBase = $comp[2];

            $netTool = $this->container->get('netTool');
            $settingsService = $this->container->get('settingsService');
            // @todo we don't really want to use images from where zencart is, but from where the app is
            $zcPath = $this->container->getParameter('zencart.root_dir');
            // set default image
            if (empty($image) || !file_exists($zcPath.'/images/'.$image) || !is_file($zcPath.'/images/'.$image)) {
                $this->imageDefault = $netTool->image($settingsService->get('imgNotFound'));
            } else {
                $this->imageDefault = $netTool->image($image);
            }

            // evaluate optional medium image
            $medium = $imageBase.$settingsService->get('imgSuffixMedium').$ext;
            if (!file_exists($zcPath.'/images/'.'medium/'.$medium)) {
                // default to next smaller version
                $this->imageMedium = $this->imageDefault;
            } else {
                $this->imageMedium = $netTool->image('medium/'.$medium);
            }

            // evaluate optional large image
            $large = $imageBase.$settingsService->get('imgSuffixLarge').$ext;
            if (!file_exists($zcPath.'/images/'.'large/'.$large)) {
                // default to next smaller version
                $this->imageLarge = $this->imageMedium;
            } else {
                $this->imageLarge = $netTool->image('large/'.$large);
            }
        }
    }

    /**
     * Check if there is an image.
     *
     * @return boolean <code>true</code> if there is an image, <code>false</code> if not.
     */
    public function hasImage() { return '' != $this->imageDefault; }

    /**
     * Get the default image.
     *
     * @return string The default image.
     */
    public function getDefaultImage() { return $this->imageDefault; }

    /**
     * Check if there is a medium image.
     *
     * @return boolean <code>true</code> if there is a medium image, <code>false</code> if not.
     */
    public function hasMediumImage() { return $this->imageMedium != $this->imageDefault; }

    /**
     * Get the medium image.
     *
     * @return string The medium image.
     */
    public function getMediumImage() { return $this->imageMedium; }

    /**
     * Get the large image.
     *
     * @return string The large image.
     */
    public function getLargeImage() { return $this->imageLarge; }

    /**
     * Check if there is a large image.
     *
     * @return boolean <code>true</code> if there is a large image, <code>false</code> if not.
     */
    public function hasLargeImage() { return $this->imageLarge != $this->imageMedium; }

    /**
     * Set the alt text.
     *
     * @param string text The alt text.
     */
    public function setAltText($text) { $this->altText = $text; }

    /**
     * Get the alt text.
     *
     * @return string The alt text.
     */
    public function getAltText() { return $this->altText; }

    /**
     * Set the parameter.
     *
     * @param mixed parameter Additional parameter for the <code>&lt;mg&gt;</code> tag; can be either
     *  a query string style list of name/value pairs or a map.
     */
    public function setParameter($parameter)
    {
        if (is_array($parameter)) {
            $this->parameter = $parameter;
        } elseif (!empty($parameter)) {
            parse_str($parameter, $this->parameter);
        }
    }

    /**
     * Get the parameter.
     *
     * @return array Map of key/value pairs.
     */
    public function getParameter() { return $this->parameter; }

    /**
     * Get the parameter formatted as <code>key="value" </code>.
     *
     * @return string HTML formatted parameter.
     */
    public function getFormattedParameter()
    {
        $html = '';
        foreach ($this->parameter as $attr => $value) {
            $html .= ' '.$attr.'="'.$value.'"';
        }

        return $html;
    }


    /**
     * Split image name into components that we need to process it.
     *
     * @param string image The image.
     * @return array An array consisting of [optional subdirectory], [file extension], [basename]
     */
    public static function splitImageName($image)
    {
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
     * @param string image The image to look up.
     * @return array An array of <code>ZMImageInfo</code> instances.
     */
    public static function getAdditionalImages($image)
    {
        $comp = ZMImageInfo::splitImageName($image);
        $subdir = $comp[0];
        $ext = $comp[1];
        $realImageBase = basename($comp[2]);

        // directory to scan
        $dirname = Runtime::getContainer()->getParameter('zencart.root_dir').'/images/'.$subdir;
        $imageList = array();
        if (is_dir($dirname) && ($dir = dir($dirname))) {
            while ($file = $dir->read()) {
                if (!is_dir($dirname . $file)) {
                    if (Toolbox::endsWith($file, $ext)) {
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
            $imageInfo = Beans::getBean('ZMImageInfo');
            $imageInfo->setDefaultImage($subdir.$aimg);
            $imageInfoList[] = $imageInfo;
        }

        return $imageInfoList;
    }

}
