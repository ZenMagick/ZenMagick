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
use ZenMagick\Base\ZMObject;

/**
 * A order total line.
 *
 * @ORM\Table(name="orders_total",
 *  indexes={
 *      @ORM\Index(name="idx_ot_orders_id_zen", columns={"orders_id"}),
 *      @ORM\Index(name="idx_ot_class_zen", columns={"class"}),
 *  })
 * @ORM\Entity
 * @author DerManoMann
 */
class OrderTotalLine extends ZMObject
{
    /**
     * @var integer $ordersTotalId
     *
     * @ORM\Column(name="orders_total_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $ordersTotalId;

    /**
     * @var integer $orderId
     *
     * @ORM\Column(name="orders_id", type="integer", nullable=false)
     */
    private $orderId;

    /**
     * @var string $title
     *
     * @ORM\Column(name="title", type="text", nullable=false)
     */
    private $title;

    /**
     * @var string $value
     *
     * @ORM\Column(name="text", type="string", length=255, nullable=false)
     */
    private $value;

    /**
     * @var float $amount
     *
     * @ORM\Column(name="value", type="decimal", precision=15, scale=4, nullable=false)
     */
    private $amount;

    /**
     * @var string $type
     *
     * @ORM\Column(name="class", type="string", length=32, nullable=false)
     */
    private $type;

    /**
     * @var integer $sortOrder
     *
     * @ORM\Column(name="sort_order", type="integer", nullable=false)
     */
    private $sortOrder;

    /**
     * Create new total line.
     *
     * @param string name The total name.
     * @param string value The total value.
     * @param float amount The total amount.
     * @param string type The total type.
     */
    public function __construct($name=null, $value=null, $amount=0, $type=null)
    {
        parent::__construct();
        $this->setId(0);
        $this->name = $name;
        $this->value = $value;
        $this->amount = $amount;
        $this->type = $type;
        $this->sortOrder = 0;
    }

    /**
     * Get the order total id.
     *
     * @return int The order total id.
     */
    public function getId()
    {
        return $this->orderTotalId;
    }

    /**
     * Get the order  id.
     *
     * @return int The order id.
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * Get the order total name.
     *
     * @return string The order total name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the order total value.
     *
     * @return string The formatted order total value.
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get the order total amount.
     *
     * @return float The order total amount.
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Get the order total type.
     *
     * @return string The order total type.
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get the sort order.
     *
     * @return integer sort order.
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * Set the order total id.
     *
     * @param int id The order total id.
     */
    public function setId($id)
    {
        return $this->orderTotalId = $id;

        return $this;
    }

    /**
     * Set the order id.
     *
     * @param int id The order id.
     */
    public function setOrderId($orderId)
    {
        return $this->orderId = $orderId;

        return $this;
    }

    /**
     * Set the order total name.
     *
     * @oparam string name The order total name.
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set the order total value.
     *
     * @param string value The formatted order total value.
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Set the order total amount.
     *
     * @param float amount The order total amount.
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Set the order total type.
     *
     * @param string type The order total type.
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Set the sort order.
     *
     * @param integer sortOrder The sort order.
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

}
