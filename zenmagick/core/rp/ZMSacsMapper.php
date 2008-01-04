<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
 *
 * Portions Copyright (c) 2003 The zen-cart developers
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


/**
 * Handle access control and security mappings.
 *
 * <p>Access control mappings define the level of authentication required for resources.
 * Resources in this context are controller or page requests.</p>
 *
 * <p>Controller/resources marked as secure will force redirects using SSL (if enabled), if
 * non secure HTTP is used to access them.</p>
 *
 * @author mano
 * @package org.zenmagick.rp
 * @version $Id$
 */
class ZMSacsMapper extends ZMObject {
    var $mapping_;
    var $levelMap_;


    /**
     * Default c'tor.
     */
    function ZMSacsMapper() {
        parent::__construct();

        $this->mapping_ = array();
        // which level allows what
        $this->levelMap_ = array(
            ZM_ACCOUNT_TYPE_ANONYMOUS => array(ZM_ACCOUNT_TYPE_ANONYMOUS, ZM_ACCOUNT_TYPE_GUEST, ZM_ACCOUNT_TYPE_REGISTERED),
            ZM_ACCOUNT_TYPE_GUEST => array(ZM_ACCOUNT_TYPE_GUEST, ZM_ACCOUNT_TYPE_REGISTERED),
            ZM_ACCOUNT_TYPE_REGISTERED => array(ZM_ACCOUNT_TYPE_REGISTERED)
        );
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMSacsMapper();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Set a mapping.
     *
     * @param string controller The controller.
     * @param string authentication The level of authentication required; default is <code>ZM_ACCOUNT_TYPE_REGISTERED</code>.
     * @param boolean secure Mark resource as secure; default is <code>true</code>.
     */
    function setMapping($controller, $authentication=ZM_ACCOUNT_TYPE_REGISTERED, $secure=true) {
        if (null == $controller) {
            zm_backtrace("invalid sacs mapping");
        }
        $this->mapping_[$controller] = array('level' => $authentication, 'secure' => $secure);
    }

    /**
     * Authorize the current request.
     *
     * @param string controller The controller; default is <code>null</code> to use the current page name.
     */
    function ensureAuthorization($controller=null) {
    global $zm_request;

        $requiredLevel = $this->getMappingValue($controller, 'level', null);
        if (null == $requiredLevel) {
            return;
        }

        $account = $zm_request->getAccount();
        $level = ZM_ACCOUNT_TYPE_ANONYMOUS;
        if (null != $account) {
            $level = $account->getType();
        }

        if (!zm_is_in_array($level, $this->levelMap_[$requiredLevel])) {
            // not required level of authentication
            $session = new ZMSession();
            if (!$session->isValid()) {
                // no valid session
                zm_redirect(zm_href(zm_setting('invalidSessionPage'), '', false));
            }
            $session->markRequestAsLoginFollowUp();
            zm_redirect(zm_secure_href('login', '', false));
        }
    }

    /**
     * Ensure the page is accessed using proper security.
     *
     * <p>If a page is requested using HTTP and the page is mapped as <em>secure</code>, a
     * redirect using SSL will be performed.</p>
     *
     * @param string controller The controller; default is <code>null</code> to use the current page name.
     */
    function ensureAccessMethod($controller=null) {
    global $zm_request;

        $secure = $this->getMappingValue($controller, 'level', false);
        if ($secure && !$zm_request->isSecure() && zm_setting('isEnableSSL') && zm_setting('isEnforceSSL')) {
            zm_redirect(zm_secure_href(null, null, false));
        }
    }

    /**
     * Get mapping value.
     *
     * @param string controller The controller; default is <code>null</code> to use the current page name.
     * @param string key The mapping key.
     * @param mixed default The mapping key.
     * @return mixed The value or the provided default value; default is <code>null</code>.
     */
    function getMappingValue($controller, $key, $default=null) {
    global $zm_request;

        if (null == $controller) {
            $controller = $zm_request->getPageName();
        }

        if (!isset($this->mapping_[$controller])) {
            return $default;
        }

        return $this->mapping_[$controller][$key];
    }

}

?>
