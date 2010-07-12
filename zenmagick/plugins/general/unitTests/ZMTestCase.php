<?php
/*
 * ZenMagick - Smart e-commerce
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
 * TestCase base class.
 *
 * @package org.zenmagick.plugins.unitTests
 * @author DerManoMann
 */
class ZMTestCase extends UnitTestCase {
    private $defaultDb_;


    /**
     * Get the current request.
     *
     * @return ZMRequest The current request.
     */
    public function getRequest() {
        return ZMRequest::instance();
    }

    /**
     * {@inheritDoc}
     */
    public function setUp() {
        // use test connection by temp. re-configuring the default connection
        $this->defaultDb_ = ZMSettings::get('zenmagick.core.database.connections.default');
        if (ZMSettings::exists('plugins.unitTests.database.test')) {
            $testConnection = ZMLangUtils::toArray(ZMSettings::get('plugins.unitTests.database.test'));
            $merged = array_merge(ZMLangUtils::toArray($this->defaultDb_), $testConnection);
            ZMSettings::set('zenmagick.core.database.connections.default', $merged);
        }

        $session = $this->getRequest()->getSession();
        if (!$session->isAnonymous()) {
            // logged in
            $session->clear();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function tearDown() {
        // restore
        ZMSettings::set('zenmagick.core.database.connections.default', $this->defaultDb_);
    }

    /**
     * {@inheritDoc}
     */
    public function assertEqual($first, $second, $message = '%s') {
        if (is_array($second)) {
            return $this->assert(ZMLoader::make('ArrayEqualExpectation', $first), $second, $message);
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
        return ZMPlugins::instance()->getPluginForId('unitTests');
    }

}
