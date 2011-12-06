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


/**
 * Plugin to illustrate using custom controller methods.
 *
 * @package org.zenmagick.plugins.methodController
 * @author DerManoMann <mano@zenmagick.org>
 */
class ZMMethodControllerPlugin extends Plugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Method controller', 'Custom method controller example.', '${plugin.version}');
        $this->setContext('storefront');
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
        // plural, so we need a map of mappings!
        ZMUrlManager::instance()->setMappings(array('page' => array(
                'foo' => array('controller' => 'ZMMethodController#method=foo'),
                'bar' => array(
                    // empty = none, null = default layout
                    'layout' => '',
                    'controller' => 'ZMMethodController#method=bar'
                ),
                // proper view incl. layout as default, plus custom success view
                'xform' => array(
                    'controller' => 'ZMMethodController#method=xform',
                    'success' => array(
                        //'view' => 'RedirectView',
                        'template' => 'xform_success'
                    )
                )
            )
        ));
    }

}
