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

define('FILENAME_OPEN_ID', 'openID');
define('OPENID_ENABLED', true);
define('Auth_OpenID_RAND_SOURCE', null); 


/**
 * Support for OpenID.
 *
 * @package org.zenmagick.plugins.zm_openid
 * @author DerManoMann
 * @version $Id$
 */
class zm_openid extends ZMPlugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('ZenMagick OpenID', 'Allows to login using OpenID', '${plugin.version}');
        $this->setLoaderSupport('ALL');
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Install this plugin.
     */
    function install() {
        parent::install();
        ZMDbUtils::executePatch(file($this->getPluginDir()."sql/install-openid.sql"), $this->messages_);
    }

    /**
     * Remove this plugin.
     *
     * @param boolean keepSettings If set to <code>true</code>, the settings will not be removed; default is <code>false</code>.
     */
    function remove($keepSettings=false) {
        parent::remove($keepSettings);
        ZMDbUtils::executePatch(file($this->getPluginDir()."sql/uninstall.sql"), $this->messages_);
    }

    /**
     * Init this plugin.
     */
    function init() {
        parent::init();

        // add OpenID field to accounts fields list
        $key = 'sql.customers.customFields';
        ZMSettings::set($key, ZMSettings::get($key, '').',openid;string');

        // create new validation ruleset for login
        ZMValidator::instance()->addRuleSet(new ZMRuleSet('openid_login', array(
            new ZMRequiredRule('openid', 'Please enter your OpenID.')
        )));

        // add validation rule for account edit
        ZMValidator::instance()->addRule('edit_account',
            ZMLoader::make('ZMUniqueOpenIDRule', 'openid')
        );
    }

    /**
     * Find account for a given OpenID.
     *
     * @param string openid The OpenID.
     * @return ZMAccount The account or <code>null</code>.
     */
    public function getAccountForOpenID($openid) {
        $sql = "SELECT customers_id from ".TABLE_CUSTOMERS."
                WHERE openid = :openid";
        $args = array('openid' => $openid);
        $result = ZMRuntime::getDatabase()->querySingle($sql, $args, TABLE_CUSTOMERS);
        if (null != $result) {
            return ZMAccounts::instance()->getAccountForId($result['accountId']);
        }
        return null;
    }

}

?>
