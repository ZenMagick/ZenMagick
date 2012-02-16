<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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

use zenmagick\base\ZMObject;

/**
 * Gravatar plugin.
 *
 * @package org.zenmagick.plugins.gravatar
 * @author DerManoMann <mano@zenmagick.org>
 */
class ZMGravatarPlugin extends Plugin {

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct('Gravatar', 'Adds gravatar support to accounts.', '${plugin.version}');
        $this->setContext('storefront');
    }


    /**
     * {@inheritDoc}
     */
    public function install() {
        parent::install();

        $this->addConfigValue('Image Size', 'defaultSize', '80', 'Default avatar size');
        $this->addConfigValue('Default Image Set', 'imageSet', 'mm', 'Default image if no avatar found',
            'widget@ZMSelectFormWidget#name=imageSet&options='.urlencode('404=404&mm=Mystery Man&identicon=Identicon geometrical pattern&monsterid=Monster&wavartar=Generated faces&retro=Retro Pixels'));
        $this->addConfigValue('Maximum Rating', 'rating', 'g', 'Maximum rating of avatar images allowed',
            'widget@ZMSelectFormWidget#name=rating&options='.urlencode('g=G - General&pg=PG - Rude, mild violence&r=R - Rated&x=X - Rated'));
    }

    /**
     * {@inheritDoc}
     */
    public function init() {
        parent::init();
        // attach method to ZMAccount
        ZMObject::attachMethod('getGravatar', 'ZMAccount', array($this, 'getGravatar'));
    }

    /**
     * Get avatar.
     *
     * @param mixed email The email address or <code>ZMAccount</code> instance.
     * @param string size Size in pixels; default is null to use the system default.
     * @param boole img <code>true</code> to return a complete <code>IMG</code> tag, <code>false</code> for just the URL; default is <code>true</code>.
     * @param array attributes Optional, additional key/value attributes to include in the IMG tag; default is an empty array.
     */
    public function getGravatar($email, $size=null, $img=true, array $attributes=array()) {
        if ($email instanceof ZMAccount) {
            $email = $email->getEmail();
        }
        $size = null != $size ? $size : $this->get('defaultSize');
        $imageSet = $this->get('imageSet');
        $rating = $this->get('rating');
        return $this->pullGravatar($email, $size, $imageSet, $rating, $img, $attributes);
    }

    /**
     * Pull either a Gravatar URL or complete image tag for a specified email address.
     *
     * @param string $email The email address
     * @param string $s Size in pixels, defaults to 80px [ 1 - 512 ]
     * @param string $d Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ]
     * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]
     * @param boole $img True to return a complete IMG tag False for just the URL
     * @param array $atts Optional, additional key/value attributes to include in the IMG tag
     * @return String containing either just a URL or a complete image tag
     * @source http://gravatar.com/site/implement/images/php/
     */
    private function pullGravatar($email, $s=80, $d='mm', $r='g', $img=false, $atts=array()) {
        $url = 'http://www.gravatar.com/avatar/';
        $url .= md5(strtolower(trim($email)));
        $url .= "?s=$s&d=$d&r=$r";
        if ($img) {
            $slash = $this->container->get('settingsService')->get('zenmagick.mvc.html.xhtml') ? '/' : '';
            $url = '<img src="' . $url . '"';
            foreach ($atts as $key => $val)
                $url .= ' ' . $key . '="' . $val . '"';
            $url .= ' '.$slash.'>';
        }
        return $url;
    }

}
