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

/**
 * A UI plugin to manage (custom) settings.
 *
 * @package org.zenmagick.plugins.zm_settings
 * @author DerManoMann
 * @version $Id$
 */
class zm_settings extends Plugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Settings', 'Manage (custom) settings');
        $this->setLoaderPolicy(ZMPlugin::LP_FOLDER);
        $this->setScope(Plugin::SCOPE_ALL);
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

        $this->addMenuItem('zm_settings', zm_l10n_get('Manage Settings'), 'ZMSettingsAdminController');
        $this->addMenuItem('zm_settings', zm_l10n_get('Show Settings'), 'ZMShowSettingsAdminController');

        // make all config values proper settings
        foreach ($this->getConfigValues() as $value) {
            if ($value instanceof ZMWidget) {
                ZMSettings::set($value->getName(), $value->getStringValue());
            }
        }
    }

}

?>
