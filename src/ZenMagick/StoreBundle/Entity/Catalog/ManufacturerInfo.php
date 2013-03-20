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
 * @ORM\Table(name="manufacturers_info")
 * @ORM\Entity
 */
class ManufacturerInfo
{
    /**
     * @var integer $manufacturerId
     *
     * @ORM\Column(name="manufacturers_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $manufacturerId;

    /**
     * @var integer $languageId
     *
     * @ORM\Column(name="languages_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $languageId;

    /**
     * @var string $url
     *
     * @ORM\Column(name="manufacturers_url", type="string", length=255, nullable=false)
     */
    private $url;

    /**
     * @var integer $clickCount
     *
     * @ORM\Column(name="url_clicked", type="integer", nullable=false)
     */
    private $clickCount;

    /**
     * @var \DateTime $lastClick
     *
     * @ORM\Column(name="date_last_click", type="datetime", nullable=true)
     */
    private $lastClick;

}
