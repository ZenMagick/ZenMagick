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

namespace ZenMagick\ZenCartBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="authorizenet")
 * @ORM\Entity
 */
class Authorizenet
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer $customerId
     *
     * @ORM\Column(name="customer_id", type="integer", nullable=false)
     */
    private $customerId;

    /**
     * @var integer $orderId
     *
     * @ORM\Column(name="order_id", type="integer", nullable=false)
     */
    private $orderId;

    /**
     * @var integer $responseCode
     *
     * @ORM\Column(name="response_code", type="integer", nullable=false)
     */
    private $responseCode;

    /**
     * @var string $responseText
     *
     * @ORM\Column(name="response_text", type="string", length=255, nullable=false)
     */
    private $responseText;

    /**
     * @var string $authorizationType
     *
     * @ORM\Column(name="authorization_type", type="string", length=50, nullable=false)
     */
    private $authorizationType;

    /**
     * @var integer $transactionId
     *
     * @ORM\Column(name="transaction_id", type="bigint", nullable=true)
     */
    private $transactionId;

    /**
     * @var string $sent
     *
     * @ORM\Column(name="sent", type="text", nullable=false)
     */
    private $sent;

    /**
     * @var string $received
     *
     * @ORM\Column(name="received", type="text", nullable=false)
     */
    private $received;

    /**
     * @var string $time
     *
     * @ORM\Column(name="time", type="string", length=50, nullable=false)
     */
    private $time;

    /**
     * @var string $sessionId
     *
     * @ORM\Column(name="session_id", type="string", length=255, nullable=false)
     */
    private $sessionId;


}
