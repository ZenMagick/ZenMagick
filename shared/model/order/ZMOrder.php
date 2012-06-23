<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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

use zenmagick\base\Runtime;
use zenmagick\base\Toolbox;
use zenmagick\base\ZMObject;

/**
 * A single order.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.model.order
 */
class ZMOrder extends ZMObject {
    private $accountId_;
    private $orderStatusId_;
    private $orderDate_;
    private $totalValue_;
    private $account_;
    private $shippingAddress_;
    private $billingAddress_;
    private $total_;


    /**
     * Create order.
     */
    public function __construct() {
        parent::__construct();
        $this->setId(0);
        $this->accountId_ = 0;
        $this->reset();
    }


    /**
     * Reset.
     *
     * <p>This method may be called to reuse an existing instance.
     */
    public function reset() {
        $this->account_ = null;
        $this->shippingAddress_ = null;
        $this->billingAddress_ = null;
    }

    /**
     * Get the order id.
     *
     * @return int The order id.
     */
    public function getId() { return $this->get('orderId'); }

    /**
     * Set the order id.
     *
     * @param int id The order id.
     */
    public function setId($id) { $this->set('orderId', $id); }

    /**
     * Set the account id.
     *
     * @param int accountId The account id.
     */
    public function setAccountId($accountId) { $this->accountId_ = $accountId; }

    /**
     * Get the account id.
     *
     * @return int The account id.
     */
    public function getAccountId() { return $this->accountId_; }

    /**
     * Get the order status [id].
     *
     * @return int The order status [id].
     */
    public function getOrderStatusId() { return $this->orderStatusId_; }

    /**
     * Get the order status name [read only]
     *
     * @return string The order status name.
     */
    public function getStatusName() { return $this->get('statusName'); }

    /**
     * Set the order status [id].
     *
     * @param int statusId The order status [id].
     */
    public function setOrderStatusId($statusId) { $this->orderStatusId_ = $statusId; }

    /**
     * Get the order date.
     *
     * @return string The order date.
     */
    public function getOrderDate() { return $this->orderDate_; }

    /**
     * Set the order date.
     *
     * @param string date The order date.
     */
    public function setOrderDate($date) { $this->orderDate_ = $date; }

    /**
     * Get the account for this order.
     *
     * <p><strong>NOTE: This contains the account information as of the time the order was placed. This might be
     * different from the current account data.</strong></p>
     *
     * @return ZMAccount The account.
     */
    public function getAccount() {
        if (null === $this->account_) {
            $this->account_ = Runtime::getContainer()->get("ZMAccount");
            $this->account_->setAccountId($this->accountId_);
            // orders has only name, not first/last...
            $this->account_->setLastName($this->get('customers_name'));
            $this->account_->setEmail($this->get('customers_email_address'));
            $this->account_->setPhone($this->get('customers_telephone'));
        }

        return $this->account_;
    }

    /**
     * Set the account for this order.
     *
     * <p><strong>NOTE: This contains the account information as of the time the order was placed. This might be
     * different from the current account data.</strong></p>
     *
     * @param ZMAccount account The account.
     */
    public function setAccount($account) { $this->account_ = $account; }

    /**
     * Create address instance.
     */
    private function mkAddress($prefix) {
        $address = Runtime::getContainer()->get("ZMAddress");
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
    private function loadAddress($address, $prefix) {
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
     * @return ZMAddress The shipping address or <code>null</code>.
     */
    public function getShippingAddress() {
        if (null === $this->shippingAddress_) {
            $this->shippingAddress_ = $this->mkAddress('delivery');
        }
        return $this->shippingAddress_;
    }

    /**
     * Set the shipping address.
     *
     * @param ZMAddress address The shipping address.
     */
    public function setShippingAddress($address) {
        $this->shippingAddress_ = $address;
        $this->loadAddress($address, 'delivery');
    }

    /**
     * Get the billing address.
     *
     * @return ZMAddress The billing address or <code>null</code>.
     */
    public function getBillingAddress() {
        if (null === $this->billingAddress_) {
            $this->billingAddress_ = $this->mkAddress('billing');
        }
        return $this->billingAddress_;
    }

    /**
     * Set the billing address.
     *
     * @param ZMAddress address The billing address.
     */
    public function setBillingAddress($address) {
        $this->billingAddress_ = $address;
        $this->loadAddress($address, 'billing');
    }

    /**
     * Checks if the order has a shipping address.
     *
     * @return boolean <code>true</code> if a shipping address exists, <code>false</code> if not.
     */
    public function hasShippingAddress() {
        $address = $this->getShippingAddress();
        return !(Toolbox::isEmpty($address->getLastName()) || Toolbox::isEmpty($address->getAddressLine1()));
    }

    /**
     * Get the order items.
     *
     * @return array A list of <code>ZMOrderItem<code> instances.
     */
    public function getOrderItems() {
        return $this->container->get('orderService')->getOrderItems($this->getId());
    }

    /**
     * Get the order status history.
     *
     * @param int languageId The language id.
     * @return array A list of previous order stati.
     */
    public function getOrderStatusHistory($languageId) {
        return $this->container->get('orderService')->getOrderStatusHistoryForId($this->getId(), $languageId);
    }

    /**
     * Get the order total.
     *
     * @return float The order total.
     */
    public function getTotal() { return $this->total_; }

    /**
     * Set the order total.
     *
     * @param float total The order total.
     */
    public function setTotal($total) { $this->total_ = $total; }

    /**
     * Get all order total lines.
     *
     * @return array A list of <code>ZMOrderTotalLine</code> instances.
     */
    public function getOrderTotalLines() { return $this->container->get('orderService')->getOrderTotalLines($this->getId()); }

    /**
     * Get order total lines for the given type.
     *
     * @param string type The total type (without the <em>ot_</em> prefix).
     * @param boolean force If set, a new order total will be created in case the order
     *  does not contain the one requested.
     * @return ZMOrderTotalLine A <code>ZMOrderTotalLine</code> or <code>null</code>.
     */
    public function getOrderTotalLinesForType($type, $force=false) {
        $rawtype = 'ot_'.$type;
        $lines = array();
        foreach ($this->getOrderTotalLines() as $total) {
            if ($rawtype == $total->getType()) {
                $lines[] = $total;
            }
        }

        if ($force && 0 == count($lines)) {
            $lines[] = new ZMOrderTotalLine(ucwords($name), 0, 0, $rawtype);
        }

        return $lines;
    }

    /**
     * Get the total amount for a given total line type.
     *
     * @param string type The total type (without the <em>ot_</em> prefix).
     * @return float The total amount for all total lines with the given type.
     */
    public function getOrderTotalLineAmountForType($type) {
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
    function isStorePickup() {
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
    public function getPaymentType() {
        return $this->container->get('paymentTypeService')->getPaymentTypeForId($this->get('payment_module_code'));
    }

    /**
     * Get downloads.
     *
     * @return array List of <code>ZMDownload</code> instances.
     */
    public function getDownloads() {
        return $this->container->get('orderService')->getDownloadsForOrderId($this->getId());
    }

}
