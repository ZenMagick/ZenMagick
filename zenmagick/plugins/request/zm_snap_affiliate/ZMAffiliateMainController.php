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
        $result = ZMRuntime::getDatabase()->querySingle($sql, array('referrer_customers_id' => $account->getId()), TABLE_REFERRERS, 'ZMObject');
        if (null != $result) {
            $this->exportGlobal('referrer', $result);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function processGet() {
        if (null == $this->getGlobal('referrer')) {
            // logged in *and* signed up
            return $this->findView('signup');
        }
    }

    /*
$today = getdate();
$show_terms = isset( $_GET['terms'] );

$referrer = null;
$submitted = false;
$approved = false;
$banned = false;
$is_referrer = false;
$is_logged_in = isset( $_SESSION['customer_id'] );

$total_total = 0;
$total_commission = 0;
$unpaid_total = 0;
$unpaid_commission = 0;
$yearly_total = 0;
$yearly_commission = 0;
$last_payout = 0;
$next_payout = 0;

$activity_begin = mktime(0, 0, 0, $today['mon'], 1, $today['year']);
$activity_end = mktime(23, 59, 59, $activity_begin['mon'] + 1, 0, $today['year']);
$activity_total = 0;
$activity_commission = 0;
$activity = array();

if( isset($_GET['start']) ) {
  $activity_begin = intval( $_GET['start'] );
}

if( $activity_begin > time() ) {
  $activity_begin = time();
}

if( isset( $_GET['end'] ) ) {
  $activity_end = intval( $_GET['end'] );
}

if( $activity_begin > $activity_end ) {
  $tempDate = getdate($activity_begin);

  $activity_end = mktime( 23, 59, 59, $tempDate['mon']+1, 0, $tempDate['year'] );
}

if( $is_logged_in ) {
  $query = "select * from ". TABLE_REFERRERS ." where referrer_customers_id = " . intval($_SESSION['customer_id']);

  $referrer = $db->Execute($query);

  if( $referrer && $referrer->fields ) {

    $referrer = $referrer->fields;
    $submitted = true;

    if( isset($referrer['referrer_approved']) ) {
      $approved = intval($referrer['referrer_approved']) != 0;
    }

    if( isset($referrer['referrer_banned']) ) {
      $banned = intval($referrer['referrer_banned']) != 0;
    }

    if( $approved ) {
      $year_start = mktime(0,0,0, 1, 1, $today['year']);
      $query = "select o.date_purchased, o.order_total, c.commission_paid, c.commission_rate, t.value " .
	"from ". TABLE_ORDERS ." as o, ". TABLE_ORDERS_TOTAL ." as t, " . TABLE_COMMISSION . " as c where " .
	"c.commission_referrer_key = \"" . $referrer['referrer_key'] . "\" and o.orders_id = t.orders_id and o.orders_id = c.commission_orders_id and t.class = \"ot_shipping\"";

      $totals = $db->Execute($query);

      while( !$totals->EOF ) {
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

	$totals->MoveNext();
      }
    }
  } 
*/
}

?>
