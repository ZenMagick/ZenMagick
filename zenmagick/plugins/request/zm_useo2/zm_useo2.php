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
 * Plugin for Ultimate SEO 2.x support.
 *
 * @package org.zenmagick.plugins.zm_useo2
 * @author mano
 * @version $Id$
 */
class zm_useo2 extends ZMPlugin {

    /**
     * Create new instance.
     */
    function zm_useo2() {
        parent::__construct('ZenMagick Ultimate SEO2', 'Ultimate SEO 2.x for ZenMagick', '${plugin.version}');
        $this->setLoaderSupport('ALL');
    }

    /**
     * Create new instance.
     */
    function __construct() {
        $this->zm_useo2();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Install this plugin.
     */
    function install() {
    global $zm_messages;

        // this will remove all '%SEO%' configuration settings, so do this first,
        // before creating SEO plugin settings in parent::install() ...
        $patch = $this->create('ZMUltimateSeoSupportPatch');
        if (null != $patch && $patch->isOpen()) {
            $status = $patch->patch(true);
            $zm_messages->addAll($patch->getMessages());
        }

        parent::install();
    }

    /**
     * Remove this plugin.
     *
     * @param boolean keepSettings If set to <code>true</code>, the settings will not be removed; default is <code>true</code>.
     */
    function remove($keepSettings=true) {
    global $zm_messages;

        parent::remove($keepSettings);

        $patch = $this->create('ZMUltimateSeoSupportPatch');
        if (!$patch->isOpen() && $patch->canUndo()) {
            $status = $patch->undo();
            $zm_messages->addAll($patch->getMessages());
        }
    }

}

?>
