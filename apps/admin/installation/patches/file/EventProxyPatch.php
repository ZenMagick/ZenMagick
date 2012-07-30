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
namespace zenmagick\apps\admin\installation\patches\file;

use zenmagick\base\Runtime;
use zenmagick\apps\admin\installation\patches\FilePatch;

/**
 * Patch to hook up ZenMagick as glboal zencart event listener.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class EventProxyPatch extends FilePatch {
    protected $baseClassFile;

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct('eventProxy');
        $this->label_ = 'Remove deprecated event proxy patch.';
        $this->baseClassFile = Runtime::getSettings()->get('apps.store.zencart.path').'/includes/classes/class.base.php';
    }


    /**
     * Checks if this patch can still be applied.
     *
     * @param array lines The file contents of <code>index.php</code>.
     * @return boolean <code>true</code> if this patch can still be applied.
     */
    function isOpen($lines=null) {
        if (null == $lines) {
            $lines = $this->getFileLines($this->baseClassFile);
        }

        // look for ZenMagick code...
        foreach ($lines as $line) {
            if (false !== strpos($line, '$zm_events') || false !== strpos($line, 'ZMEvents::instance()') || false !== strpos($line, 'getEventDispatcher()')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if this patch is ready to be applied.
     *
     * @return boolean <code>true</code> if this patch is ready and all preconditions are met.
     */
    function isReady() {
        return is_writeable($this->baseClassFile);
    }

    /**
     * Get the precondition message.
     *
     * <p>This will return an empty string when <code>isReady()</code> returns <code>true</code>.</p>
     *
     * @return string The preconditions message or an empty string.
     */
    function getPreconditionsMessage() {
        return $this->isReady() ? "" : "Need permission to write " . $this->baseClassFile;
    }

    /**
     * Execute this patch.
     *
     * @param boolean force If set to <code>true</code> it will force patching even if
     *  disabled as per settings.
     * @return boolean <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function patch($force=false) {
        $lines = $this->getFileLines($this->baseClassFile);
        if ($this->isOpen($lines)) {
            return true;
        }

        if (is_writeable($this->baseClassFile)) {
            $unpatchedLines = array();
            foreach ($lines as $line) {
                if (false !== strpos($line, '$zm_events') || false !== strpos($line, 'ZMEvents::instance()') || false !== strpos($line, 'getEventDispatcher()')) {
                    continue;
                }
                array_push($unpatchedLines, $line);
            }

            return $this->putFileLines($this->baseClassFile, $unpatchedLines);
        } else {
            Runtime::getLogging()->error("** ZenMagick: no permission to patch class.base.php for uninstall");
            return false;
        }

        return true;
    }

    function canUndo() {
        return false;
    }
}
