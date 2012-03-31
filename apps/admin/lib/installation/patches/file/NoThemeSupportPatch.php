<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
 * Patch to enable ZenMagick without themes.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class NoThemeSupportPatch extends FilePatch {
    protected $applicationBottomFile;

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct('noThemeSupport');
        $this->label_ = 'Patch zen-cart to use ZenMagick <strong>without</strong> ZenMagick themes';
        $this->applicationBottomFile = Runtime::getSettings()->get('apps.store.zencart.path').'/includes/application_bottom.php';
    }


    /**
     * Checks if this patch can still be applied.
     *
     * @param array lines The file contents of <code>index.php</code>.
     * @return boolean <code>true</code> if this patch can still be applied.
     */
    function isOpen($lines=null) {
        if (null == $lines) {
            $lines = $this->getFileLines($this->applicationBottomFile);
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
        return is_writeable($this->applicationBottomFile);
    }

    /**
     * Get the precondition message.
     *
     * <p>This will return an empty string when <code>isReady()</code> returns <code>true</code>.</p>
     *
     * @return string The preconditions message or an empty string.
     */
    function getPreconditionsMessage() {
        return $this->isReady() ? "" : "Need permission to write " . $this->applicationBottomFile;
    }

    /**
     * Execute this patch.
     *
     * @param boolean force If set to <code>true</code> it will force patching even if
     *  disabled as per settings.
     * @return boolean <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function patch($force=false) {
        $lines = $this->getFileLines($this->applicationBottomFile);
        if (!$this->isOpen($lines)) {
            return true;
        }

        $PATCHLINE = "if (!zenmagick\\base\\Runtime::getSettings()->get('isEnableZMThemes', true)) { \$request = zenmagick\\base\\Runtime::getContainer()->get('request'); \$event = new zenmagick\\base\\events\\Event(null, array('request' => \$request, 'content' => ob_get_clean(), 'view' => zenmagick\\base\\Runtime::getContainer()->get('defaultView'))); \$event->get('view')->setContainer(zenmagick\\base\\Runtime::getContainer()); zenmagick\\base\\Runtime::getEventDispatcher()->dispatch('finalise_content', \$event); echo \$event->get('content'); \$request->getSession()->clearMessages(); zenmagick\\base\\Runtime::getEventDispatcher()->dispatch('all_done', new zenmagick\\base\\events\\Event(null, array('request' => \$request))); } /* added by ZenMagick installation patcher */";

        if (is_writeable($this->applicationBottomFile)) {
            $patchedLines = array();
            foreach ($lines as $line) {
                if (false !== strpos($line, "session_write_close")) {
                    array_push($patchedLines, $PATCHLINE);
                }
                $patchedLines[] = $line;
            }
            return $this->putFileLines($this->applicationBottomFile, $patchedLines);
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
        $lines = $this->getFileLines($this->applicationBottomFile);
        if ($this->isOpen($lines)) {
            return true;
        }

        if (is_writeable($this->applicationBottomFile)) {
            $unpatchedLines = array();
            foreach ($lines as $line) {
                if (false !== stripos($line, "finalise_content") && (false !== strpos($line, "getEventDispatcher") || false !== strpos($line, "ZMEvents"))) {
                    continue;
                }
                $unpatchedLines[] = $line;
            }

            return $this->putFileLines($this->applicationBottomFile, $unpatchedLines);
        } else {
            Runtime::getLogging()->error("** ZenMagick: no permission to patch application_bottpm.php for uninstall");
            return false;
        }

        return true;
    }

}
