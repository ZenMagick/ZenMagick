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
 * ZenMagick\ZenCartBundle\Entity\AdminPages
 *
 * @ORM\Table(name="admin_pages")
 * @ORM\Entity
 */
class AdminPages
{
    /**
     * @var string $pageKey
     *
     * @ORM\Column(name="page_key", type="string", length=32, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $pageKey;

    /**
     * @var string $languageKey
     *
     * @ORM\Column(name="language_key", type="string", length=255, nullable=false)
     */
    private $languageKey;

    /**
     * @var string $mainPage
     *
     * @ORM\Column(name="main_page", type="string", length=64, nullable=false)
     */
    private $mainPage;

    /**
     * @var string $pageParams
     *
     * @ORM\Column(name="page_params", type="string", length=64, nullable=false)
     */
    private $pageParams;

    /**
     * @var string $menuKey
     *
     * @ORM\Column(name="menu_key", type="string", length=32, nullable=false)
     */
    private $menuKey;

    /**
     * @var string $displayOnMenu
     *
     * @ORM\Column(name="display_on_menu", type="string", length=1, nullable=false)
     */
    private $displayOnMenu;

    /**
     * @var integer $sortOrder
     *
     * @ORM\Column(name="sort_order", type="integer", nullable=false)
     */
    private $sortOrder;

    /**
     * Get pageKey
     *
     * @return string
     */
    public function getPageKey()
    {
        return $this->pageKey;
    }

    /**
     * Set languageKey
     *
     * @param string $languageKey
     * @return AdminPages
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
     * Set mainPage
     *
     * @param string $mainPage
     * @return AdminPages
     */
    public function setMainPage($mainPage)
    {
        $this->mainPage = $mainPage;

        return $this;
    }

    /**
     * Get mainPage
     *
     * @return string
     */
    public function getMainPage()
    {
        return $this->mainPage;
    }

    /**
     * Set pageParams
     *
     * @param string $pageParams
     * @return AdminPages
     */
    public function setPageParams($pageParams)
    {
        $this->pageParams = $pageParams;

        return $this;
    }

    /**
     * Get pageParams
     *
     * @return string
     */
    public function getPageParams()
    {
        return $this->pageParams;
    }

    /**
     * Set menuKey
     *
     * @param string $menuKey
     * @return AdminPages
     */
    public function setMenuKey($menuKey)
    {
        $this->menuKey = $menuKey;

        return $this;
    }

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
     * Set displayOnMenu
     *
     * @param string $displayOnMenu
     * @return AdminPages
     */
    public function setDisplayOnMenu($displayOnMenu)
    {
        $this->displayOnMenu = $displayOnMenu;

        return $this;
    }

    /**
     * Get displayOnMenu
     *
     * @return string
     */
    public function getDisplayOnMenu()
    {
        return $this->displayOnMenu;
    }

    /**
     * Set sortOrder
     *
     * @param integer $sortOrder
     * @return AdminPages
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
