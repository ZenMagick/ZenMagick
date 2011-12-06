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

use zenmagick\base\Runtime;

define('_ZM_ZEN_COUPON_ADMIN_PHP', ZC_INSTALL_PATH.ZC_ADMIN_FOLDER.'/coupon_admin.php');

/**
 * Patch to enable ZenMagick templates for coupon admin emails.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package zenmagick.store.admin.installation.patches.file
 */
class ZMCouponAdminMailPatch extends ZMFilePatch {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('couponAdminMail');
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Checks if this patch can still be applied.
     *
     * @param array lines The file contents of <code>index.php</code>.
     * @return boolean <code>true</code> if this patch can still be applied.
     */
    function isOpen($lines=null) {
        if (null == $lines) {
            $lines = $this->getFileLines(_ZM_ZEN_COUPON_ADMIN_PHP);
        }

        // look for ZenMagick code...
        $patched = false;
        foreach ($lines as $line) {
            if (false !== strpos($line, "added by ZenMagick")) {
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
        return is_writeable(_ZM_ZEN_COUPON_ADMIN_PHP);
    }

    /**
     * Get the precondition message.
     *
     * <p>This will return an empty string when <code>isReady()</code> returns <code>true</code>.</p>
     *
     * @return string The preconditions message or an empty string.
     */
    function getPreconditionsMessage() {
        return $this->isReady() ? "" : "Need permission to write " . _ZM_ZEN_COUPON_ADMIN_PHP;
    }

    /**
     * Execute this patch.
     *
     * @param boolean force If set to <code>true</code> it will force patching even if
     *  disabled as per settings.
     * @return boolean <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function patch($force=false) {
        $lines = $this->getFileLines(_ZM_ZEN_COUPON_ADMIN_PHP);
        if (!$this->isOpen($lines)) {
            return true;
        }

        if (is_writeable(_ZM_ZEN_COUPON_ADMIN_PHP)) {
            $patchedLines = array();
            foreach ($lines as $line) {
                array_push($patchedLines, $line);
                // need to insert after the match
                if (false !== strpos($line, "audience_select = get_audience_sql_query")) {
                    array_push($patchedLines, '    $audience_select["query_string"] = $db->bindVars("select customers_id, customers_firstname, customers_lastname, customers_email_address from " . TABLE_CUSTOMERS . " where customers_email_address = :emailAddress", ":emailAddress", zen_db_prepare_input($_POST["customers_email_address"]), "string"); // added by ZenMagick');
                }
                if (false !== strpos($line, "html_msg['EMAIL_FIRST_NAME'] =")) {
                    array_push($patchedLines, '    $html_msg["accountId"] = $mail->fields["customers_id"]; // added by ZenMagick');
                }
            }

            return $this->putFileLines(_ZM_ZEN_COUPON_ADMIN_PHP, $patchedLines);
        } else {
            Runtime::getLogging()->error("** ZenMagick: no permission to patch coupon admin mail fix into coupon_admin.php");
            return false;
        }
    }

    /**
     * Revert the patch.
     *
     * @return boolean <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function undo() {
        $lines = $this->getFileLines(_ZM_ZEN_COUPON_ADMIN_PHP);
        if ($this->isOpen($lines)) {
            return true;
        }

        if (is_writeable(_ZM_ZEN_COUPON_ADMIN_PHP)) {
            $unpatchedLines = array();
            foreach ($lines as $line) {
                if (false !== strpos($line, "added by ZenMagick")) {
                    continue;
                }
                array_push($unpatchedLines, $line);
            }

            return $this->putFileLines(_ZM_ZEN_COUPON_ADMIN_PHP, $unpatchedLines);
        } else {
            Runtime::getLogging()->error("** ZenMagick: no permission to patch coupon_admin.php for uninstall");
            return false;
        }

        return true;
    }

}
