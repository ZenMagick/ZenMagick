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
?>
<?php
namespace zenmagick\apps\store\admin\installation;

use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;

/**
 * Single installation patch.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class InstallationPatch extends ZMObject {

    public $messages_;
    protected $id_;
    protected $label_;
    protected $zcAdminPath;

    /**
     * Create new patch.
     *
     * @param string id Id of the patch.
     */
    public function __construct($id) {
        parent::__construct();
        $this->id_ = $id;
        $this->label_ = $id. ' Patch';
        $this->messages_ = array();
    }


    /**
     * Get the patch id.
     *
     * @return string The id of the patch.
     */
    function getId() { return $this->id_; }

    /**
     * Get the patch label.
     *
     * @return string The label of the patch.
     */
    function getLabel() { return $this->label_; }

    /**
     * Returns a list of other patches it depends on.
     *
     * @return array List of patch names.
     */
    function dependsOn() { return array(); }


    /**
     * Checks if this patch can still be applied.
     *
     * @return boolean <code>true</code> if this patch can still be applied.
     */
    function isOpen() {
        return false;
    }

    /**
     * Checks if this patch is ready to be applied.
     *
     * @return boolean <code>true</code> if this patch is ready and all preconditions are met.
     */
    function isReady() {
        return true;
    }

    /**
     * Get the patch group id.
     *
     * @return string The patch group id.
     */
    function getGroupId() {
        return '';
    }

    /**
     * Get the precondition message.
     *
     * <p>This will return an empty string when <code>isReady()</code> returns <code>true</code>.</p>
     *
     * @return string The preconditions message or an empty string.
     */
    function getPreconditionsMessage() {
        return "";
    }

    /**
     * Get optional installation messages.
     *
     * @return array List of <code>Message</code> instances.
     */
    function getMessages() {
        return $this->messages_;
    }

    /**
     * Execute this patch.
     *
     * @param boolean force If set to <code>true</code> it will force patching even if
     *  disabled as per settings.
     * @return boolean <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function patch($force=false) {
        return true;
    }

    /**
     * Check if this patch supports undo.
     *
     * @return boolean <code>true</code> if undo is supported, <code>false</code> if not.
     */
    function canUndo() {
        return true;
    }

    /**
     * Revert the patch.
     *
     * @return boolean <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function undo() {
        return true;
    }

    protected function getZcAdminPath() {
        if (null != $this->zcAdminPath) { return $this->zcAdminPath; }
        $this->zcAdminPath = $this->guessZcAdminPath();
        return $this->zcAdminPath;
    }

    /**
     * Get full path to ZenCart admin
     *
     * return string full path to ZenCart Admin
     */
    public function guessZcAdminPath() {

        $folder = null;
        $basePath = dirname(Runtime::getInstallationPath());
        if (false !== ($handle = opendir($basePath))) {
            while (false !== ($file = readdir($handle))) {
                if (is_dir($basePath.'/'.$file) && !in_array($file, array('.', '..'))) {
                    if (file_exists(realpath($basePath.'/'.$file.'/specials.php')) && file_exists(realpath($basePath.'/'.$file.'/featured.php'))) {
                        $folder = $file;
                        break;
                    }
                }
            }
            closedir($handle);
        }
        return $basePath.'/'.$folder;
    }
}
