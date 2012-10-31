<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
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

namespace ZenMagick\StoreBundle\Entity\Order;

use Doctrine\ORM\Mapping as ORM;
use ZenMagick\Base\Beans;
use ZenMagick\Base\Toolbox;
use ZenMagick\Base\ZMObject;
use ZenMagick\StoreBundle\Entity\Order\OrderTotalLine;

/**
 * A single order.
 *
 * @ORM\Table(name="orders",
 *  indexes={
 *      @ORM\Index(name="idx_status_orders_cust_zen", columns={"orders_status", "orders_id", "customers_id"}),
 *      @ORM\Index(name="idx_date_purchased_zen", columns={"date_purchased"}),
 *      @ORM\Index(name="idx_cust_id_orders_id_zen", columns={"customers_id", "orders_id"}),
 *  })
 * @ORM\Entity
 * @author DerManoMann
 */
class Order extends ZMObject
{
    /**
     * @var integer $orderId
     *
     * @ORM\Column(name="orders_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $orderId;

    /**
     * @var integer $accountId
     *
     * @ORM\Column(name="customers_id", type="integer", nullable=false)
     */
    private $accountId;

    /**
     * @var string $customers_name
     *
     * @ORM\Column(name="customers_name", type="string", length=64, nullable=false)
     */
    private $customers_name;

    /**
     * @var string $customers_company
     *
     * @ORM\Column(name="customers_company", type="string", length=64, nullable=true)
     */
    private $customers_company;

    /**
     * @var string $customers_street_address
     *
     * @ORM\Column(name="customers_street_address", type="string", length=64, nullable=false)
     */
    private $customers_street_address;

    /**
     * @var string $customers_suburb
     *
     * @ORM\Column(name="customers_suburb", type="string", length=32, nullable=true)
     */
    private $customers_suburb;

    /**
     * @var string $customers_city
     *
     * @ORM\Column(name="customers_city", type="string", length=32, nullable=false)
     */
    private $customers_city;

    /**
     * @var string $customers_postcode
     *
     * @ORM\Column(name="customers_postcode", type="string", length=10, nullable=false)
     */
    private $customers_postcode;

    /**
     * @var string $customers_state
     *
     * @ORM\Column(name="customers_state", type="string", length=32, nullable=true)
     */
    private $customers_state;

    /**
     * @var string $customers_country
     *
     * @ORM\Column(name="customers_country", type="string", length=32, nullable=false)
     */
    private $customers_country;

    /**
     * @var string $customers_telephone
     *
     * @ORM\Column(name="customers_telephone", type="string", length=32, nullable=false)
     */
    private $customers_telephone;

    /**
     * @var string $customers_email_address
     *
     * @ORM\Column(name="customers_email_address", type="string", length=96, nullable=false)
     */
    private $customers_email_address;

    /**
     * @var integer $customers_address_format_id
     *
     * @ORM\Column(name="customers_address_format_id", type="smallint", nullable=false)
     */
    private $customers_address_format_id;

    /**
     * @var string $delivery_name
     *
     * @ORM\Column(name="delivery_name", type="string", length=64, nullable=false)
     */
    private $delivery_name;

    /**
     * @var string $delivery_telephone
     *
     * @ORM\Column(name="delivery_telephone", type="string", length=32, nullable=true)
     */
    private $delivery_telephone;

   /**
     * @var string $delivery_company
     *
     * @ORM\Column(name="delivery_company", type="string", length=64, nullable=true)
     */
    private $delivery_company;

    /**
     * @var string $delivery_street_address
     *
     * @ORM\Column(name="delivery_street_address", type="string", length=64, nullable=false)
     */
    private $delivery_street_address;

    /**
     * @var string $delivery_suburb
     *
     * @ORM\Column(name="delivery_suburb", type="string", length=32, nullable=true)
     */
    private $delivery_suburb;

    /**
     * @var string $delivery_city
     *
     * @ORM\Column(name="delivery_city", type="string", length=32, nullable=false)
     */
    private $delivery_city;

    /**
     * @var string $delivery_postcode
     *
     * @ORM\Column(name="delivery_postcode", type="string", length=10, nullable=false)
     */
    private $delivery_postcode;

    /**
     * @var string $delivery_state
     *
     * @ORM\Column(name="delivery_state", type="string", length=32, nullable=true)
     */
    private $delivery_state;

    /**
     * @var string $delivery_country
     *
     * @ORM\Column(name="delivery_country", type="string", length=32, nullable=false)
     */
    private $delivery_country;

    /**
     * @var integer $delivery_address_format_id
     *
     * @ORM\Column(name="delivery_address_format_id", type="smallint", nullable=false)
     */
    private $delivery_address_format_id;

