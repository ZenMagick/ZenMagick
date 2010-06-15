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
 * A UI plugin to manage (custom) settings.
 *
 * @package org.zenmagick.plugins.settings
 * @author DerManoMann
 * @version $Id$
 */
class ZMSettingsPlugin extends Plugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Settings', 'Manage (custom) settings');
        $this->setLoaderPolicy(ZMPlugin::LP_FOLDER);
        $this->setContext(Plugin::CONTEXT_ADMIN);
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

        $this->addMenuItem('settings_manage', _zm('Manage Settings'), 'settings_admin_manage');
        $this->addMenuItem('settings_show', _zm('Show Settings'), 'settings_admin_show');

        // make all config values proper settings
        foreach ($this->getConfigValues() as $value) {
            if ($value instanceof ZMWidget) {
                ZMSettings::set($value->getName(), $value->getStringValue());
            }
        }
    }

}
