<?php
/*
 * ZenMagick - Smart e-commerce
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

namespace zenmagick\apps\store\admin\installation\patches\file;

use zenmagick\base\Runtime;
use zenmagick\apps\store\admin\installation\patches\FilePatch;


/**
 * Patch to control ZenCart
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ApplicationTopPatch extends FilePatch {
    protected $applicationTop;

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct('applicationTop');
        $this->label_ = 'Allow Zenmagick to handle all ZenCart initialisation code';
        $this->applicationTop = ZC_INSTALL_PATH.'includes/application_top.php';
    }


    /**
     * Checks if this patch can still be applied.
     *
     * @param array lines The file contents of <code>includes/application_top.php</code>.
     * @return boolean <code>true</code> if this patch can still be applied.
     */
    function isOpen($lines=null) {
        if (null == $lines) {
            $lines = $this->getFileLines($this->applicationTop);
        }

        // look for ZenMagick code...
        $patched = !(false !== strpos($lines[0], 'ZenCartBundle'));
        return $patched;
    }

    /**
     * Checks if this patch is ready to be applied.
     *
     * @return boolean <code>true</code> if this patch is ready and all preconditions are met.
     */
    function isReady() {
        return is_writeable($this->applicationTop);
    }

    /**
     * Get the precondition message.
     *
     * <p>This will return an empty string when <code>isReady()</code> returns <code>true</code>.</p>
     *
     * @return string The preconditions message or an empty string.
     */
    function getPreconditionsMessage() {
        return $this->isReady() ? "" : "Need permission to write " . $this->applicationTop;
    }

    /**
     * Execute this patch.
     *
     * @param boolean force If set to <code>true</code> it will force patching even if
     *  disabled as per settings.
     * @return boolean <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function patch($force=false) {
        $lines = $this->getFileLines($this->applicationTop);
        if (!$this->isOpen($lines)) {
            return true;
        }

        if (is_writeable($this->applicationTop)) {
            $lines[0] = "<?php require '".basename(Runtime::getInstallationPath())."/shared/store/bundles/ZenCartBundle/bridge/includes/application_top.php'; return; /* added by ZenMagick installation patcher */";
            return $this->putFileLines($this->applicationTop, $lines);
        } else {
            Runtime::getLogging()->error("** ZenMagick: no permission to patch theme support into application_top.php");
            return false;
        }

        return true;
    }

    /**
     * Revert the patch.
     *
     * @return boolean <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function undo() {
        $lines = $this->getFileLines($this->applicationTop);
        if ($this->isOpen($lines)) {
            return true;
        }

        if (is_writeable($this->applicationTop)) {
            $lines[0] = '<?php';
            return $this->putFileLines($this->applicationTop, $lines);
        } else {
            Runtime::getLogging()->error("** ZenMagick: no permission to patch application_top.php for uninstall");
            return false;
        }

        return true;
    }

}
