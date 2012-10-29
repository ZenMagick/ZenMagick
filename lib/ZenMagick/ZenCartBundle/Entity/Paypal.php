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

namespace ZenMagick\ZenCartBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="paypal",
 *   indexes={
 *     @ORM\Index(name="idx_order_id_zen", columns={"order_id"}),
 * })
 * @ORM\Entity
 */
class Paypal {
    /**
     * @var integer $ipnId
     *
     * @ORM\Column(name="paypal_ipn_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $ipnId;

    /**
     * @var string $txnId
     *
     * @ORM\Column(name="txn_id", type="string", length=20, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $txnId;

    /**
     * @var integer $orderId
     *
     * @ORM\Column(name="order_id", type="integer", nullable=false)
     */
    private $orderId;

    /**
     * @var string $txnType
     *
     * @ORM\Column(name="txn_type", type="string", length=40, nullable=false)
     */
    private $txnType;

    /**
     * @var string $moduleName
     *
     * @ORM\Column(name="module_name", type="string", length=40, nullable=false)
     */
    private $moduleName;

    /**
     * @var string $moduleMode
     *
     * @ORM\Column(name="module_mode", type="string", length=40, nullable=false)
     */
    private $moduleMode;

    /**
     * @var string $reasonCode
     *
     * @ORM\Column(name="reason_code", type="string", length=40, nullable=true)
     */
    private $reasonCode;

    /**
     * @var string $paymentType
     *
     * @ORM\Column(name="payment_type", type="string", length=40, nullable=false)
     */
    private $paymentType;

    /**
     * @var string $paymentStatus
     *
     * @ORM\Column(name="payment_status", type="string", length=32, nullable=false)
     */
    private $paymentStatus;

    /**
     * @var string $pendingReason
     *
     * @ORM\Column(name="pending_reason", type="string", length=32, nullable=true)
     */
    private $pendingReason;

    /**
     * @var string $invoice
     *
     * @ORM\Column(name="invoice", type="string", length=128, nullable=true)
     */
    private $invoice;

    /**
     * @var string $mcCurrency
     *
     * @ORM\Column(name="mc_currency", type="string", length=3, nullable=false)
     */
    private $mcCurrency;

    /**
     * @var string $firstName
     *
     * @ORM\Column(name="first_name", type="string", length=32, nullable=false)
     */
    private $firstName;

    /**
     * @var string $lastName
     *
     * @ORM\Column(name="last_name", type="string", length=32, nullable=false)
     */
    private $lastName;

    /**
     * @var string $payerBusinessName
     *
     * @ORM\Column(name="payer_business_name", type="string", length=128, nullable=true)
     */
    private $payerBusinessName;

    /**
     * @var string $addressName
     *
     * @ORM\Column(name="address_name", type="string", length=64, nullable=true)
     */
    private $addressName;

    /**
     * @var string $addressStreet
     *
     * @ORM\Column(name="address_street", type="string", length=254, nullable=true)
     */
    private $addressStreet;

    /**
     * @var string $addressCity
     *
     * @ORM\Column(name="address_city", type="string", length=120, nullable=true)
     */
    private $addressCity;

    /**
     * @var string $addressState
     *
     * @ORM\Column(name="address_state", type="string", length=120, nullable=true)
     */
    private $addressState;

    /**
     * @var string $addressZip
     *
     * @ORM\Column(name="address_zip", type="string", length=10, nullable=true)
     */
    private $addressZip;

    /**
     * @var string $addressCountry
     *
     * @ORM\Column(name="address_country", type="string", length=64, nullable=true)
     */
    private $addressCountry;

    /**
     * @var string $addressStatus
     *
     * @ORM\Column(name="address_status", type="string", length=11, nullable=true)
     */
    private $addressStatus;

    /**
     * @var string $payerEmail
     *
     * @ORM\Column(name="payer_email", type="string", length=128, nullable=false)
     */
    private $payerEmail;

    /**
     * @var string $payerId
     *
     * @ORM\Column(name="payer_id", type="string", length=32, nullable=false)
     */
    private $payerId;

    /**
     * @var string $payerStatus
     *
     * @ORM\Column(name="payer_status", type="string", length=10, nullable=false)
     */
    private $payerStatus;

    /**
     * @var \DateTime $paymentDate
     *
     * @ORM\Column(name="payment_date", type="datetime", nullable=false)
     */
    private $paymentDate;

    /**
     * @var string $business
     *
     * @ORM\Column(name="business", type="string", length=128, nullable=false)
     */
    private $business;

    /**
     * @var string $receiverEmail
     *
     * @ORM\Column(name="receiver_email", type="string", length=128, nullable=false)
     */
    private $receiverEmail;

    /**
     * @var string $receiverId
     *
     * @ORM\Column(name="receiver_id", type="string", length=32, nullable=false)
     */
    private $receiverId;

    /**
     * @var string $parentTxnId
     *
     * @ORM\Column(name="parent_txn_id", type="string", length=20, nullable=true)
     */
    private $parentTxnId;

    /**
     * @var boolean $numCartItems
     *
     * @ORM\Column(name="num_cart_items", type="smallint", nullable=false)
     */
    private $numCartItems;

    /**
     * @var float $mcGross
     *
     * @ORM\Column(name="mc_gross", type="decimal", precision=7, scale=2, nullable=false)
     */
    private $mcGross;

    /**
     * @var float $mcFee
     *
     * @ORM\Column(name="mc_fee", type="decimal", precision=7, scale=2, nullable=false)
     */
    private $mcFee;

    /**
     * @var float $paymentGross
     *
     * @ORM\Column(name="payment_gross", type="decimal", precision=7, scale=2, nullable=true)
     */
    private $paymentGross;

    /**
     * @var float $paymentFee
     *
     * @ORM\Column(name="payment_fee", type="decimal", precision=7, scale=2, nullable=true)
     */
    private $paymentFee;

    /**
     * @var float $settleAmount
     *
     * @ORM\Column(name="settle_amount", type="decimal", precision=7, scale=2, nullable=true)
     */
    private $settleAmount;

    /**
     * @var string $settleCurrency
     *
     * @ORM\Column(name="settle_currency", type="string", length=3, nullable=true)
     */
    private $settleCurrency;

    /**
     * @var float $exchangeRate
     *
     * @ORM\Column(name="exchange_rate", type="decimal", precision=4, scale=2, nullable=true)
     */
    private $exchangeRate;

    /**
     * @var float $notifyVersion
     *
     * @ORM\Column(name="notify_version", type="string", length=6, nullable=false)
     */
    private $notifyVersion;

    /**
     * @var string $verifySign
     *
     * @ORM\Column(name="verify_sign", type="string", length=128, nullable=false)
     */
    private $verifySign;

    /**
     * @var \DateTime $lastModified
     *
     * @ORM\Column(name="last_modified", type="datetime", nullable=false)
     */
    private $lastModified;

    /**
     * @var \DateTime $dateAdded
     *
     * @ORM\Column(name="date_added", type="datetime", nullable=false)
     */
    private $dateAdded;

    /**
     * @var string $memo
     *
     * @ORM\Column(name="memo", type="text", nullable=true)
     */
    private $memo;

}
