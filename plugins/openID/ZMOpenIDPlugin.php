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

define('OPENID_ENABLED', true);
define('Auth_OpenID_RAND_SOURCE', null);


/**
 * Support for OpenID.
 *
 * @package org.zenmagick.plugins.openID
 * @author DerManoMann <mano@zenmagick.org>
 */
class ZMOpenIDPlugin extends Plugin {

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct('OpenID', 'Allows to login using OpenID', '${plugin.version}');
    }


    /**
     * {@inheritDoc}
     */
    public function install() {
        parent::install();
        ZMDbUtils::executePatch(file(ZMDbUtils::resolveSQLFilename($this->getPluginDirectory()."/sql/install-openid.sql")), $this->messages_);
        $this->addConfigValue('Allowed OpenID provider', 'openIDProvider', '', 'A list of allowed OpenID identity providers (separated by \'|\').');
    }

    /**
     * {@inheritDoc}
     */
    public function remove($keepSettings=false) {
        parent::remove($keepSettings);
        ZMDbUtils::executePatch(file(ZMDbUtils::resolveSQLFilename($this->getPluginDirectory()."/sql/uninstall.sql")), $this->messages_);
    }

    /**
     * {@inheritDoc}
     */
    public function init() {
        parent::init();
        zenmagick\base\Runtime::getEventDispatcher()->listen($this);

        // add OpenID field to accounts fields list
        ZMSettings::append('zenmagick.core.database.sql.customers.customFields', 'openid;string');

        // make openid_login use session token
        ZMSettings::append('zenmagick.mvc.html.tokenSecuredForms', 'openid_login');

        // add success URL mapping if none exists
        ZMUrlManager::instance()->setMapping('openID', array(
            'success' => array('template' => 'account', 'view' => 'RedirectView')
        ));

        // TODO: manually load lib for now
        require_once dirname(__FILE__).'/lib/openid-2.1.3-php53.packed.php';
    }

    /**
     * Init done callback.
     *
     * <p>Setup additional validation rules; this is done here to avoid getting in the way of
     * custom global/theme validation rule setups.</p>
     */
    public function onInitDone($event) {
        // initial rule
        $rules = array(
            array('ZMRequiredRule', 'openid', 'Please enter your OpenID.')
        );
        $providerList = trim($this->get('openIDProvider'));
        if (!empty($providerList)) {
            $rules[] = array('ZMRegexpRule', 'openid', '/'.$providerList.'/', 'The provider of the entered OpenID is currently not supported.');
        }
        // validation rules for login
        ZMValidator::instance()->addRules('openid_login', $rules);

        // add validation rule for account edit
        ZMValidator::instance()->addRule('edit_account', array('ZMUniqueOpenIDRule', 'openid'));
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
            return $this->container->get('accountService')->getAccountForId($result['accountId']);
        }
        return null;
    }

}
