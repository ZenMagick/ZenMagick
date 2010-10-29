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
 * Example plugin admin controller.
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.examplePlugin
 */
class ZMExamplePluginAdminController extends ZMController {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
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
    public function processPost($request) {
        $plugin = $this->getPlugin();

        foreach ($plugin->getConfigValues() as $configValue) {
            if (null != ($value = $request->getParameter($configValue->getName()))) {
                $plugin->set($configValue->getName(), $value);
            }
        }

        ZMMessages::instance()->success('Plugin settings updated!');

        return $this->getRedirectView($request);
    }

}
