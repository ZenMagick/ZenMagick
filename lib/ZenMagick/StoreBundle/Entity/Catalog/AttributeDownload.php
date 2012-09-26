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
 * @ORM\Table(name="products_attributes_download")
 * @ORM\Entity
 */
class AttributeDownload {
    /**
     * @var integer $id
     *
     * @ORM\Column(name="products_attributes_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $id;

    /**
     * @var string $filename
     *
     * @ORM\Column(name="products_attributes_filename", type="string", length=255, nullable=false)
     */
    private $filename;

    /**
     * @var integer $maxDays
     *
     * @ORM\Column(name="products_attributes_maxdays", type="smallint", nullable=true)
     */
    private $maxDays;

    /**
     * @var integer $maxCount
     *
     * @ORM\Column(name="products_attributes_maxcount", type="integer", nullable=true)
     */
    private $maxCount;


}
