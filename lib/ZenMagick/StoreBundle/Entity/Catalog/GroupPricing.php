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

namespace ZenMagick\StoreBundle\Entity\Catalog;

use Doctrine\ORM\Mapping as ORM;

/**
 * ZenMagick\StoreBundle\Entity\GroupPricing
 *
 * @ORM\Table(name="group_pricing")
 * @ORM\Entity
 */
class GroupPricing {
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


}
