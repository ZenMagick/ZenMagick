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
 * ZenMagick\ZenCartBundle\Entity\AdminPagesToProfiles
 *
 * @ORM\Table(name="admin_pages_to_profiles",
 *  uniqueConstraints={@ORM\UniqueConstraint(name="page_profile",columns={"page_key", "profile_id"})}
 * )
 * @ORM\Entity
 */
class AdminPagesToProfiles
{
    /**
     * @var integer $profileId
     *
     * @ORM\Column(name="profile_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $profileId;

    /**
     * @var string $pageKey
     *
     * @ORM\Column(name="page_key", type="string", length=32, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $pageKey;



    /**
     * Set profileId
     *
     * @param integer $profileId
     * @return AdminPagesToProfiles
     */
    public function setProfileId($profileId)
    {
        $this->profileId = $profileId;

        return $this;
    }

    /**
     * Get profileId
     *
     * @return integer
     */
    public function getProfileId()
    {
        return $this->profileId;
    }

    /**
     * Set pageKey
     *
     * @param string $pageKey
     * @return AdminPagesToProfiles
     */
    public function setPageKey($pageKey)
    {
        $this->pageKey = $pageKey;

        return $this;
    }

    /**
     * Get pageKey
     *
     * @return string
     */
    public function getPageKey()
    {
        return $this->pageKey;
    }
}
