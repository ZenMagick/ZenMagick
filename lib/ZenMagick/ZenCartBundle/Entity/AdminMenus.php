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

namespace ZenMagick\ZenCartBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ZenMagick\ZenCartBundle\Entity\AdminMenus
 *
 * @ORM\Table(name="admin_menus")
 * @ORM\Entity
 */
class AdminMenus
{
    /**
     * @var string $menuKey
     *
     * @ORM\Column(name="menu_key", type="string", length=32, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $menuKey;

    /**
     * @var string $languageKey
     *
     * @ORM\Column(name="language_key", type="string", length=255, nullable=false)
     */
    private $languageKey;

    /**
     * @var integer $sortOrder
     *
     * @ORM\Column(name="sort_order", type="integer", nullable=false)
     */
    private $sortOrder;



    /**
     * Get menuKey
     *
     * @return string
     */
    public function getMenuKey()
    {
        return $this->menuKey;
    }

    /**
     * Set languageKey
     *
     * @param string $languageKey
     * @return AdminMenus
     */
    public function setLanguageKey($languageKey)
    {
        $this->languageKey = $languageKey;

        return $this;
    }

    /**
     * Get languageKey
     *
     * @return string
     */
    public function getLanguageKey()
    {
        return $this->languageKey;
    }

    /**
     * Set sortOrder
     *
     * @param integer $sortOrder
     * @return AdminMenus
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
}
