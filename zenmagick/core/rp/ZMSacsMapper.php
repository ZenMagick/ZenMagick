<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
 * @author DerManoMann
 * @package org.zenmagick.rp
 * @version $Id$
 */
class ZMSacsMapper extends ZMObject {
    private $mapping_;
    private $levelMap_;


    /**
     * Create new instance.
     */
    function __construct() {
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
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Get instance.
     */
    public static function instance() {
        return ZMObject::singleton('SacsMapper');
    }


    /**
     * Set a mapping.
     *
     * @param string page The page [ie. the request name].
     * @param string authentication The level of authentication required; default is <code>ZM_ACCOUNT_TYPE_REGISTERED</code>.
     * @param boolean secure Mark resource as secure; default is <code>true</code>.
     */
    public function setMapping($page, $authentication=ZM_ACCOUNT_TYPE_REGISTERED, $secure=true) {
        if (null == $page) {
            throw ZMLoader::make('ZMException', "invalid sacs mapping (null controller)");
        }
        $this->mapping_[$page] = array('level' => $authentication, 'secure' => $secure);
    }

    /**
     * Authorize the current request.
     *
     * @param string page The page; default is <code>null</code> to use the current page name.
     */
    public function ensureAuthorization($page=null) {
        $requiredLevel = $this->getMappingValue($page, 'level', ZMSettings::get('defaultAccessLevel'));
        if (null == $requiredLevel) {
            return;
        }

        $account = ZMRequest::getAccount();
        $level = ZM_ACCOUNT_TYPE_ANONYMOUS;
        if (null != $account) {
            $level = $account->getType();
        }

        if (!ZMTools::inArray($level, $this->levelMap_[$requiredLevel])) {
            // not required level of authentication
            $session = ZMRequest::getSession();
            if (!$session->isValid()) {
                // no valid session
                ZMRequest::redirect(ZMToolbox::instance()->net->url(ZMSettings::get('invalidSessionPage'), '', false, false));
            }
            $session->markRequestAsLoginFollowUp();
            ZMRequest::redirect(ZMToolbox::instance()->net->url('login', '', true, false));
        }
    }

    /**
     * Ensure the page is accessed using proper security.
     *
     * <p>If a page is requested using HTTP and the page is mapped as <em>secure</code>, a
     * redirect using SSL will be performed.</p>
     *
     * @param string page The page; default is <code>null</code> to use the current page name.
     */
    public function ensureAccessMethod($page=null) {
        $secure = $this->getMappingValue($page, 'level', false);
        if ($secure && !ZMRequest::isSecure() && ZMSettings::get('isEnableSSL') && ZMSettings::get('isEnforceSSL')) {
            ZMRequest::redirect(ZMToolbox::instance()->net->url(null, null, true, false));
        }
    }

    /**
     * Get mapping value.
     *
     * @param string page The page [name].
     * @param string key The mapping key.
     * @param mixed default The mapping key.
     * @return mixed The value or the provided default value; default is <code>null</code>.
     */
    protected function getMappingValue($page, $key, $default=null) {
        if (null == $page) {
            $page = ZMRequest::getPageName();
        }

        if (!isset($this->mapping_[$page])) {
            return $default;
        }

        return $this->mapping_[$page][$key];
    }

    /**
     * Check if a request to the given page [name] is required to be secure.
     *
     * @param string page The page [name].
     * @return boolean <code>true</code> if a secure conenction is required.
     */
    public function secureRequired($page) {
        return $this->getMappingValue($page, 'level', false);
    }

}

?>
