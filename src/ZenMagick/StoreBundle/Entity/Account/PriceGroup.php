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

namespace ZenMagick\StoreBundle\Entity\Account;

use Doctrine\ORM\Mapping as ORM;
use ZenMagick\Base\ZMObject;

/**
 * A price group.
 *
 * @ORM\Table(name="group_pricing")
 * @ORM\Entity
 * @author DerManoMann
 */
class PriceGroup extends ZMObject
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="group_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string $name
     *
     * @ORM\Column(name="group_name", type="string", length=32, nullable=false)
     */
    private $name;

    /**
     * @var float $discount
     *
     * @ORM\Column(name="group_percentage", type="decimal", precision=5, scale=2, nullable=false)
     */
    private $discount;

    /**
     * @var \DateTime $lastModified
     *
     * @ORM\Column(name="last_modified", type="datetime", nullable=true)
     */
    private $lastModified;

    /**
     * @var \DateTime $dateAdded
     *
     * @ORM\Column(name="date_added", type="datetime", nullable=false)
     */
    private $dateAdded;

    /**
     * Create new instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->id = 0;
        $this->name = 0;
        $this->discount = 0;
        $this->dateAdded = null;
        $this->lastModified = null;
    }

    /**
     * Get the group id.
     *
     * @return int The group id.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the name.
     *
     * @return string The group name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the discount.
     *
     * @return float The discount.
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * Get the date the group was added.
     *
     * @return string The added date.
     */
    public function getDateAdded()
    {
        return $this->dateAdded;
    }

    /**
     * Get the last modified date.
     *
     * @return string The last modified date.
     */
    public function getLastModified()
    {
        return $this->lastModified;
    }

    /**
     * Set the group id.
     *
     * @param int id The group id.
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set the name.
     *
     * @param string name The group name.
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set the discount.
     *
     * @param float discount The discount.
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;

        return $this;
    }

    /**
     * Set the date the group was added.
     *
     * @param string dateAdded The added date.
     */
    public function setDateAdded($dateAdded)
    {
        $this->dateAdded = $dateAdded;

        return $this;
    }

    /**
     * Set the last modified date.
     *
     * @param string lastModified The last modified date.
     */
    public function setLastModified($lastModified)
    {
        $this->lastModified = $lastModified;

        return $this;
    }

}
