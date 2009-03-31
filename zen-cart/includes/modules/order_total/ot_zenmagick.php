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
 * A central place for all runtime stuff.
 *
 * <p>This is kind of the <em>application context</em>.</p>
 * @author DerManoMann
 * @package org.zenmagick
 * @version $Id: ZMRuntime.php 2096 2009-03-23 01:49:18Z dermanomann $
 */
class ot_zenmagick {
    var $title;
    var $description;
    var $code;
    var $sort_order;
    var $credit_class;


    /**
     * Create proxy instance.
     */
    function __construct() {
        $this->title = 'ZenMagick Order Totals';
        $this->description = 'ZenMagick Order Total Proxy';
        $this->code = 'ot_zenmagick';
        $this->sort_order = 0;
        // to start with
        $this->credit_class = false;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
    }


    /**
     * Generate order total line(s).
     *
     * <p>Each order total line must contain the following elements:</p>
     * <ul>
     *  <li>title - The order total text.</li>
     *  <li>text - The order total value as string.</li>
     *  <li>value - The actual value as float.</li>
     * </ul>
     *
     * @return array A list of order total line info (which is of type <code>array</code> too).
     */
    public function process() {
        global $order, $currencies;
        $output = array('title' => 'Foo',
                      'text' => $currencies->format($order->info['total'], true, $order->info['currency'], $order->info['currency_value']),
                      'value' => $order->info['total']);
        $this->output[] = $output;
        $this->output[] = $output;
        //$this->output = array();
    }

    /**
     * Check if this module is active.
     *
     * @return boolean <code>true</code> if active.
     */
    public function check() {
        return true;
    }

    /**
     * Return configuration keys.
     *
     * @return array Empty list.
     */
    public function keys() {
        return array();
    }

    /**
     * Install module.
     */
    public function install() {
        return;
    }

    /**
     * Remove module.
     */
    public function remove() {
        return;
    }

    /*

    // credit class

  // The second function called is credit_selection(). This in the credit classes already made is usually a redeem box.
  // for entering a Gift Voucher number. Note credit classes can decide whether this part is displayed depending on
  // E.g. a setting in the admin section.
    // cc
    function credit_selection() {
        return array();
    }

  // update_credit_account is called in checkout process on a per product basis. It's purpose
  // is to decide whether each product in the cart should add something to a credit account.
  // e.g. for the Gift Voucher it checks whether the product is a Gift voucher and then adds the amount
  // to the Gift Voucher account.
  // Another use would be to check if the product would give reward points and add these to the points/reward account.
    // cc
    function update_credit_account($productIndex) {
        return;
    }

  // This function is called in checkout confirmation.
  // It's main use is for credit classes that use the credit_selection() method. This is usually for
  // entering redeem codes(Gift Vouchers/Discount Coupons). This function is used to validate these codes.
  // If they are valid then the necessary actions are taken, if not valid we are returned to checkout payment
  // with an error
    // cc
    function collect_posts() {
        return;
    }

    // cc
    function get_order_total() {
        return 0;
    }

  // pre_confirmation_check is called on checkout confirmation. It's function is to decide whether the
  // credits available are greater than the order total. If they are then a variable (credit_covers) is set to
  // true. This is used to bypass the payment method. In other words if the Gift Voucher is more than the order
  // total, we don't want to go to paypal etc.
    // cc
    function pre_confirmation_check($orderTotal) {
    global $credit_covers;
        // only if MODULE_ORDER_TOTAL_INSTALLED!!
        if ($covers_amount)
        $credit_covers = true;
        return;
    }

  // this function is called in checkout process. it tests whether a decision was made at checkout payment to use
  // the credit amount be applied aginst the order. If so some action is taken. E.g. for a Gift voucher the account
  // is reduced the order total amount.
  //
    // cc
    function apply_credit() {
        return;
    }

  // Called in checkout process to clear session variables created by each credit class module.
  //
    // cc
    function clear_posts() {
      return;
    }

     */

}

?>
