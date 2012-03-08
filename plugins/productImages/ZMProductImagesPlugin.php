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

use zenmagick\base\Toolbox;

/**
 * Plugin to add product image support similar to Zen Cart's IH2.
 *
 * @package org.zenmagick.plugins.productImages
 * @author DerManoMann <mano@zenmagick.org>
 */
class ZMProductImagesPlugin extends Plugin {

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct('Product Images', 'Serious product image support', '${plugin.version}');
    }


    /**
     * {@inheritDoc}
     */
    public function install() {
        parent::install();
        $this->addConfigValue('Resize images', 'resize', true, 'Select to activate automatic resizing and caching of images',
            'widget@ZMBooleanFormWidget#name=resize&default=true&label=Automatic resize and caching');
        $this->addConfigValue('Watermark gravity', 'watermarkGravity', 'c', 'Select the position for the watermark relative to the image\'s canvas. Default is <strong>Center</Strong>',
            'widget@ZMSelectFormWidget#name=watermarkGravity&options='.urlencode('nw=North West&n=North&ne=North East&w=West&c=Center&e=east&sw=South West&s=South&se=South East'));

        // small
        $this->addConfigValue('Small image filetype', 'smallImageType', '', 'You better stick to \'gif\' for transparency or \'jpg\' for larger images. \'no change\' means use the same file extension for small images as uploaded image\'s.',
            'widget@ZMSelectFormWidget#name=smallImageType&options='.urlencode('gif=gif&jpg=jpg&png=png&no_change==no change'));
        $this->addConfigValue('Small image background', 'smallImageBackground', '255:255:255', 'If converted from an uploaded image with transparent areas, these areas become the specified color. Set to \'transparent\' to keep transparency');
        $this->addConfigValue('Watermark small images', 'watermarkSmallImages', false, 'Select if you want to show watermarked small images',
            'widget@ZMBooleanFormWidget#name=watermarkSmallImages&default=false&label=Add watermark');
        $this->addConfigValue('Zoom small images', 'zoomSmallImages', true, 'Select if you want to enable a nice zoom overlay while hovering the mouse pointer over small images',
            'widget@ZMBooleanFormWidget#name=zoomSmallImages&default=true&label=Add hover zoom');
        $this->addConfigValue('Small image compression quality', 'smallImageQuality', '85', 'Specify the desired image quality for small jpg images, decimal values ranging from 0 to 100. Higher is better quality and takes more space. Default is 85 which is ok unless you have very specific needs');

        // medium
        $this->addConfigValue('Medium image filetype', 'mediumImageType', '', 'You better stick to \'gif\' for transparency or \'jpg\' for larger images. \'no change\' means use the same file extension for medium images as uploaded image\'s.',
            'widget@ZMSelectFormWidget#name=mediumImageType&options='.urlencode('gif=gif&jpg=jpg&png=png&no_change==no change'));
        $this->addConfigValue('Medium image background', 'mediumImageBackground', '255:255:255', 'If converted from an uploaded image with transparent areas, these areas become the specified color. Set to \'transparent\' to keep transparency');
        $this->addConfigValue('Watermark medium images', 'watermarkMediumImages', false, 'Select if you want to show watermarked medium images',
            'widget@ZMBooleanFormWidget#name=watermarkMediumImages&default=false&label=Add watermark');
        $this->addConfigValue('Medium image compression quality', 'mediumImageQuality', '85', 'Specify the desired image quality for medium jpg images, decimal values ranging from 0 to 100. Higher is better quality and takes more space. Default is 85 which is ok unless you have very specific needs');

        // large
        $this->addConfigValue('Large image filetype', 'largeImageType', '', 'You better stick to \'gif\' for transparency or \'jpg\' for larger images. \'no change\' means use the same file extension for large images as uploaded image\'s.',
            'widget@ZMSelectFormWidget#name=largeImageType&options='.urlencode('gif=gif&jpg=jpg&png=png&no_change=no change'));
        $this->addConfigValue('Large image background', 'largeImageBackground', '255:255:255', 'If converted from an uploaded image with transparent areas, these areas become the specified color. Set to \'transparent\' to keep transparency');
        $this->addConfigValue('Watermark large images', 'watermarkLargeImages', false, 'Select if you want to show watermarked large images',
            'widget@ZMBooleanFormWidget#name=watermarkLargeImages&default=false&label=Add watermark');
        $this->addConfigValue('Large image compression quality', 'largeImageQuality', '85', 'Specify the desired image quality for large jpg images, decimal values ranging from 0 to 100. Higher is better quality and takes more space. Default is 85 which is ok unless you have very specific needs');
        $this->addConfigValue('Large image maximum width', 'largeImageMaxWidth', '750', 'Specify a maximum width for your large images. If width and height are empty or set to 0, no resizing of large images is done');
        $this->addConfigValue('Large image maximum height', 'largeImageMaxHeight', '550', 'Specify a maximum height for your large images. If width and height are empty or set to 0, no resizing of large images is done');
    }

    /**
     * {@inheritDoc}
     */
    public function init() {
        parent::init();
        zenmagick\base\Runtime::getEventDispatcher()->listen($this);
    }

    /**
     * Add resources.
     */
    public function onViewStart($event) {
        if (Toolbox::asBoolean($this->get('zoomSmallImages'))) {
            if (null != ($resourceManager = $event->get('view')->getVariable('resourceManager'))) {
                // might be null in case of redirect/forward/etc
                $resourceManager->cssFile('ih2/style_imagehover.css');
                $resourceManager->jsFile('ih2/jscript_imagehover.js', $resourceManager::FOOTER);
            }
        }
    }

    /**
     * Returns the name of a cachefile from given data
     *
     * The needed directory is created by this function!
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @author Tim Kroeger <tim@breakmyzencart.com>
     *
     * @param string $data  This data is used to create a unique md5 name
     * @param string $ext   This is appended to the filename if given
     * @return string       The filename of the cachefile
     */
    public static function getCacheName($data, $ext='') {
        $md5  = md5($data);
        $dir = ZMSettings::get('plugins.imageHandler2.cachedir') . '/' . $md5{0} . '/' . $md5.$ext;
        $this->container->get('filesystem')->mkdir($dir, 0755);
        return $file;
    }

}
