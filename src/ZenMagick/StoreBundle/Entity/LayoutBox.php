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

namespace ZenMagick\StoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="layout_boxes",
 *  uniqueConstraints={
 *     @ORM\UniqueConstraint(name="idx_template_box", columns={"layout_template","layout_box_name"}),
 *  },
 *   indexes={
 *     @ORM\Index(name="idx_layout_box_status_zen", columns={"layout_box_status"}),
 *     @ORM\Index(name="idx_layout_box_sort_order_zen", columns={"layout_box_sort_order"})
 * })
 * @ORM\Entity
 */
class LayoutBox
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="layout_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string $themeId
     *
     * @ORM\Column(name="layout_template", type="string", length=64, nullable=false)
     */
    private $themeId;

    /**
     * @var string $name
     *
     * @ORM\Column(name="layout_box_name", type="string", length=64, nullable=false)
     */
    private $name;

    /**
     * @var boolean $status
     *
     * @ORM\Column(name="layout_box_status", type="boolean", nullable=false)
     */
    private $status;

    /**
     * @var boolean $location
     *
     * @ORM\Column(name="layout_box_location", type="boolean", nullable=false)
     */
    private $location;

    /**
     * @var integer $sortOrder
     *
     * @ORM\Column(name="layout_box_sort_order", type="integer", nullable=false)
     */
    private $sortOrder;

    /**
     * @var integer $singleSortOrder
     *
     * @ORM\Column(name="layout_box_sort_order_single", type="integer", nullable=false)
     */
    private $singleSortOrder;

    /**
     * @var boolean $singleStatus
     *
     * @ORM\Column(name="layout_box_status_single", type="boolean", nullable=false)
     */
    private $singleStatus;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set themeId
     *
     * @param  string    $themeId
     * @return LayoutBox
     */
    public function setThemeId($themeId)
    {
        $this->themeId = $themeId;

        return $this;
    }

    /**
     * Get themeId
     *
     * @return string
     */
    public function getThemeId()
    {
        return $this->themeId;
    }

    /**
     * Set name
     *
     * @param  string    $name
     * @return LayoutBox
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set status
     *
     * @param  boolean   $status
     * @return LayoutBox
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return boolean
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set location
     *
     * @param  boolean   $location
     * @return LayoutBox
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return boolean
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set sortOrder
     *
     * @param  integer   $sortOrder
     * @return LayoutBox
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    /**
     * Get sortOrder
     *
     * @return integer
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * Set singleSortOrder
     *
     * @param  integer   $singleSortOrder
     * @return LayoutBox
     */
    public function setSingleSortOrder($singleSortOrder)
    {
        $this->singleSortOrder = $singleSortOrder;

        return $this;
    }

    /**
     * Get singleSortOrder
     *
     * @return integer
     */
    public function getSingleSortOrder()
    {
        return $this->singleSortOrder;
    }

    /**
     * Set singleStatus
     *
     * @param  boolean   $singleStatus
     * @return LayoutBox
     */
    public function setSingleStatus($singleStatus)
    {
        $this->singleStatus = $singleStatus;

        return $this;
    }

    /**
     * Get singleStatus
     *
     * @return boolean
     */
    public function getSingleStatus()
    {
        return $this->singleStatus;
    }
}
