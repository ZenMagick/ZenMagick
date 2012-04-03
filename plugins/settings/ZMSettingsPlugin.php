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

use zenmagick\http\widgets\Widget;

/**
 * A UI plugin to manage (custom) settings.
 *
 * @package org.zenmagick.plugins.settings
 * @author DerManoMann <mano@zenmagick.org>
 */
class ZMSettingsPlugin extends Plugin {

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct('Settings', 'Manage (custom) settings');
        $this->setContext('admin');
    }


    /**
     * {@inheritDoc}
     */
    public function init() {
        parent::init();
        // add admin pages
        $menuKey = $this->addMenuGroup(_zm('Settings'));
        $this->addMenuItem(_zm('Manage Settings'), 'settingsAdmin', $menuKey);
        $this->addMenuItem(_zm('Show Settings'), 'settingsShow', $menuKey);

        // make all config values proper settings
        $settingsService = $this->container->get('settingsService');
        foreach ($this->getConfigValues() as $value) {
            if ($value instanceof Widget) {
                $settingsService->set($value->getName(), $value->getStringValue());
            }
        }

        // TODO: manually load lib for now
        require_once dirname(__FILE__).'/lib/settings_details.php';
    }

}
