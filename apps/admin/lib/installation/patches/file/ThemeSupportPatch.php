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
?>
<?php
namespace zenmagick\apps\store\admin\installation\patches\file;

use zenmagick\base\Runtime;
use zenmagick\apps\store\admin\installation\patches\FilePatch;


define('_ZM_ZEN_INDEX_PHP', ZC_INSTALL_PATH."index.php");

/**
 * Patch to enable ZenMagick themes.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ThemeSupportPatch extends FilePatch {

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct('themeSupport');
    }


    /**
     * Checks if this patch can still be applied.
     *
     * @param array lines The file contents of <code>index.php</code>.
     * @return boolean <code>true</code> if this patch can still be applied.
     */
    function isOpen($lines=null) {
        if (null == $lines) {
            $lines = $this->getFileLines(_ZM_ZEN_INDEX_PHP);
        }

        // look for ZenMagick code...
        $storeInclude = false;
        foreach ($lines as $line) {
            if (false !== strpos($line, "store.php")) {
                $storeInclude = true;
            }
        }

        return !($storeInclude);
    }

    /**
     * Checks if this patch is ready to be applied.
     *
     * @return boolean <code>true</code> if this patch is ready and all preconditions are met.
     */
    function isReady() {
        return is_writeable(_ZM_ZEN_INDEX_PHP);
    }

    /**
     * Get the precondition message.
     *
     * <p>This will return an empty string when <code>isReady()</code> returns <code>true</code>.</p>
     *
     * @return string The preconditions message or an empty string.
     */
    function getPreconditionsMessage() {
        return $this->isReady() ? "" : "Need permission to write " . _ZM_ZEN_INDEX_PHP;
    }

    /**
     * Execute this patch.
     *
     * @param boolean force If set to <code>true</code> it will force patching even if
     *  disabled as per settings.
     * @return boolean <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function patch($force=false) {
        $lines = $this->getFileLines(_ZM_ZEN_INDEX_PHP);
        if (!$this->isOpen($lines)) {
            return true;
        }

        if (is_writeable(_ZM_ZEN_INDEX_PHP)) {
            $patchedLines = array();
            foreach ($lines as $line) {
                // need to insert before the zen-cart html_header...
                if (false !== strpos($line, "require") && false !== strpos($line, "html_header.php")) {
                    array_push($patchedLines, "  include('".Runtime::getInstallationPath()."/store.php'); /* added by ZenMagick installation patcher */");
                }

                array_push($patchedLines, $line);
            }

            return $this->putFileLines(_ZM_ZEN_INDEX_PHP, $patchedLines);
        } else {
            Runtime::getLogging()->error("** ZenMagick: no permission to patch theme support into index.php");
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
        $lines = $this->getFileLines(_ZM_ZEN_INDEX_PHP);
        if ($this->isOpen($lines)) {
            return true;
        }

        if (is_writeable(_ZM_ZEN_INDEX_PHP)) {
            $unpatchedLines = array();
            foreach ($lines as $line) {
                if (false !== strpos($line, "include") && false !== strpos($line, "store.php")) {
                    continue;
                }
                array_push($unpatchedLines, $line);
            }

            return $this->putFileLines(_ZM_ZEN_INDEX_PHP, $unpatchedLines);
        } else {
            Runtime::getLogging()->error("** ZenMagick: no permission to patch index.php for uninstall");
            return false;
        }

        return true;
    }

}
