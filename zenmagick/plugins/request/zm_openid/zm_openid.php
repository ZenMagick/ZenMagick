<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
        parent::__construct('OpenID', 'Allows to login using OpenID', '${plugin.version}');
        $this->setLoaderPolicy(ZMPlugin::LP_ALL);

        // add OpenID field to accounts fields list
        ZMSettings::append('sql.customers.customFields', 'openid;string', ',');
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
        ZMDbUtils::executePatch(file(ZMDbUtils::resolveSQLFilename($this->getPluginDir()."sql/install-openid.sql")), $this->messages_);

        $this->addConfigValue('Allowed OpenID provider', 'openIDProvider', '', 'A list of allowed OpenID identity providers (separated by \'|\').');
    }

    /**
     * Remove this plugin.
     *
     * @param boolean keepSettings If set to <code>true</code>, the settings will not be removed; default is <code>false</code>.
     */
    function remove($keepSettings=false) {
        parent::remove($keepSettings);
        ZMDbUtils::executePatch(file(ZMDbUtils::resolveSQLFilename($this->getPluginDir()."sql/uninstall.sql")), $this->messages_);
    }

    /**
     * Init this plugin.
     */
    function init() {
        parent::init();

        // make openid_login use session token
        $tokenSecuredForms = ZMSettings::get('tokenSecuredForms', '');
        ZMSettings::set('tokenSecuredForms', $tokenSecuredForms.',openid_login');

        // initial rule
        $rules = array(
            array('RequiredRule', 'openid', 'Please enter your OpenID.')
        );
        $providerList = trim($this->get('openIDProvider'));
        if (!empty($providerList)) {
            $rules[] = array('ZMRegexpRule', 'openid', '/'.$providerList.'/', 'The provider of the entered OpenID is currently not supported.');
        }
        // validation rules for login
        ZMValidator::instance()->addRules('openid_login', $rules);

        // add validation rule for account edit
        ZMValidator::instance()->addRule('edit_account', array('ZMUniqueOpenIDRule', 'openid'));

        // add success URL mapping if none exists
        ZMUrlMapper::instance()->setMapping('openID', 'success', 'account', 'RedirectView', 'secure=true');

        // register tests
        if (null != ($tests = ZMPlugins::instance()->getPluginForId('zm_tests'))) {
            // add class path only now to avoid errors due to missing ZMTestCase
            ZMLoader::instance()->addPath($this->getPluginDir().'tests/');
            $tests->addTest('TestZMDatabaseOpenIDStore');
        }
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
            return ZMAccounts::instance()->getAccountForId($result['id']);
        }
        return null;
    }

}

?>
