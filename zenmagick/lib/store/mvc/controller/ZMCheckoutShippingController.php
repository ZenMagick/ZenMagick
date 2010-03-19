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
 * Request controller for checkout shipping page.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.controller
 * @version $Id$
 */
class ZMCheckoutShippingController extends ZMController {
    private $viewData_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->viewData_ = array();
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
        $shoppingCart = $request->getShoppingCart();
        // set default address if required
        if (!$shoppingCart->hasShippingAddress()) {
            $account = $request->getAccount();
            $shoppingCart->setShippingAddressId($account->getDefaultAddresssId());
            // TODO: reset selected shipping method as address changed (if addressId set in session is invalid)
        }

        $request->getToolbox()->crumbtrail->addCrumb("Checkout", $request->getToolbox()->net->url(FILENAME_CHECKOUT_SHIPPING, '', true));
        $request->getToolbox()->crumbtrail->addCrumb($request->getToolbox()->utils->getTitle());

        $this->viewData_['shoppingCart'] = $shoppingCart;
    }

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        $shoppingCart = $request->getShoppingCart();
        $checkoutHelper = ZMLoader::make('CheckoutHelper', $shoppingCart);

        if (null !== ($viewId = $checkoutHelper->validateCheckout(false))) {
            return $this->findView($viewId, $this->viewData_);
        }

        if (!$checkoutHelper->verifyHash($request)) {
            return $this->findView('check_cart');
        }

        if ($checkoutHelper->isVirtual()) {
            // TODO: set free shipping
            //return $this->findView('skip_shipping');
        }

        return $this->findView(null, $this->viewData_);
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $shoppingCart = $request->getShoppingCart();
        $checkoutHelper = ZMLoader::make('CheckoutHelper', $shoppingCart);

        if (null !== ($viewId = $checkoutHelper->validateCheckout(false))) {
            return $this->findView($viewId, $this->viewData_);
        }

        if (!$checkoutHelper->verifyHash($request)) {
            return $this->findView('check_cart');
        }

        if ($checkoutHelper->isVirtual()) {
            // TODO: set free shipping
            //return $this->findView('skip_shipping');
        }

        return parent::processPost($request);
    }

}

/********************



// load all enabled shipping modules
  require(DIR_WS_CLASSES . 'shipping.php');
  $shipping_modules = new shipping;

  if ( defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING') && (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true') ) {
    $pass = false;

    switch (MODULE_ORDER_TOTAL_SHIPPING_DESTINATION) {
      case 'national':
        if ($order->delivery['country_id'] == STORE_COUNTRY) {
          $pass = true;
        }
        break;
      case 'international':
        if ($order->delivery['country_id'] != STORE_COUNTRY) {
          $pass = true;
        }
        break;
      case 'both':
        $pass = true;
        break;
    }

    $free_shipping = false;
    if ( ($pass == true) && ($_SESSION['cart']->show_total() >= MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER) ) {
      $free_shipping = true;
    }
  } else {
    $free_shipping = false;
  }

  require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));

  if (isset($_SESSION['comments'])) {
    $comments = $_SESSION['comments'];
  }


// process the selected shipping method
  if ( isset($_POST['action']) && ($_POST['action'] == 'process') ) {
    if (zen_not_null($_POST['comments'])) {
      $_SESSION['comments'] = zen_db_prepare_input($_POST['comments']);
    }
    $comments = $_SESSION['comments'];

    if ( (zen_count_shipping_modules() > 0) || ($free_shipping == true) ) {
      if ( (isset($_POST['shipping'])) && (strpos($_POST['shipping'], '_')) ) {
        $_SESSION['shipping'] = $_POST['shipping'];

        list($module, $method) = explode('_', $_SESSION['shipping']);
        if ( is_object($$module) || ($_SESSION['shipping'] == 'free_free') ) {
          if ($_SESSION['shipping'] == 'free_free') {
            $quote[0]['methods'][0]['title'] = FREE_SHIPPING_TITLE;
            $quote[0]['methods'][0]['cost'] = '0';
          } else {
            $quote = $shipping_modules->quote($method, $module);
          }
          if (isset($quote['error'])) {
            $_SESSION['shipping'] = '';
          } else {
            if ( (isset($quote[0]['methods'][0]['title'])) && (isset($quote[0]['methods'][0]['cost'])) ) {
              $_SESSION['shipping'] = array('id' => $_SESSION['shipping'],
                                'title' => (($free_shipping == true) ?  $quote[0]['methods'][0]['title'] : $quote[0]['module'] . ' (' . $quote[0]['methods'][0]['title'] . ')'),
                                'cost' => $quote[0]['methods'][0]['cost']);

              zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
            }
          }
        } else {
          $_SESSION['shipping'] = false;
        }
      }
    } else {
      $_SESSION['shipping'] = false;

      zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
    }
  }


*************/
