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

namespace ZenMagick\StoreBundle\Entity\Catalog;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="categories_description",
 *  indexes={
 *      @ORM\Index(name="idx_categories_name_zen", columns={"categories_name"}),
 * })
 * @ORM\Entity
 */
class CategoryDescriptions
{
    /**
     * @var object $category
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="ZenMagick\StoreBundle\Entity\Catalog\Category", inversedBy="descriptions")
     * @ORM\JoinColumn(name="categories_id", referencedColumnName="categories_id")
     */
    private $category;

    /**
     * @var integer $languageId
     *
     * @ORM\Column(name="language_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $languageId;

    /**
     * @var string $name
     *
     * @ORM\Column(name="categories_name", type="string", length=128, nullable=false)
     */
    private $name;

    /**
     * @var text $description
     *
     * @ORM\Column(name="categories_description", type="text", nullable=false)
     */
    private $description;


    /**
     * Set languageId
     *
     * @param integer $languageId
     * @return CategoryDescriptions
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
     * Set name
     *
     * @param string $name
     * @return CategoryDescriptions
     */
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return CategoryDescriptions
     */
    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * Set category
     *
     * @param ZenMagick\StoreBundle\Entity\Catalog\Category $category
     * @return CategoryDescriptions
     */
    public function setCategory(Category $category) {
        $this->category = $category;
        return $this;
    }

    /**
     * Get category
     *
     * @return ZenMagick\StoreBundle\Entity\Catalog\Category
     */
    public function getCategory() {
        return $this->category;
    }
}
