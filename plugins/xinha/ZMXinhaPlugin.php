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

use zenmagick\base\Runtime;
use zenmagick\base\Toolbox;

/**
 * Xinha plugin.
 *
 * @package org.zenmagick.plugins.xinha
 * @author DerManoMann <mano@zenmagick.org>
 */
class ZMXinhaPlugin extends Plugin {

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct('Xinha', 'Xinha WYSIWYG editor.');
    }


    /**
     * {@inheritDoc}
     */
    public function init() {
        parent::init();
        if (Toolbox::asBoolean($this->get('defaultEditor'))) {
            ZMSettings::set('apps.store.admin.defaultEditor', 'xinhaEditorWidget');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function install() {
        parent::install();
        $this->addConfigValue('Default Editor', 'defaultEditor', false, 'Make Xinha the default editor',
            'widget@ZMBooleanFormWidget#name=defaultEditor&default=false&label=Default Editor&style=checkbox');
    }

}
