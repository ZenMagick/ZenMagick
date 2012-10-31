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
 *
 * @ORM\Table(name="products_options_values",
 *   indexes={
 *     @ORM\Index(name="idx_products_options_values_name_zen", columns={"products_options_values_name"}),
 *     @ORM\Index(name="idx_products_options_values_sort_order_zen", columns={"products_options_values_sort_order"}),
 * })
 * @ORM\Entity
 */
class ProductOptionValue
{
    /**
     * @var integer $optionValueId
     *
     * @ORM\Column(name="products_options_values_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $optionValueId;

    /**
     * @var integer $languageId
     *
     * @ORM\Column(name="language_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $languageId;

    /**
     * @var string $optionValueName
     *
     * @ORM\Column(name="products_options_values_name", type="string", length=255, nullable=false)
     */
    private $optionsValueName;

    /**
     * @var integer $optionValueSortOrder
     *
     * @ORM\Column(name="products_options_values_sort_order", type="integer", nullable=false)
     */
    private $optionValueSortOrder;

}
