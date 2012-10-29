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
 * @ORM\Table(name="paypal_payment_status_history",
 *  indexes={
 *      @ORM\Index(name="idx_paypal_ipn_id_zen", columns={"paypal_ipn_id"}),
 *  })
 * @ORM\Entity
 */
class PaypalPaymentStatusHistory
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="payment_status_history_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer $ipnId
     *
     * @ORM\Column(name="paypal_ipn_id", type="integer", nullable=false)
     */
    private $ipnId;

    /**
     * @var string $txnId
     *
     * @ORM\Column(name="txn_id", type="string", length=64, nullable=false)
     */
    private $txnId;

    /**
     * @var string $parentTxnId
     *
     * @ORM\Column(name="parent_txn_id", type="string", length=64, nullable=false)
     */
    private $parentTxnId;

    /**
     * @var string $paymentStatus
     *
     * @ORM\Column(name="payment_status", type="string", length=17, nullable=false)
     */
    private $paymentStatus;

    /**
     * @var string $pendingReason
     *
     * @ORM\Column(name="pending_reason", type="string", length=14, nullable=true)
     */
    private $pendingReason;

    /**
     * @var \DateTime $dateAdded
     *
     * @ORM\Column(name="date_added", type="datetime", nullable=false)
     */
    private $dateAdded;

}
