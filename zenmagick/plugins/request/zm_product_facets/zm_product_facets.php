<?php
/*
 * ZenMagick - Extensions for zen-cart
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
 * Product facets.
 *
 * @package org.zenmagick.plugins.zm_product_facets
 * @author DerManoMann
 * @version $Id$
 */
class zm_product_facets extends Plugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Product Facets', 'Facets view on products', '${plugin.version}');
        $this->setLoaderPolicy(ZMPlugin::LP_FOLDER);
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Init this plugin.
     */
    public function init() {
        parent::init();

        // load default facets
        ZMFacets::instance()->addFacetBuilder('manufacturers', 'zm_build_manufacturer_facet');
        ZMFacets::instance()->addFacetBuilder('categories', 'zm_build_category_facet');
        ZMFacets::instance()->addFacetBuilder('prices', 'zm_build_pricerange_facet');

        // add view to play around with
        $parameter = array('plugin' => $this, 'subdir' => 'views');
        ZMUrlMapper::instance()->setMappingInfo('facets', array(
            'class' => 'PluginView',
            'parameter' => array('plugin' => 'zm_product_facets', 'subdir' => 'views')
        ));

        // if zm_cron available, load cron job
        if (null != ZMPlugins::instance()->getPluginForId('zm_cron')) {
            // add class path only now to avoid errors due to missing ZMCronJob
            ZMLoader::instance()->addPath($this->getPluginDirectory().'cron/');
        }
    }

}

?>
