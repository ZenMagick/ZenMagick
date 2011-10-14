<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
namespace zenmagick\apps\store\bundles\ZenCartBundle\Mock;

use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;

/**
 * A zencart order based on the shopping cart.
 *
 * @author DerManoMann
 * @package zenmagick.apps.store.bundles.ZenCartBundle.Mock
 */
class ZenCartCheckoutOrder extends ZMObject {
    public $content_type;
    public $info;
    public $products;
    public $customer;
    public $shipping;
    public $delivery;


    /**
     * Create new instance for the given shopping cart.
     *
     * @param ZMShoppingCart shoppingCart The shopping cart; default is <code>null</code>
     */
    public function __construct($shoppingCart=null) {
        parent::__construct();
        $this->setShoppingCart($shoppingCart);
    }

    /**
     * Set the shopping cart.
     *
     * @param ZMShoppingCart shoppingCart The shopping cart.
     */
    public function setShoppingCart($shoppingCart) {
        if (null == $shoppingCart) {
            return;
        }

        // type
        $this->content_type = $shoppingCart->getType();

        // general stuff
        // TODO: where from/to??
        $languageId = $this->container->get('settingsService')->get('storeDefaultLanguageId');
        // TODO: move all cart/session values into ZMShoppingCart
        $currencyCode = $this->container->get('session')->getCurrencyCode();
        $couponCode = null;
        if (null != ($couponCodeId = $this->container->get('session')->getValue('cc_id'))) {
            $coupon = $this->container->get('couponService')->getCouponForId($couponCodeId, $languageId);
            if (null != $coupon) {
                $couponCode = $coupon->getCode();
            }
        }
        $shippingMethod = $shoppingCart->getSelectedShippingMethod();
        $paymentType = $shoppingCart->getSelectedPaymentType();
        $this->info = array(
            'order_status' => DEFAULT_ORDERS_STATUS_ID,
            'currency' => $currencyCode,
            'currency_value' => $this->container->get('currencyService')->getCurrencyForCode($currencyCode)->getRate(),
            'payment_method' => null != $paymentType ? $paymentType->getName() : '',
            'payment_module_code' => null != $paymentType ? $paymentType->getId() : '',
            'coupon_code' => $couponCode,
            'shipping_method' => null != $shippingMethod ? $shippingMethod->getName() : '',
            'shipping_module_code' => null != $shippingMethod ? $shippingMethod->getShippingId() : '',
            'shipping_cost' => null != $shippingMethod ? $shippingMethod->getCost() : '',
            'subtotal' => $shoppingCart->getSubTotal(),
            'shipping_tax' => 0, //TODO?
            'tax' => 0, //TODO?
            'total' => $shoppingCart->getTotal(),
            'tax_groups' => array(),
            'comments' => $shoppingCart->getComments()
        );
if ($this->container->get('settingsService')->get('zenmagick.apps.store.assertZencart', false)) {
  $order = new \order();
  foreach ($order->info as $key => $value) {
      if (in_array($key, array('rowClass', 'ip_address'))) { continue; }
      if (array_key_exists($key, $this->info)) {
        if ('tax_groups' == $key) {
          $mytg = $this->info[$key];
          if (count($value) != count($mytg)) {
            echo 'tax groups length diff! order: ';var_dump($value);echo 'my: ';var_dump($mytg);echo '<br>';
          }
            continue;
        }
        if ($value != $this->info[$key]) {
            echo 'info value mismatch for '.$key.': value='.$value.', got: '.$this->info[$key]."<BR>";
        }
      } else {
        echo 'info missing key: '.$key.', value is: '.$value."<BR>";
      }
  }
}

        // account
        $account = $this->container->get('accountService')->getAccountForId($shoppingCart->getAccountId());
        $this->setAccount($account);

        // addresses
        $this->setShippingAddress($shoppingCart->getShippingAddress());
        $this->setBillingAddress($shoppingCart->getBillingAddress());

        // TODO: fill with something to have the correct count
        $taxAddress = $shoppingCart->getTaxAddress();
        $this->products = array();
        foreach ($shoppingCart->getItems() as $item) {
            $itemProduct = $item->getProduct();
            $offers = $itemProduct->getOffers();
            $taxRate = $itemProduct->getTaxRate();
            $product = array(
                'id' => $itemProduct->getId(),
                'qty' => $item->getQuantity(),
                'name' => $itemProduct->getName(),
                'model' => $itemProduct->getModel(),
                //'tax' => zen_get_tax_rate($products[$i]['tax_class_id'], $tax_address->fields['entry_country_id'], $tax_address->fields['entry_zone_id']),
                //'tax_groups'=>$taxRates,
                //'tax_description' => zen_get_tax_description($products[$i]['tax_class_id'], $tax_address->fields['entry_country_id'], $tax_address->fields['entry_zone_id']),
                'price' => $itemProduct->getProductPrice(),
                'final_price' => $offers->getCalculatedPrice(),
                'onetime_charges' => 0, //TODO: $_SESSION['cart']->attributes_price_onetime_charges($products[$i]['id'], $products[$i]['quantity']),
                'weight' => $itemProduct->getWeight(),
                'products_priced_by_attribute' => $itemProduct->isPricedByAttributes(),
                'product_is_free' => $itemProduct->isFree(),
                'products_discount_type' => $itemProduct->getDiscountType(),
                'products_discount_type_from' => $itemProduct->getDiscountTypeFrom()
            );
            $this->products[] = $product;
        }
if ($this->container->get('settingsService')->get('zenmagick.apps.store.assertZencart', false)) {
  $order = new \order();
  foreach ($order->products[0] as $key => $value) {
      if (in_array($key, array('rowClass'))) { continue; }
      if (array_key_exists($key, $this->products[0])) {
        if ('tax_groups' == $key) {
          $mytg = $this->products[0][$key];
          if (count($value) != count($mytg)) {
            echo 'tax groups length diff! order: ';var_dump($value);echo 'my: ';var_dump($mytg);echo '<br>';
          }
            continue;
        }
        if ($value != $this->products[0][$key]) {
            echo 'product value mismatch for '.$key.': value='.$value.', got: '.$this->products[0][$key]."<BR>";
        }
      } else {
        echo 'product missing key: '.$key.', value is: '.$value."<BR>";
      }
  }
}

    }

