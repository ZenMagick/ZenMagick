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
 * Provides support for all file patching of zen-cart files ZenMagick might need.
 *
 * @author mano
 * @package org.zenmagick.admin.installation
 * @version $Id$
 */
class ZMInstallationPatcher extends ZMObject {
    var $patches_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->_loadPatches();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Load all patches.
     */
    function _loadPatches() {
        foreach (ZMLoader::instance()->getClassPath() as $clazz => $file) {
            if (false !== strpos($file, "installation") && false != strpos($file, "patches") && 'patches' != basename(dirname($file))) {
                $patch = ZMLoader::make($clazz);
                $this->patches_[$patch->getId()] = $patch;
            }
        }
    }

    /**
     * Returns <code>true</code> if any patches left to run.
     *
     * @param string groupId Optional group id.
     * @return boolean <code>true</code> if there are any patches left that could be run.
     */
    function isPatchesOpen($groupId=null) {
        foreach ($this->patches_ as $id => $patch) {
            if (null != $groupId && $patch->getGroupId() != $groupId) {
                continue;
            }
            if ($patch->isOpen()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Execute all open patches.
     *
     * @param boolean force If set to <code>true</code> it will force patching even if
     *  it is disabled as per settings.
     * @return boolean <code>true</code> if <strong>all</strong> patching was successful, <code>false</code> if not.
     */
    function patch($force=false) {
        $result = true;
        foreach ($this->patches_ as $id => $patch) {
            $result |= $patch->patch($force);
        }

        return $result;
    }

    /**
     * Get the patch for the given id.
     *
     * @param string id The patch id.
     * @return ZMInstallationPatch The corresponding installation patch or <code>null</code>.
     */
    function getPatchForId($id) {
        return array_key_exists($id, $this->patches_) ? $this->patches_[$id] : null;
    }

    /**
     * Get all patches.
     *
     * @param string groupId Optional group id.
     * @return array A list of <code>ZMInstallationPatch</code> instances.
     */
    function getPatches($groupId=null) {
        $patches = array();
        foreach ($this->patches_ as $id => $patch) {
            if (null != $groupId && $patch->getGroupId() != $groupId) {
                continue;
            }
            $patches[$id] = $patch;
        }

        return $patches;
    }

}

?>
