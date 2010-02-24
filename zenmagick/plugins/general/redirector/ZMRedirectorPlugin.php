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
 * Redirect handler plugin for (missing) products and categories.
 *
 * @package org.zenmagick.plugins.redirector
 * @author DerManoMann
 * @version $Id$
 */
class ZMRedirectorPlugin extends Plugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Redirector', 'Handle redirects for missing products and categories.');
        $this->setLoaderPolicy(ZMPlugin::LP_ALL);
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
        parent::init();
        // merge
        ZMUrlManager::instance()->setMapping(null, array('product_not_found' => array('view' => 'ForwardView#requestId=redirector')), false);
        ZMUrlManager::instance()->setMapping('redirector', array(
          'product_not_found' => array('template' => 'product_not_found')
        ), false);
    }

}

?>
