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
 * Optional token service.
 *
 * @package org.zenmagick.plugins.zm_token
 * @author DerManoMann
 * @version $Id$
 */
class zm_token extends ZMPlugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Token', 'Optional secure token service', '${plugin.version}');
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
    function install() {
        parent::install();
        ZMDbUtils::executePatch(file($this->getPluginDir()."sql/install.sql"), $this->messages_);
    }

    /**
     * {@inheritDoc}
     */
    function remove($keepSettings=false) {
        parent::remove($keepSettings);
        ZMDbUtils::executePatch(file($this->getPluginDir()."sql/uninstall.sql"), $this->messages_);
    }

    /**
     * {@inheritDoc}
     */
    public function init() {
        parent::init();
        define('ZM_TABLE_TOKEN', ZM_DB_PREFIX . 'zm_token');
        ZMDbTableMapper::instance()->setMappingForTable('zm_token', 
            array(
                'hashId' => 'column=hash_id;type=integer;key=true;auto=true',
                'hash' => 'column=hash;type=blob',
                'resource' => 'column=resource;type=string',
                'issued' => 'column=issued;type=datetime',
                'expires' => 'column=expires;type=datetime',
              )
        );

        // register tests
        if (null != ($tests = ZMPlugins::instance()->getPluginForId('zm_tests'))) {
            // add class path only now to avoid errors due to missing UnitTestCase
            ZMLoader::instance()->addPath($this->getPluginDir().'tests/');
            $tests->addTest('TestZMTokens');
        }

        // load service
        ZMLoader::resolve('ZMTokens');
    }

}

?>
