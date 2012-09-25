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
 * @ORM\Table(name="zones_to_geo_zones",
 *    indexes={
 *     @ORM\Index(name="idx_zones_zen", columns={"geo_zone_id", "zone_country_id", "zone_id"})
 * })
 * @ORM\Entity
 */
class ZoneToGeoZone {
    /**
     * @var integer $zoneToGeoZoneId
     *
     * @ORM\Column(name="association_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $zoneToGeoZoneId;

    /**
     * @var integer $countryId
     *
     * @ORM\Column(name="zone_country_id", type="integer", nullable=false)
     */
    private $countryId;

    /**
     * @var integer $zoneId
     *
     * @ORM\Column(name="zone_id", type="integer", nullable=true)
     */
    private $zoneId;

    /**
     * @var integer $geoZoneId
     *
     * @ORM\Column(name="geo_zone_id", type="integer", nullable=true)
     */
    private $geoZoneId;

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