    /**
     * Address to array.
     *
     * @param ZMAddress address The address.
     * @return array Address array.
     */
    protected function address2array($address) {
        $aa = array();
        $aa['country'] = array();
        if (null != $address) {
            $aa['firstname'] = $address->getFirstName();
            $aa['lastname'] = $address->getLastName();
            $aa['street_address'] = $address->getAddressLine1();
            $aa['city'] = $address->getCity();
            $aa['suburb'] = $address->getSuburb();
            $aa['zone_id'] = $address->getZoneId();
            $aa['state'] = $address->getState();
            $aa['postcode'] = $address->getPostcode();
            if (null != ($country = $address->getCountry())) {
                $aa['country']['id'] = $country->getId();
                $aa['country']['title'] = $country->getName();
                $aa['country']['iso_code_2'] = $country->getIsoCode2();
            }
        }
        return $aa;
    }

    /**
     * Set the account.
     *
     * @param ZMAccount account The account.
     */
    public function setAccount($account) {
        $primaryAddress = $this->container->get('addressService')->getAddressForId($account->getDefaultAddressId());
        $customer = $this->address2array($primaryAddress);
        $customer['firstname'] = $account->getFirstName();
        $customer['lastname'] = $account->getLastName();
        $customer['email_address'] = $account->getEmail();
        $this->customer = $customer;
    }

    /**
     * Set shipping address.
     *
     * @param ZMAddress address The shipping address.
     */
    public function setShippingAddress($address) {
        $this->shipping = $this->address2array($address);
    }

    /**
     * Set billing address.
     *
     * @param ZMAddress address The billing address.
     */
    public function setBillingAddress($address) {
        $this->delivery = $this->address2array($address);
    }

}
