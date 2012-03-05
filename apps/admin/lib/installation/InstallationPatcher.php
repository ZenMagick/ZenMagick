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
namespace zenmagick\apps\store\admin\installation;

use zenmagick\base\Beans;
use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;

/**
 * Provides support for all file patching of zen-cart files ZenMagick might need.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class InstallationPatcher extends ZMObject {
    private $patches_;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        if (!defined('ZENCART_ADMIN_FOLDER') && Runtime::getSettings()->get('apps.store.zencart.admindir')) {
            define('ZENCART_ADMIN_FOLDER', Runtime::getSettings()->get('apps.store.zencart.admindir'));
        }
        $this->_loadPatches();
    }


    /**
     * Load all patches.
     */
    public function _loadPatches() {
        $path = Runtime::getInstallationPath().'/apps/admin/lib/installation/patches';
        $ext = '.php';
        $this->patches_ = array();
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $filename => $fileInfo) {
            if ($fileInfo->isFile() && $ext == substr($fileInfo->getFilename(), -strlen($ext))) {
                $filename = $fileInfo->getPathname();
                $parent = basename(dirname($filename));
                if (in_array($parent, array('file', 'sql'))) {
                    $class = sprintf('zenmagick\apps\store\admin\installation\patches\%s\%s', $parent, substr($fileInfo->getFilename(), 0, strlen($fileInfo->getFilename())-strlen($ext)));
                    $patch = Beans::getBean($class);
                    $this->patches_[$patch->getId()] = $patch;
                }
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
     * @return InstallationPatch The corresponding installation patch or <code>null</code>.
     */
    function getPatchForId($id) {
        return array_key_exists($id, $this->patches_) ? $this->patches_[$id] : null;
    }

    /**
     * Run a set of patches
     *
     * @param mixed a single patch or an array of patches
     */
    function installPatches($patches) {
        $patches = (array)$patches;
        foreach ($patches as $patchId) {
            $patch = $this->getPatchForId($patchId);
            if (null == $patch) continue;
            $status = $patch->patch();
        }
    }

    /**
     * Get all patches.
     *
     * @param string groupId Optional group id.
     * @return array A list of <code>InstallationPatch</code> instances.
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
