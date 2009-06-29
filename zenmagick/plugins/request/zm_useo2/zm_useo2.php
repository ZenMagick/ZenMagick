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
 * Plugin for Ultimate SEO 2.x support.
 *
 * @package org.zenmagick.plugins.zm_useo2
 * @author mano
 * @version $Id$
 */
class zm_useo2 extends Plugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Ultimate SEO2', 'Ultimate SEO 2.x for ZenMagick', '${plugin.version}');
        $this->setLoaderPolicy(ZMPlugin::LP_ALL);
        $this->setScope(Plugin::SCOPE_STORE);
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
        // this will remove all '%SEO%' configuration settings, so do this first,
        // before creating SEO plugin settings in parent::install() ...
        $patch = new ZMUltimateSeoSupportPatch();
        if (null != $patch && $patch->isOpen()) {
            $status = $patch->patch(true);
            ZMMessages::instance()->addAll($patch->getMessages());
        }

        parent::install();
    }

    /**
     * Remove this plugin.
     *
     * @param boolean keepSettings If set to <code>true</code>, the settings will not be removed; default is <code>true</code>.
     */
    function remove($keepSettings=true) {
        parent::remove($keepSettings);

        $patch = new ZMUltimateSeoSupportPatch();
        if (!$patch->isOpen() && $patch->canUndo()) {
            $status = $patch->undo();
            ZMMessages::instance()->addAll($patch->getMessages());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getMessages() {
        $messages = parent::getMessages();
        $patch = new ZMUltimateSeoSupportPatch();
        if (null !== $patch && $patch->isOpen() && !$patch->isReady()) {
            $messages[] = ZMLoader::make('Message', $patch->getPreconditionsMessage(), ZMMessages::T_WARN);
        }
        return $messages;
    }

}

?>
