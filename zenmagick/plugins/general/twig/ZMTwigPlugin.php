<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
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
 * Twig support for ZenMagick.
 *
 * @package org.zenmagick.plugins.twig
 * @author DerManoMann
 * @version $Id$
 */
class ZMTwigPlugin extends Plugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Twig', 'Adds Twig support to ZenMagick', '${plugin.version}');
        $this->setLoaderPolicy(ZMPlugin::LP_FOLDER);
        $this->setContext(Plugin::CONTEXT_STOREFRONT);
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * {@inheritDoc}
     */
    public function init() {
        ZMEvents::instance()->attach($this);
        require ZMFileUtils::mkPath(array($this->getPluginDirectory(), 'twig', 'lib', 'Twig', 'Autoloader.php'));
        Twig_Autoloader::register();
    }


    /**
     * Set default view class *after* all loading is done (including the store defaults).
     */
    public function onZMInitDone($args=null) {
        ZMSettings::set('zenmagick.mvc.view.default', 'TwigView');
        ZMSettings::set('zenmagick.mvc.templates.ext', '.twig');
    }

}

?>
