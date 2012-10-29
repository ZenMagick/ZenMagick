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

namespace ZenMagick\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ZenMagick\StoreBundle\Entity\SacsPermissions
 *
 * @ORM\Table(name="sacs_permissions",
 *   uniqueConstraints={@ORM\UniqueConstraint(name="unique_perm", columns={"rid", "type", "name"})}
 * )
 * @ORM\Entity
 */
class SacsPermissions {
    /**
     * @var integer $id
     *
     * @ORM\Column(name="sacs_permission_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string $rid
     *
     * @ORM\Column(name="rid", type="string", length=32, nullable=false)
     */
    private $rid;

    /**
     * @var string $type
     *
     * @ORM\Column(name="type", type="string", nullable=false)
     */
    private $type;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=32, nullable=false)
     */
    private $name;

}
