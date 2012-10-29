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

/**
 * @ORM\Table(name="orders_status",
 *  indexes={
 *      @ORM\Index(name="idx_orders_status_zen", columns={"orders_status_name"}),
 *  })
 * @ORM\Entity
 */
class OrderStatus {
    /**
     * @var integer $orderStatusId
     *
     * @ORM\Column(name="orders_status_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $orderStatusId;

    /**
     * @var integer $languageId
     *
     * @ORM\Column(name="language_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $languageId;

    /**
     * @var string $statusName
     *
     * @ORM\Column(name="orders_status_name", type="string", length=32, nullable=false)
     */
    private $statusName;

    public function getId() {
        return $this->orderStatusId;
    }

    /**
     * Set orderStatusId
     *
     * @param  integer     $orderStatusId
     * @return OrderStatus
     */
    public function setOrderStatusId($orderStatusId) {
        $this->orderStatusId = $orderStatusId;

        return $this;
    }

    /**
     * Get orderStatusId
     *
     * @return integer
     */
    public function getOrderStatusId() {
        return $this->orderStatusId;
    }

    /**
     * Set languageId
     *
     * @param  integer     $languageId
     * @return OrderStatus
     */
    public function setLanguageId($languageId) {
        $this->languageId = $languageId;

        return $this;
    }

    /**
     * Get languageId
     *
     * @return integer
     */
    public function getLanguageId() {
        return $this->languageId;
    }

    /**
     * Set Name
     *
     * @param  string      $name
     * @return OrderStatus
     */
    public function setName($name) {
        $this->statusName = $name;

        return $this;
    }

    /**
     * Set statusName
     *
     * @param  string      $statusName
     * @return OrderStatus
     */
    public function setStatusName($statusName) {
        $this->statusName = $statusName;

        return $this;
    }

    /**
     * Get statusName
     *
     * @return string
     */
    public function getName() {
        return $this->statusName;
    }

    /**
     * Get statusName
     *
     * @return string
     */
    public function getStatusName() {
        return $this->statusName;
    }
}
