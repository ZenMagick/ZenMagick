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
namespace zenmagick\apps\store\admin\installation\patches\file;

use zenmagick\base\Runtime;
use zenmagick\apps\store\admin\installation\patches\FilePatch;


define('_ZM_ZEN_APP_BOTTOM_PHP', ZC_INSTALL_PATH."/includes/application_bottom.php");

/**
 * Patch to enable ZenMagick without themes.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class NoThemeSupportPatch extends FilePatch {

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct('noThemeSupport');
    }


    /**
     * Checks if this patch can still be applied.
     *
     * @param array lines The file contents of <code>index.php</code>.
     * @return boolean <code>true</code> if this patch can still be applied.
     */
    function isOpen($lines=null) {
        if (null == $lines) {
            $lines = $this->getFileLines(_ZM_ZEN_APP_BOTTOM_PHP);
        }

        // look for ZenMagick code...
        $storeInclude = false;
        foreach ($lines as $line) {
            if (false !== strpos($line, 'getEventDispatcher')) {
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
        return is_writeable(_ZM_ZEN_APP_BOTTOM_PHP);
    }

    /**
     * Get the precondition message.
     *
     * <p>This will return an empty string when <code>isReady()</code> returns <code>true</code>.</p>
     *
     * @return string The preconditions message or an empty string.
     */
    function getPreconditionsMessage() {
        return $this->isReady() ? "" : "Need permission to write " . _ZM_ZEN_APP_BOTTOM_PHP;
    }

    /**
     * Execute this patch.
     *
     * @param boolean force If set to <code>true</code> it will force patching even if
     *  disabled as per settings.
     * @return boolean <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function patch($force=false) {
        $lines = $this->getFileLines(_ZM_ZEN_APP_BOTTOM_PHP);
        if (!$this->isOpen($lines)) {
            return true;
        }

        $PATCHLINE = "if (!zenmagick\\base\\Runtime::getSettings()->get('isEnableZMThemes', true)) { \$request = zenmagick\\base\\Runtime::getContainer()->get('request'); \$event = new zenmagick\\base\\events\\Event(null, array('request' => \$request, 'content' => ob_get_clean(), 'view' => zenmagick\\base\\Runtime::getContainer()->get('defaultView'))); \$event->get('view')->setContainer(zenmagick\\base\\Runtime::getContainer()); zenmagick\\base\\Runtime::getEventDispatcher()->dispatch('finalise_content', \$event); echo \$event->get('content'); \$request->getSession()->clearMessages(); zenmagick\\base\\Runtime::getEventDispatcher()->dispatch('all_done', new zenmagick\\base\\events\\Event(null, array('request' => \$request))); } /* added by ZenMagick installation patcher */";

        if (is_writeable(_ZM_ZEN_APP_BOTTOM_PHP)) {
            $patchedLines = array();
            foreach ($lines as $line) {
                if (false !== strpos($line, "session_write_close")) {
                    array_push($patchedLines, $PATCHLINE);
                }
                $patchedLines[] = $line;
            }
            return $this->putFileLines(_ZM_ZEN_APP_BOTTOM_PHP, $patchedLines);
        } else {
            Runtime::getLogging()->error("** ZenMagick: no permission to patch no theme support into application_bottpm.php");
            return false;
        }
    }

    /**
     * Revert the patch.
     *
     * @return boolean <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function undo() {
        $lines = $this->getFileLines(_ZM_ZEN_APP_BOTTOM_PHP);
        if ($this->isOpen($lines)) {
            return true;
        }

        if (is_writeable(_ZM_ZEN_APP_BOTTOM_PHP)) {
            $unpatchedLines = array();
            foreach ($lines as $line) {
                if (false !== stripos($line, "finalise_content") && (false !== strpos($line, "getEventDispatcher") || false !== strpos($line, "ZMEvents"))) {
                    continue;
                }
                $unpatchedLines[] = $line;
            }

            return $this->putFileLines(_ZM_ZEN_APP_BOTTOM_PHP, $unpatchedLines);
        } else {
            Runtime::getLogging()->error("** ZenMagick: no permission to patch application_bottpm.php for uninstall");
            return false;
        }

        return true;
    }

}
