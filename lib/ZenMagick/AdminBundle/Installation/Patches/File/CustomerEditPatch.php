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
namespace ZenMagick\AdminBundle\Installation\Patches\File;

use ZenMagick\Base\Runtime;
use ZenMagick\AdminBundle\Installation\Patches\FilePatch;

/**
 * Patch to enable editing customers if the same email exists as guest checkout.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class CustomerEditPatch extends FilePatch {
    protected $customersAdminFile;

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct('customerEdit');
        $this->label_ = 'Patch zen-cart to allow editing customers where email also exists as guest account';
        $this->customersAdminFile = $this->container->get('settingsService')->get('zencart.admin_dir').'/customers.php';
    }


    /**
     * Checks if this patch can still be applied.
     *
     * @param array lines The file contents of <code>index.php</code>.
     * @return boolean <code>true</code> if this patch can still be applied.
     */
    function isOpen($lines=null) {
        if (null == $lines) {
            $lines = $this->getFileLines($this->customersAdminFile);
        }

        // look for ZenMagick code...
        $patched = false;
        foreach ($lines as $line) {
            if (false !== strpos($line, "and NOT customers_password = ''")) {
                $patched = true;
                break;
            }
        }

        return !($patched);
    }

    /**
     * Checks if this patch is ready to be applied.
     *
     * @return boolean <code>true</code> if this patch is ready and all preconditions are met.
     */
    function isReady() {
        return is_writeable($this->customersAdminFile);
    }

    /**
     * Get the precondition message.
     *
     * <p>This will return an empty string when <code>isReady()</code> returns <code>true</code>.</p>
     *
     * @return string The preconditions message or an empty string.
     */
    function getPreconditionsMessage() {
        return $this->isReady() ? "" : "Need permission to write " . $this->customersAdminFile;
    }

    /**
     * Execute this patch.
     *
     * @param boolean force If set to <code>true</code> it will force patching even if
     *  disabled as per settings.
     * @return boolean <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function patch($force=false) {
        $lines = $this->getFileLines($this->customersAdminFile);
        if (!$this->isOpen($lines)) {
            return true;
        }

        if (is_writeable($this->customersAdminFile)) {
            $patchedLines = array();
            foreach ($lines as $line) {
                array_push($patchedLines, $line);
                // need to insert after the match
                if (false !== strpos($line, "where customers_email_address = '")) {
                    array_push($patchedLines, "  and NOT customers_password = ''");
                }
            }

            return $this->putFileLines($this->customersAdminFile, $patchedLines);
        } else {
            Runtime::getLogging()->err("** ZenMagick: no permission to patch edit fix into customers.php");
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
        $lines = $this->getFileLines($this->customersAdminFile);
        if ($this->isOpen($lines)) {
            return true;
        }

        if (is_writeable($this->customersAdminFile)) {
            $unpatchedLines = array();
            foreach ($lines as $line) {
                if (false !== strpos($line, "  and NOT customers_password = ''")) {
                    continue;
                }
                array_push($unpatchedLines, $line);
            }

            return $this->putFileLines($this->customersAdminFile, $unpatchedLines);
        } else {
            Runtime::getLogging()->err("** ZenMagick: no permission to patch customers.php for uninstall");
            return false;
        }

        return true;
    }

}
