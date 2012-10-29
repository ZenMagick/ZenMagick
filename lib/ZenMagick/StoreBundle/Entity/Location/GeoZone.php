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

namespace ZenMagick\StoreBundle\Entity\Location;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="geo_zones")
 * @ORM\Entity
 */
class GeoZone {
    /**
     * @var integer $geoZoneId
     *
     * @ORM\Column(name="geo_zone_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $geoZoneId;

    /**
     * @var string $name
     *
     * @ORM\Column(name="geo_zone_name", type="string", length=32, nullable=false)
     */
    private $name;

    /**
     * @var string $description
     *
     * @ORM\Column(name="geo_zone_description", type="string", length=255, nullable=false)
     */
    private $description;

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
