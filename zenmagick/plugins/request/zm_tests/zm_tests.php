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


define('ZM_TESTS_GROUP_DEFAULT', '@default');
define('ZM_TESTS_GROUP_OTHER', '@other');


/**
 * Unit testing.
 *
 * @package org.zenmagick.plugins.zm_tests
 * @author DerManoMann
 * @version $Id$
 */
class zm_tests extends Plugin {
    private $tests;

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Unit Testing', 'Run unit tests using SimpleTest.');
        $this->setLoaderPolicy(ZMPlugin::LP_FOLDER);
        $this->tests = array();
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
        ZMSettings::set('zenmagick.mvc.templates.ext', '.php');
        // should be 'views/tests'
        ZMUrlMapper::instance()->setMappingInfo('tests', array('view' => 'tests', 'viewDefinition' => 'PluginView#plugin=zm_tests'));
    }

    /**
     * Add test.
     *
     * @param string clazz The test class name.
     * @param string group Optional group name; default is <code>ZM_TESTS_GROUP_OTHER</code>.
     */
    public function addTest($clazz, $group=ZM_TESTS_GROUP_OTHER) {
        if (!array_key_exists($group, $this->tests)) {
            $this->tests[$group] = array();
        }
        $this->tests[$group][] = $clazz;
    }

    /**
     * Get other tests.
     *
     * @return array List of other tests.
     */
    public function getTests() {
        return $this->tests;
    }


}

?>