    /**
     * @var string $billing_name
     *
     * @ORM\Column(name="billing_name", type="string", length=64, nullable=false)
     */
    private $billing_name;

    /**
     * @var string $billing_company
     *
     * @ORM\Column(name="billing_company", type="string", length=64, nullable=true)
     */
    private $billing_company;

    /**
     * @var string $billing_street_address
     *
     * @ORM\Column(name="billing_street_address", type="string", length=64, nullable=false)
     */
    private $billing_street_address;

    /**
     * @var string $billing_suburb
     *
     * @ORM\Column(name="billing_suburb", type="string", length=32, nullable=true)
     */
    private $billing_suburb;

    /**
     * @var string $billing_city
     *
     * @ORM\Column(name="billing_city", type="string", length=32, nullable=false)
     */
    private $billing_city;

    /**
     * @var string $billing_postcode
     *
     * @ORM\Column(name="billing_postcode", type="string", length=10, nullable=false)
     */
    private $billing_postcode;

    /**
     * @var string $billing_state
     *
     * @ORM\Column(name="billing_state", type="string", length=32, nullable=true)
     */
    private $billing_state;

    /**
     * @var string $billing_country
     *
     * @ORM\Column(name="billing_country", type="string", length=32, nullable=false)
     */
    private $billing_country;

    /**
     * @var integer $billing_address_format_id
     *
     * @ORM\Column(name="billing_address_format_id", type="integer", nullable=false)
     */
    private $billing_address_format_id;

    /**
     * @var string $payment_method
     *
     * @ORM\Column(name="payment_method", type="string", length=128, nullable=false)
     */
    private $payment_method;

    /**
     * @var string $payment_module_code
     *
     * @ORM\Column(name="payment_module_code", type="string", length=32, nullable=false)
     */
    private $payment_module_code;

    /**
     * @var string $shipping_method
     *
     * @ORM\Column(name="shipping_method", type="text", nullable=false)
     */
    private $shipping_method;

    /**
     * @var string $shipping_module_code
     *
     * @ORM\Column(name="shipping_module_code", type="string", length=32, nullable=false)
     */
    private $shipping_module_code;

    /**
     * @var string $coupon_code
     *
     * @ORM\Column(name="coupon_code", type="string", length=32, nullable=false)
     */
    private $coupon_code;

    /**
     * @var string $cc_type
     *
     * @ORM\Column(name="cc_type", type="string", length=20, nullable=true)
     */
    private $cc_type;

    /**
     * @var string $cc_owner
     *
     * @ORM\Column(name="cc_owner", type="string", length=64, nullable=true)
     */
    private $cc_owner;

    /**
     * @var string $cc_number
     *
     * @ORM\Column(name="cc_number", type="string", length=32, nullable=true)
     */
    private $cc_number;

    /**
     * @var string $cc_expires
     *
     * @ORM\Column(name="cc_expires", type="string", length=4, nullable=true)
     */
    private $cc_expires;

    /**
     * @var string $cc_cvv
     *
     * @ORM\Column(name="cc_cvv", type="string", length=8, nullable=true)
     */
    private $cc_cvv;

    /**
     * @var \DateTime $last_modified
     *
     * @ORM\Column(name="last_modified", type="datetime", nullable=true)
     */
    private $last_modified;

    /**
     * @var \DateTime $orderDate
     *
     * @ORM\Column(name="date_purchased", type="datetime", nullable=true)
     */
    private $orderDate;

    /**
     * @var integer $orderStatusId
     *
     * @ORM\Column(name="orders_status", type="integer", nullable=false)
     */
    private $orderStatusId;

    /**
     * @var \DateTime $orders_date_finished
     *
     * @ORM\Column(name="orders_date_finished", type="datetime", nullable=true)
     */
    private $orders_date_finished;

    /**
     * @var string $currency
     *
     * @ORM\Column(name="currency", type="string", length=3, nullable=true)
     */
    private $currency;

    /**
     * @var float $currency_value
     *
     * @ORM\Column(name="currency_value", type="decimal", precision=14, scale=6, nullable=true)
     */
    private $currency_value;

    /**
     * @var float $total
     *
     * @ORM\Column(name="order_total", type="decimal", precision=14, scale=2, nullable=true)
     */
    private $total;

    /**
     * @var float $order_tax
     *
     * @ORM\Column(name="order_tax", type="decimal", precision=14, scale=2, nullable=true)
     */
    private $order_tax;

    /**
     * @var integer $paypal_ipn_id
     *
     * @ORM\Column(name="paypal_ipn_id", type="integer", nullable=false)
     */
    private $paypal_ipn_id;

