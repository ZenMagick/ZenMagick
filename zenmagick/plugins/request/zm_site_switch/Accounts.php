<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
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
 * Split Accounts for multi sites
 *
 * @author mano
 * @package org.zenmagick.plugins.zm_site_switch
 * @version $Id$
 */
class Accounts extends ZMAccounts {
    private $isShareAccounts;

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->isShareAccounts = ZMSettings::get('plugins.zm_site_switch.isShareAccounts', true);
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * {@inheritDoc}
     */
    public function getAccountForEmailAddress($emailAddress) {
        if ($this->isShareAccounts) {
            return parent::getAccountForEmailAddress($emailAddress);
        }

        $sql = "SELECT c.*, ci.*
                FROM " . TABLE_CUSTOMERS . " c
                  LEFT JOIN " . TABLE_CUSTOMERS_INFO . " ci ON (c.customers_id = ci.customers_info_id)
                WHERE customers_email_address = :email
                AND NOT (customers_password = '')
                AND site_id = :siteId";
        $args = array('email' => $emailAddress, 'siteId' => ZMRequest::instance()->getHostname());
        return ZMRuntime::getDatabase()->querySingle($sql, $args, array(TABLE_CUSTOMERS, TABLE_CUSTOMERS_INFO), 'Account');
    }

    /**
     * {@inheritDoc}
     */
    public function emailExists($emailAddress) {
        if ($this->isShareAccounts) {
            return parent::emailExists($emailAddress);
        }

        $sql = "SELECT count(*) as total
                FROM " . TABLE_CUSTOMERS . " c
                WHERE customers_email_address = :email
                AND NOT (customers_password = '')
                AND site_id = :siteId";
        $args = array('email' => $emailAddress, 'siteId' => ZMRequest::instance()->getHostname());
        $result = ZMRuntime::getDatabase()->querySingle($sql, $args, array(TABLE_CUSTOMERS), ZMDatabase::MODEL_RAW);
        return 0 < $result['total'];
    }

    /**
     * {@inheritDoc}
     */
    public function createAccount($account) {
        $account->set('siteId', ZMRequest::instance()->getHostname());
        return parent::createAccount($account);
    }

}

?>
