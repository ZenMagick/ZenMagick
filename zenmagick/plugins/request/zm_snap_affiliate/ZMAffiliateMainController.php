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
 * Handle main affiliate page.
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.zm_snap_affiliate
 * @version $Id$
 */
class ZMAffiliateMainController extends ZMController {

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
    public function handleRequest() {
        ZMCrumbtrail::instance()->addCrumb("Affilite Overview");

        $account = ZMRequest::getAccount();

        // check for existing referrer
        $sql = "SELECT * FROM ". TABLE_REFERRERS ." 
                WHERE referrer_customers_id = :referrer_customers_id";
        $result = Runtime::getDatabase()->querySingle($sql, array('referrer_customers_id' => $account->getId()), TABLE_REFERRERS, 'AffiliateDetails');
        if (null != $result) {
            $this->exportGlobal('affiliateDetails', $result);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function processGet() {
        if (null == ($affiliateDetails = $this->getGlobal('affiliateDetails'))) {
            // logged in *and* signed up
            return $this->findView('signup');
        }

        $today = getdate();
        $activityBegin = mktime(0, 0, 0, $today['mon'], 1, $today['year']);
        $activityEnd = mktime(23, 59, 59, $activityBegin['mon'] + 1, 0, $today['year']);

        // date range selected?
        if (null != ($start = ZMRequest::getParameter('start'))) {
            $activityBegin = intval($start);
        }
        if ($activityBegin > time()) {
            $activityNegin = time();
        }
        if (null != ($end = ZMRequest::getParameter('end'))) {
            $activityEnd = intval($end);
        }

        if ($activityBegin > $activityEnd) {
            $tempDate = getdate($activityBegin);
            $activityEnd = mktime(23, 59, 59, $tempDate['mon']+1, 0, $tempDate['year']);
        }

        if ($affiliateDetails->referrer_approved) {
            $yearStart = mktime(0,0,0, 1, 1, $today['year']);
            $sql = "SELECT o.date_purchased, o.order_total, c.commission_paid, c.commission_rate, t.value " .
                   "FROM ". TABLE_ORDERS ." AS o, ". TABLE_ORDERS_TOTAL ." AS t, " . TABLE_COMMISSION . " AS c " .
                   "WHERE c.commission_referrer_key = :commission_referrer_key AND o.orders_id = t.orders_id
                       AND o.orders_id = c.commission_orders_id AND t.class = :type";
            $args = array('commission_referrer_key' => $affiliateDetails->referrer_key, 'type' => 'ot_shipping');
            foreach (Runtime::getDatabase()->query($sql, $args, array(TABLE_REFERRERS, TABLE_ORDERS_TOTAL, TABLE_COMMISSION)) as $result) {
              /*
	$commission = floatval($totals->fields['commission_rate']);
	$purchase_date = strtotime($totals->fields['date_purchased']);
	$current_date = $totals->fields['commission_paid'];
	$current_shipping = floatval($totals->fields['value']);
	$current_amount = floatval($totals->fields['order_total']) - $current_shipping;

	if( $current_amount < 0 ) {
	  $current_amount = 0;
	}

	if( $current_date != "0000-00-00 00:00:00" ) {
	  $current_date = strtotime($current_date);
	} else {
	  $current_date = 0;
	}

	$total_total += $current_amount;
	$total_commission += $commission * $current_amount;

	if( $purchase_date > $year_start ) {
	  $yearly_total += $current_amount;
	  $yearly_commission += $commission * $current_amount;
	}
	
	if( $totals->fields['commission_paid'] == "0000-00-00 00:00:00" ) {
	  $unpaid_total += $current_amount;
	  $unpaid_commission += $commission * $current_amount;
	}
	
	if( $current_date > $last_payout ) {
	  $last_payout = $current_date;
	}


	if( $activity_begin < $purchase_date && $purchase_date < $activity_end ) {
	  $activity_total += $current_amount;
	  $activity_commission += $commission * $current_amount;

	  array_push( $activity, array('amount' => $current_amount, 'date' => $purchase_date, 'paid' => $current_date, 'commission' => $commission) );
	}
*/
            }

        }

        return parent::processGet();
    }

}

?>
