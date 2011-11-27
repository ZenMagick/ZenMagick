<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
 * Plugin adding master password functionality.
 *
 * @author mano
 * @package org.zenmagick.plugins.masterPassword
 */
class ZMMasterPasswordPlugin extends Plugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Master Password', 'Master password for all accounts.', '${plugin.version}');
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
    public function install() {
        parent::install();
        $this->addConfigValue('Master Password', 'masterPassword', '', 'The master password (will be encrypted in the database)',
            'widget@ZMPasswordFormWidget#name=masterPassword&size=12&maxlength=28&hidden=true');
    }

    /**
     * {@inheritDoc}
     */
    public function init() {
        parent::init();

        // add admin page
        $this->addMenuItem2(_zm('Master Password'), 'masterPasswordAdmin');
    }

    /**
     * {@inheritDoc}
     */
    public function hasOptions() {
        // only do separate dialog
        return false;
    }

}