    /**
     * @var string $ip_address
     *
     * @ORM\Column(name="ip_address", type="string", length=96, nullable=false)
     */
    private $ip_address;

    private $totalValue;
    private $account;
    private $shippingAddress;
    private $billingAddress;

    /**
     * Create order.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId(0);
        $this->accountId = 0;
        $this->reset();
    }

    /**
     * Reset.
     *
     * <p>This method may be called to reuse an existing instance.
     */
    public function reset()
    {
        $this->account = null;
        $this->shippingAddress = null;
        $this->billingAddress = null;
    }

    /**
     * Get the order id.
     *
     * @return int The order id.
     */
    public function getId()
    {
        return $this->orderId;
    }

    /**
     * Set the order id.
     *
     * @param int id The order id.
     */
    public function setId($id)
    {
        $this->orderId = $id;
    }

    /**
     * Set the account id.
     *
     * @param int accountId The account id.
     */
    public function setAccountId($accountId)
    {
        $this->accountId = $accountId;
    }

    /**
     * Get the account id.
     *
     * @return int The account id.
     */
    public function getAccountId()
    {
        return $this->accountId;
    }

    /**
     * Get the order status [id].
     *
     * @return int The order status [id].
     */
    public function getOrderStatusId()
    {
        return $this->orderStatusId;
    }

    /**
     * Get the order status name [read only]
     *
     * @return string The order status name.
     */
    public function getStatusName()
    {
        return $this->get('statusName');
    }

    /**
     * Set the order status [id].
     *
     * @param int statusId The order status [id].
     */
    public function setOrderStatusId($statusId)
    {
        $this->orderStatusId = $statusId;
    }

    /**
     * Get the order date.
     *
     * @return string The order date.
     */
    public function getOrderDate()
    {
        return $this->orderDate;
    }

    /**
     * Set the order date.
     *
     * @param string date The order date.
     */
    public function setOrderDate($date)
    {
        $this->orderDate = $date;
    }

    /**
     * Get the account for this order.
     *
     * <p><strong>NOTE: This contains the account information as of the time the order was placed. This might be
     * different from the current account data.</strong></p>
     *
     * @return ZenMagick\StoreBundle\Entity\Account\Account The account.
     */
    public function getAccount()
    {
        if (null === $this->account) {
            $this->account = Beans::getBean('ZenMagick\StoreBundle\Entity\Account\Account');
            $this->account->setAccountId($this->accountId);
            // orders has only name, not first/last...
            $this->account->setLastName($this->get('customers_name'));
            $this->account->setEmail($this->get('customers_email_address'));
            $this->account->setPhone($this->get('customers_telephone'));
        }

        return $this->account;
    }

    /**
     * Set the account for this order.
     *
     * <p><strong>NOTE: This contains the account information as of the time the order was placed. This might be
     * different from the current account data.</strong></p>
     *
     * @param ZenMagick\StoreBundle\Entity\Account\Account account The account.
     */
    public function setAccount($account)
    {
        $this->account = $account;
    }

    /**
     * Create address instance.
     */
    private function mkAddress($prefix)
    {
        $address = Beans::getBean('ZenMagick\StoreBundle\Entity\Address');
        $address->setAddressId(0);
        // orders has only name, not first/last...
        $address->setLastName($this->get($prefix.'_name'));
        $address->setCompanyName($this->get($prefix.'_company'));
        $address->setAddressLine1($this->get($prefix.'_street_address'));
        $address->setSuburb($this->get($prefix.'_suburb'));
        $address->setPostcode($this->get($prefix.'_postcode'));
        $address->setCity($this->get($prefix.'_city'));
        $address->setState($this->get($prefix.'_state'));
        $address->setCountry($this->container->get('countryService')->getCountryForName($this->get($prefix.'_country')));
        $address->setFormat($this->get($prefix.'_address_format_id'));

        return $address;
    }

    /**
     * Load address details.
     */
    private function loadAddress($address, $prefix)
    {
        if (null == $address) {
            return;
        }
        // orders has only name, not first/last...
        $this->set($prefix.'_name', $address->getFullName());
        $this->set($prefix.'_company', $address->getCompanyName());
        $this->set($prefix.'_street_address', $address->getAddress());
        $this->set($prefix.'_suburb', $address->getSuburb());
        $this->set($prefix.'_postcode', $address->getPostcode());
        $this->set($prefix.'_city', $address->getCity());
        $this->set($prefix.'_state', $address->getState());
        $this->set($prefix.'_country', $address->getCountry()->getName());
        $this->set($prefix.'_address_format_id', $address->getAddressFormatId());
    }

