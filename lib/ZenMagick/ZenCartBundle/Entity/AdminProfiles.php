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
 * ZenMagick\ZenCartBundle\Entity\AdminProfiles
 *
 * @ORM\Table(name="admin_profiles")
 * @ORM\Entity
 */
class AdminProfiles
{
    /**
     * @var integer $profileId
     *
     * @ORM\Column(name="profile_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $profileId;

    /**
     * @var string $profileName
     *
     * @ORM\Column(name="profile_name", type="string", length=64, nullable=false)
     */
    private $profileName;

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
     * Set profileName
     *
     * @param string $profileName
     * @return AdminProfiles
     */
    public function setProfileName($profileName)
    {
        $this->profileName = $profileName;

        return $this;
    }

    /**
     * Get profileName
     *
     * @return string
     */
    public function getProfileName()
    {
        return $this->profileName;
    }
}
