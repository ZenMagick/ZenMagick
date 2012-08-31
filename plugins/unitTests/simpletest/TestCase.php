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
namespace ZenMagick\plugins\unitTests\simpletest;

use UnitTestCase;
use ZenMagick\base\Runtime;
use ZenMagick\base\Toolbox;

/**
 * TestCase base class.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestCase extends UnitTestCase {
    private $defaultDb_;
    public $container;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->container = Runtime::getContainer();
    }

    /**
     * Get the current request.
     *
     * @return ZenMagick\http\Request The current request.
     */
    public function getRequest() {
        return Runtime::getContainer()->get('request');
    }

    /**
     * {@inheritDoc}
     */
    public function setUp() {
        // use test connection by temp. re-configuring the default connection
        $this->defaultDb_ = Runtime::getSettings()->get('doctrine.dbal.connections.default');
        if (Runtime::getSettings()->exists('plugins.unitTests.database.test')) {
            $testConnection = Toolbox::toArray(Runtime::getSettings()->get('plugins.unitTests.database.test'));
            $merged = array_merge(Toolbox::toArray($this->defaultDb_), $testConnection);
            Runtime::getSettings()->set('doctrine.dbal.connections.default', $merged);
        }

        $this->getRequest()->getSession()->setAccount(null);
        //TODO: allow to set/have custom test container
    }

    /**
     * {@inheritDoc}
     */
    public function tearDown() {
        // restore
        Runtime::getSettings()->set('doctrine.dbal.connections.default', $this->defaultDb_);
        $this->getRequest()->getSession()->setAccount(null);
    }

    /**
     * {@inheritDoc}
     */
    public function assertEqual($first, $second, $message = '%s') {
        if (is_array($second)) {
            return $this->assert(new ArrayEqualExpectation($first), $second, $message);
        }
        return parent::assertEqual($first, $second, $message);
    }

    /**
     * {@inheritDoc}
     */
    public function assert($expectation, $compare, $message='%s') {
        $result = parent::assert($expectation, $compare, $message);
        if (!$result) {
            $location = explode(' ', trim(str_replace(array('[', ']'), '', $this->getAssertionLine())));
            $details = array(
                'line' => array_pop($location),
                'message' => trim(str_replace('%s', '', $message)),
                'expectation' => $expectation,
                'compare' => $compare
            );
            $this->reporter->zmPaintFail($details);
        }
        return $result;
    }

    /**
     * Get the test plugin.
     *
     * @return ZMPlugin The plugin.
     */
    public function getTestPlugin() {
        return $this->container->get('pluginService')->getPluginForId('unitTests');
    }

    /**
     * Get base directory of tests.
     *
     * <p><strong>This is important to make tests work with core.php.</strong></p>
     *
     * @return string The full directory name of the tests folder.
     */
    public function getTestsBaseDirectory() {
        return $this->getTestPlugin()->getPluginDirectory().'/tests';
    }

}