    /**
     * Get the shipping address.
     *
     * @return ZenMagick\StoreBundle\Entity\Address The shipping address or <code>null</code>.
     */
    public function getShippingAddress()
    {
        if (null === $this->shippingAddress) {
            $this->shippingAddress = $this->mkAddress('delivery');
        }

        return $this->shippingAddress;
    }

    /**
     * Set the shipping address.
     *
     * @param ZenMagick\StoreBundle\Entity\Address address The shipping address.
     */
    public function setShippingAddress($address)
    {
        $this->shippingAddress = $address;
        $this->loadAddress($address, 'delivery');
    }

    /**
     * Get the billing address.
     *
     * @return ZenMagick\StoreBundle\Entity\Address The billing address or <code>null</code>.
     */
    public function getBillingAddress()
    {
        if (null === $this->billingAddress) {
            $this->billingAddress = $this->mkAddress('billing');
        }

        return $this->billingAddress;
    }

    /**
     * Set the billing address.
     *
     * @param ZenMagick\StoreBundle\Entity\Address address The billing address.
     */
    public function setBillingAddress($address)
    {
        $this->billingAddress = $address;
        $this->loadAddress($address, 'billing');
    }

    /**
     * Checks if the order has a shipping address.
     *
     * @return boolean <code>true</code> if a shipping address exists, <code>false</code> if not.
     */
    public function hasShippingAddress()
    {
        $address = $this->getShippingAddress();

        return !(Toolbox::isEmpty($address->getLastName()) || Toolbox::isEmpty($address->getAddressLine1()));
    }

    /**
     * Get the order items.
     *
     * @return array A list of <code>ZenMagick\StoreBundle\Entity\Order\OrderItem<code> instances.
     */
    public function getOrderItems()
    {
        return $this->container->get('orderService')->getOrderItems($this->getId());
    }

    /**
     * Get the order status history.
     *
     * @param int languageId The language id.
     * @return array A list of previous order stati.
     */
    public function getOrderStatusHistory($languageId)
    {
        return $this->container->get('orderService')->getOrderStatusHistoryForId($this->getId(), $languageId);
    }

    /**
     * Get the order total.
     *
     * @return float The order total.
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Set the order total.
     *
     * @param float total The order total.
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }

    /**
     * Get all order total lines.
     *
     * @return array A list of <code>ZenMagick\StoreBundle\Entity\Order\OrderTotalLine</code> instances.
     */
    public function getOrderTotalLines()
    {
        return $this->container->get('orderService')->getOrderTotalLines($this->getId());
    }

    /**
     * Get order total lines for the given type.
     *
     * @param string type The total type (without the <em>ot_</em> prefix).
     * @param boolean force If set, a new order total will be created in case the order
     *  does not contain the one requested.
     * @return ZenMagick\StoreBundle\Entity\Order\OrderTotalLine A <code>ZenMagick\StoreBundle\Entity\Order\OrderTotalLine</code> or <code>null</code>.
     */
    public function getOrderTotalLinesForType($type, $force=false)
    {
        $rawtype = 'ot_'.$type;
        $lines = array();
        foreach ($this->getOrderTotalLines() as $total) {
            if ($rawtype == $total->getType()) {
                $lines[] = $total;
            }
        }

        if ($force && 0 == count($lines)) {
            $lines[] = new OrderTotalLine(ucwords($name), 0, 0, $rawtype);
        }

        return $lines;
    }

    /**
     * Get the total amount for a given total line type.
     *
     * @param string type The total type (without the <em>ot_</em> prefix).
     * @return float The total amount for all total lines with the given type.
     */
    public function getOrderTotalLineAmountForType($type)
    {
        $amount = 0;
        foreach ($this->getOrderTotalLinesForType($type) as $line) {
            $amount += $line->getAmount();
        }

        return $amount;
    }

    /**
     * Check if the order it pickup.
     *
     * @return boolean <code>true</code> if the order is store pickup, <code>false</code> if not.
     */
    public function isStorePickup()
    {
        $totals = $this->getOrderTotalLines();
        foreach ($totals as $total) {
            // AAAAAAAAAAAAAAAAAAAAAAAAAAARRRRRRRRRRRRRRRRRHHHHHHHHHHH
            if ('Store Pickup (Walk In):' == $total->getName()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the payment type.
     *
     * @return ZMPaymentType A payment type or <code>null</code> if N/A.
     */
    public function getPaymentType()
    {
        return $this->container->get('paymentTypeService')->getPaymentTypeForId($this->get('payment_module_code'));
    }

    /**
     * Get downloads.
     *
     * @return array List of <code>ZenMagick\StoreBundle\Entity\Order\Download</code> instances.
     */
    public function getDownloads()
    {
        return $this->container->get('orderService')->getDownloadsForOrderId($this->getId());
    }

}
