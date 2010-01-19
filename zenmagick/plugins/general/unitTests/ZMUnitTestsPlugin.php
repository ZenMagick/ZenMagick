<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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


define('UNIT_TESTS_GROUP_DEFAULT', '@default');
define('UNIT_TESTS_GROUP_OTHER', '@other');


/**
 * Unit testing.
 *
 * @package org.zenmagick.plugins.unitTests
 * @author DerManoMann
 * @version $Id$
 */
class ZMUnitTestsPlugin extends Plugin {
    private $tests_;
    private $customDone_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Unit Testing', 'Run unit tests using SimpleTest.');
        $this->setLoaderPolicy(ZMPlugin::LP_FOLDER);
        $this->tests_ = array();
        $this->customDone_ = false;
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
        ZMUrlManager::instance()->setMapping('tests', array(
            'template' => 'tests', 
            'view' => 'PluginView#plugin=unitTests', 
            'controller' => 'UnitTestsController'
       ));
    }

    /**
     * Add test.
     *
     * @param string clazz The test class name.
     * @param string group Optional group name; default is <code>UNIT_TESTS_GROUP_OTHER</code>.
     */
    public function addTest($clazz, $group=UNIT_TESTS_GROUP_OTHER) {
        if (!array_key_exists($group, $this->tests_)) {
            $this->tests_[$group] = array();
        }
        $this->tests_[$group][] = $clazz;
    }

    /**
     * Get other tests.
     *
     * @return array List of other tests.
     */
    public function getTests() {
        if (!$this->customDone_) {
            foreach (explode(',', ZMSettings::get('plugins.unitTests.tests.custom')) as $custom) {
                $this->addTest($custom);
            }
            $this->customDone_ = true;
        }
        return $this->tests_;
    }

}

?>
