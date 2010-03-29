<?php
/*
 * ZenMagick - Extensions for zen-cart
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
 * Admin controller.
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.googleStoreLocator
 * @version $Id$
 */
class ZMGoogleStoreLocatorAdminController extends ZMPluginAdminController {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('google_store_locator_admin', zm_l10n_get('Google Store Locator Admin'), 'googleStoreLocator');
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Build view data.
     *
     * @return array View data map.
     */
    protected function getViewData() {
        return array(
            'adminKey' => $this->getPlugin()->get('adminKey'),
            'location' => $this->getPlugin()->get('location'),
            'zoom' => $this->getPlugin()->get('zoom')
        );
    }

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        return $this->findView(null, $this->getViewData());
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
