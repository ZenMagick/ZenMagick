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
namespace zenmagick\apps\admin\installation\patches\file;

use zenmagick\base\Runtime;
use zenmagick\apps\admin\installation\patches\FilePatch;


define('_ZM_ZEN_GENERAL_FILE', ZC_INSTALL_PATH . 'includes/functions/functions_general.php');
define('_ZM_ZEN_ADMIN_GENERAL_FILE', ZC_INSTALL_PATH . ZENCART_ADMIN_FOLDER . '/includes/functions/general.php');

/**
 * Patch to enable vetoable redirects in zencart.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class RedirectFixPatch extends FilePatch {

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct('redirectPatch');
    }


    /**
     * Checks if this patch can still be applied.
     *
     * @param array lines The file contents of <code>index.php</code>.
     * @return boolean <code>true</code> if this patch can still be applied.
     */
    function isOpen($lines=null) {
        $found = 0;
        foreach (array(_ZM_ZEN_GENERAL_FILE, _ZM_ZEN_ADMIN_GENERAL_FILE) as $file) {
            if (null == $lines) {
                $lines = $this->getFileLines($file);
            }

            // look for ZenMagick code...
            foreach ($lines as $line) {
                if (false !== strpos($line, "ZMRequest::instance") || false !== strpos($line, 'Runtime::getContainer()->get(\'request\')')) {
                    ++$found;
                    break;
                }
            }
        }

        return 2 != $found;
    }

    /**
     * Checks if this patch is ready to be applied.
     *
     * @return boolean <code>true</code> if this patch is ready and all preconditions are met.
     */
    function isReady() {
        return is_writeable(_ZM_ZEN_GENERAL_FILE) && is_writeable(_ZM_ZEN_ADMIN_GENERAL_FILE);
    }

    /**
     * Get the precondition message.
     *
     * <p>This will return an empty string when <code>isReady()</code> returns <code>true</code>.</p>
     *
     * @return string The preconditions message or an empty string.
     */
    function getPreconditionsMessage() {
        return $this->isReady() ? "" : "Need permission to write " . _ZM_ZEN_GENERAL_FILE. ' and ' . _ZM_ZEN_ADMIN_GENERAL_FILE;
    }

    /**
     * Execute this patch.
     *
     * @param boolean force If set to <code>true</code> it will force patching even if
     *  disabled as per settings.
     * @return boolean <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function patch($force=false) {
        if (!$this->isOpen($lines)) {
            return true;
        }

        foreach (array(_ZM_ZEN_GENERAL_FILE, _ZM_ZEN_ADMIN_GENERAL_FILE) as $file) {
            if (is_writeable($file)) {
                $patchedLines = array();
                $insertNow = false;
                $lines = $this->getFileLines($file);
                foreach ($lines as $line) {
                    if ($insertNow && false === strpos($line, "ZMRequest::instance") && false === strpos($line, 'Runtime::getContainer()->get(\'request\')')) {
                        if ($file == _ZM_ZEN_GENERAL_FILE) {
                            $patchLine = 'zenmagick\\base\\Runtime::getContainer()->get(\'request\')->redirect($url, $httpResponseCode); return; /* added by ZenMagick installation patcher */';
                        } else {
                            $patchLine = 'zenmagick\\base\\Runtime::getContainer()->get(\'request\')->redirect($url); return; /* added by ZenMagick installation patcher */';
                        }
                        array_push($patchedLines, $patchLine);
                    }
                    $insertNow = false;
                    array_push($patchedLines, $line);
                    if (false !== strpos($line, "function zen_redirect")) {
                        $insertNow = true;
                    }
                }
                $this->putFileLines($file, $patchedLines);
            } else {
                Runtime::getLogging()->error("** ZenMagick: no permission to patch redirect fix support into ".basename($file));
                return false;
            }
        }
        return true;
    }

    /**
     * Revert the patch.
     *
     * @return boolean <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function undo() {
        if ($this->isOpen($lines)) {
            return true;
        }

        foreach (array(_ZM_ZEN_GENERAL_FILE, _ZM_ZEN_ADMIN_GENERAL_FILE) as $file) {
            if (is_writeable($file)) {
                $unpatchedLines = array();
                $lines = $this->getFileLines($file);
                foreach ($lines as $line) {
                    if (false !== strpos($line, "ZMRequest::instance") || false !== strpos($line, 'Runtime::getContainer()->get(\'request\')')) {
                        continue;
                    }
                    array_push($unpatchedLines, $line);
                }
                $this->putFileLines($file, $unpatchedLines);
            } else {
                Runtime::getLogging()->error("** ZenMagick: no permission to patch ".basename($file)." for uninstall");
                return false;
            }
        }

        return true;
    }

}
