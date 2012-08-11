<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace zenmagick\plugins\unitTests;

use Plugin;


define('UNIT_TESTS_GROUP_DEFAULT', '@default');
define('UNIT_TESTS_GROUP_OTHER', '@other');


/**
 * Unit testing.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class UnitTestsPlugin extends Plugin {
    private $tests_ = array();
    private $customDone_ = false;

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
            foreach ($this->container->get('containerTagService')->findTaggedServiceIds('plugins.unitTests.test') as $id => $args) {
                $group = UNIT_TESTS_GROUP_OTHER;
                foreach ($args as $elem) {
                    foreach ($elem as $key => $value) {
                        if ('group' == $key) {
                            $group = '@'.$value;
                            break;
                        }
                    }
                }
                $this->addTest('service#'.$id, $group);
            }
            $this->customDone_ = true;
        }
        return $this->tests_;
    }

}
