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


/**
 * Handle affiliate signup.
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.zm_snap_affiliate
 * @version $Id$
 */
class ZMAffiliateSignupController extends ZMController {

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
    public function handleRequest($request) {
        $session = $request->getSession();
        if ($session->isRegistered()) {
            $request->getToolbox()->crumbtrail->addCrumb("Affiliates", $request->getToolbox()->net->url('affiliate', '', true, false));
        } else {
            $request->getToolbox()->crumbtrail->addCrumb("Affilites");
        }
        $request->getToolbox()->crumbtrail->addCrumb("Signup");

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

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        if (null != $request->getParameter('referrer')) {
            // logged in *and* signed up
            return $this->findView('main');
        }
        return $this->findView();
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $plugin = $this->getPlugin();
        $url = $request->getParameter('url');
        $key = $plugin->get('affiliatePrefix');
        $key .= ZMTools::random(32-strlen($key));
	      $commission = $plugin->get('defaultCommission');
        $account = $request->getAccount();

        $sql = "INSERT INTO " . TABLE_REFERRERS ."
	                (referrer_customers_id, referrer_key, referrer_homepage, referrer_approved, referrer_banned, referrer_commission)
	                VALUES (:referrer_customers_id, :referrer_key, :referrer_homepage, 0, 0, :referrer_commission)";
        $args = array('referrer_customers_id' => $account->getId(), 'referrer_key' => $key, 'referrer_homepage' => $url, 'referrer_commission' => $commission);
        ZMRuntime::getDatabase()->update($sql, $args, TABLE_REFERRERS);

        //TODO: email

        return $this->findView('success');
    }

    /**
     * Send notification email.
     *
     * @param array data The form data.
     * @param string template The template.
     * @param string email The email address.
     */
    protected function sendNotificationEmail($data, $template, $email) {
        if (empty($email)) {
            $email = ZMSettings::get('storeEmail');
        }
        zm_mail(zm_l10n_get("Form Handler notification: %s", $this->getId()), $template, array('data' => $data, 'id' => $this->getId()), 
            $email, ZMSettings::get('storeEmail'), null);
    }

    /**
     * Get the plugin.
     *
     * @return ZMPlugin The plugin.
     */
    protected function getPlugin() {
        return ZMPlugins::instance()->getPluginForId('zm_snap_affiliate');
    }

}

?>
