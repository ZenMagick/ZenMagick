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
 * Xinha plugin.
 *
 * @package org.zenmagick.plugins.xinha
 * @author DerManoMann
 * @version $Id$
 */
class ZMXinhaPlugin extends Plugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Xinha', 'Xinha WYSIWYG editor.');
        $this->setLoaderPolicy(ZMPlugin::LP_FOLDER);
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
        ZMSettings::append('editorList', 'Xinha:XinhaFormWidget');
        if (ZMLangUtils::asBoolean($this->get('defaultEditor'))) {
            ZMSettings::set('defaultEditor', 'XinhaFormWidget');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function install() {
        parent::install();
        $this->addConfigValue('Default Editor', 'defaultEditor', false, 'Make Xinha the default editor',
            'widget@BooleanFormWidget#name=defaultEditor&default=false&label=Default Editor&style=checkbox');
    }

}
