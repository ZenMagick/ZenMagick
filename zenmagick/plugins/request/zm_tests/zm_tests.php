<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
 * Unit testing.
 *
 * @package org.zenmagick.plugins.zm_tests
 * @author DerManoMann
 * @version $Id$
 */
class zm_tests extends ZMPlugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Unit Testing', 'Run unit tests using SimpleTest.');
        $this->setLoaderSupport('FOLDER');
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
    global $zm_tests;

        parent::init();
        $view = 'PluginView';
        $parameter = array('plugin' => $this, 'subdir' => 'views');

        ZMUrlMapper::instance()->setMapping(null, 'tests', 'tests', 'PluginView', array('plugin' => $this, 'subdir' => 'views'));
    }

}

?>
