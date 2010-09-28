<?php
/*
 * ZenMagick - Smart e-commerce
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
 * Handle affiliate terms page.
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.zm_snap_affiliate
 */
class ZMAffiliateTermsController extends ZMController {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
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
    public function preProcess($request) {
        $request->getToolbox()->crumbtrail->addCrumb("Affilite Terms & Conditions");

        $session = $request->getSession();
        if ($session->isRegistered()) {
            $account = $request->getAccount();

            // check for existing referrer
            $sql = "SELECT * FROM ". TABLE_REFERRERS ." 
                    WHERE referrer_customers_id = :referrer_customers_id";
            $result = ZMRuntime::getDatabase()->querySingle($sql, array('referrer_customers_id' => $account->getId()), TABLE_REFERRERS, 'ZMObject');
            if (null != $result) {
                $request->setVar('referrer', $result);
            }
        }
    }

}
