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
 * A UI plugin to manage (custom) settings.
 *
 * @package org.zenmagick.plugins.zm_settings
 * @author DerManoMann
 * @version $Id$
 */
class zm_settings extends ZMPlugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Settings', 'Manage (custom) settings');
        $this->setLoaderPolicy(ZMPlugin::LP_FOLDER);
        $this->setScope(ZMPlugin::SCOPE_ALL);
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

        $this->addConfigValue('Text Widget', 'text', 'foo', 'Sample text widget',
            'widget#TextFormWidget;id=text&name=text;default=foo', 'size=30&maxlength=32');
        $this->addConfigValue('Text Widget', 'some.other', '', 'Sample other text widget',
            'widget#TextFormWidget;id=some.other&name=some.other;default=', 'size=8&maxlength=5');
        $this->addConfigValue('Boolean Widget', 'bool', 'false', 'Sample boolean widget',
            'widget#BooleanFormWidget;id=bool&name=bool;default=false', 'label=Tickle me');
        $this->addConfigValue('Other Boolean Widget', 'other.bool', 'true', 'Other sample boolean widget',
            'widget#BooleanFormWidget;id=other.bool&name=other.bool;default=true', 'true=Yup&false=Nix da&style=radio');
    }

    /**
     * Init this plugin.
     */
    function init() {
        parent::init();

        $this->addMenuItem('zm_settings', zm_l10n_get('Settings'), 'ZMSettingsAdminController');

        // make all config values proper settings
        foreach ($this->getConfigValues(false) as $value) {
            if ($value instanceof ZMWidget) {
                //echo $value->getName() . ' = ' . $value->getValue()."<BR>";
                ZMSettings::set($value->getName(), $value->getValue());
            }
        }
    }

}

?>
