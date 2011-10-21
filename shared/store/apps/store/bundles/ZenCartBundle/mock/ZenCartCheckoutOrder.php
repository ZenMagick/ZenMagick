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
namespace apps\store\bundles\ZenCartBundle\Mock;

use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;

/**
 * A zencart order based on the shopping cart.
 *
 * @author DerManoMann
 * @package apps.store.bundles.ZenCartBundle.Mock
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

        $this->info = array();

        // products
        $this->populateProducts($shoppingCart);

        // populate info - ATTENTION: this depends on populateProducts!!
        $this->populateInfo($shoppingCart);

        // account
        $account = $this->container->get('accountService')->getAccountForId($shoppingCart->getAccountId());
        $this->setAccount($account);

        // addresses
        $this->setShippingAddress($shoppingCart->getShippingAddress());
        $this->setBillingAddress($shoppingCart->getBillingAddress());
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
     * Populate products.
     *
     * @param ZMShoppingCart shoppingCart The shopping cart.
     */
    protected function populateProducts($shoppingCart) {
        $taxAddress = $shoppingCart->getTaxAddress();
        $this->products = array();
        foreach ($shoppingCart->getItems() as $item) {
            $itemProduct = $item->getProduct();

            $productTaxRate = $item->getTaxRate();
            $taxRates = array();
            foreach ($item->getTaxRates() as $taxRate) {
                $taxRates[$taxRate->getDescription()] = $taxRate->getRate();
            }

            $offers = $itemProduct->getOffers();
            $price = $itemProduct->getProductPrice();
            if (null != ($quantityDiscount = $offers->getQuantityDiscountFor($item->getQuantity(), false))) {
                $price = $quantityDiscount->getPrice();
            }

            $product = array(
                'id' => $item->getId(),
                'qty' => $item->getQuantity(),
                'name' => $itemProduct->getName(),
                'model' => $itemProduct->getModel(),
                'tax' => $productTaxRate->getRate(),
                'tax_groups' => $taxRates,
                'tax_description' => $productTaxRate->getDescription(),
                'price' => $price,
                'final_price' => $item->getItemPrice(false),
                'onetime_charges' => $item->getOneTimeCharge(false),
                'weight' => $item->getWeight(),
                'products_priced_by_attribute' => $itemProduct->isPricedByAttributes(),
                'product_is_free' => $itemProduct->isFree() ? '1' : '0',
                'products_discount_type' => $itemProduct->getDiscountType(),
                'products_discount_type_from' => $itemProduct->getDiscountTypeFrom()
            );
            $attributes = array();
            foreach ($item->getAttributes() as $attribute) {
                $arr = array('option' => $attribute->getName(), 'option_id' => $attribute->getId());
                // TODO: what about multiple values??
                foreach ($attribute->getValues() as $value) {
                    $arr['value_id'] = $value->getId();
                    $arr['value'] = $value->getName();
                    $arr['prefix'] = $value->getPricePrefix();
                    // TODO: prices are wrong!
                    $arr['price'] = $value->getPrice(true, $item->getQuantity());
                }
                $attributes[] = $arr;
            }
            if (0 < count($attributes)) {
                $product['attributes'] = $attributes;
            }
            $this->products[] = $product;
        }

        if ($this->container->get('settingsService')->get('apps.store.assertZencart', false)) {
            $order = new \order();
            foreach (array_keys($this->products) as $ii) {
                echo '<h3>'.$this->products[$ii]['id'].':'.$this->products[$ii]['name'].'</h3>';
                foreach ($order->products[$ii] as $key => $value) {
                    if (in_array($key, array('rowClass'))) { continue; }
                    if (array_key_exists($key, $this->products[$ii])) {
                        if (in_array($key, array('tax_groups', 'attributes'))) {
                            $mytg = $this->products[$ii][$key];
                            if (count($value) != count($mytg)) {
                                echo 'PRODUCT: '.$key.' diff! order: ';var_dump($value);echo 'ZM got: ';var_dump($mytg);echo '<br>';
                            }
                            foreach (array_keys($value) as $ak) {
                                if (!array_key_exists($ak, $mytg)) {
                                    echo 'PRODUCT: '.$key.' diff! missing elem: '.$ak.' => '.$value[$ak];echo '<br>';
                                }
                            }
                            foreach (array_keys($mytg) as $ak) {
                                if (!array_key_exists($ak, $value)) {
                                    echo 'PRODUCT: '.$key.' diff! additional elem: '.$ak .' => '.$mytg[$ak];echo '<br>';
                                }
                            }
                            // todo recursive check...
                            continue;
                        }
                        if ((string)$value != (string)$this->products[$ii][$key]) {
                            echo 'PRODUCT: value mismatch for '.$key.': value=';var_dump($value); echo ', ZM got: ';var_dump($this->products[$ii][$key]); echo "<BR>";
                        }
                    } else {
                        echo 'PRODUCT: missing key: '.$key.', value is: '.$value."<BR>";
                        if (is_array($value)) { var_dump($value);echo '<br>'; }
                    }
                }
            }
            echo '<br>';
        }
    }

    /**
     * Populate info.
     *
     * @param ZMShoppingCart shoppingCart The shopping cart.
     */
    protected function populateInfo($shoppingCart) {
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
        $orderStatus = DEFAULT_ORDERS_STATUS_ID;
        if (null != $paymentType && null !== ($pos = $paymentType->getOrderStatus())) {
            $orderStatus = $pos;
        }
        $info = array(
            'order_status' => $orderStatus,
            'currency' => $currencyCode,
            'currency_value' => $this->container->get('currencyService')->getCurrencyForCode($currencyCode)->getRate(),
            'payment_method' => null != $paymentType ? $paymentType->getTitle() : '',
            'payment_module_code' => null != $paymentType ? $paymentType->getId() : '',
            'coupon_code' => $couponCode,
            'shipping_method' => null != $shippingMethod ? $shippingMethod->getName() : '',
            'shipping_module_code' => null != $shippingMethod ? $shippingMethod->getShippingId() : '',
            'shipping_cost' => null != $shippingMethod ? $shippingMethod->getCost() : '',
            'subtotal' => $shoppingCart->getSubTotal(),
            'shipping_tax' => 0, //TODO?
            'tax' => 0, //TODO?
            'total' => $shoppingCart->getTotal(), //TODO: drop tax
            'tax_groups' => array(),
            'comments' => $shoppingCart->getComments()
        );

        if ($this->container->get('settingsService')->get('apps.store.assertZencart', false)) {
            $order = new \order();
            foreach ($order->info as $key => $value) {
                if (in_array($key, array('rowClass', 'ip_address'))) { continue; }
                if (array_key_exists($key, $info)) {
                    if ('tax_groups' == $key) {
                        // drop [0] as that is the default for none in zc
                        if (isset($value[0])) { unset($value[0]); }
                        $mytg = $info[$key];
                        if (count($value) != count($mytg)) {
                          echo 'info: tax groups length diff! order: ';var_dump($value);echo 'ZM got: ';var_dump($mytg);echo '<br>';
                        }
                        continue;
                    }
                    if ((string)$value != (string)$info[$key]) {
                        echo 'info: value mismatch for '.$key.': value=';var_dump($value); echo ', ZM got: ';var_dump($info[$key]); echo "<BR>";
                    }
                } else {
                    echo 'info: missing key: '.$key.', value is: '.$value."<BR>";
                }
            }
            echo '<br>';
        }

        $this->info = array_merge($this->info, $info);
    }


    /**
     * Set the account.
     *
     * @param ZMAccount account The account.
     */
    public function setAccount($account) {
        if (null != $account) {
            $primaryAddress = $this->container->get('addressService')->getAddressForId($account->getDefaultAddressId());
            $customer = $this->address2array($primaryAddress);
            $customer['firstname'] = $account->getFirstName();
            $customer['lastname'] = $account->getLastName();
            $customer['email_address'] = $account->getEmail();
            $this->customer = $customer;
        }
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
