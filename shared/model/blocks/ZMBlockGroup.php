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
?>
<?php

use zenmagick\base\ZMObject;

use Doctrine\ORM\Mapping as ORM;

/**
 * Block group model class.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.model.blocks
 * @ORM\Table(name="block_groups")
 * @ORM\Entity
 */
class ZMBlockGroup extends ZMObject {
    /**
     * @var integer $blockGroupId
     *
     * @ORM\Column(name="block_group_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $blockGroupId;
    /**
     * @var string $name
     *
     * @ORM\Column(name="group_name", type="string", length=32, nullable=false)
     */
    private $name;
    /**
     * @var string $description
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * Get the group id.
     *
     * @return int The group id.
     */
    public function getGroupId() { return $this->groupId; }

    /**
     * Get the name
     *
     * @return string The name.
     */
    public function getName() { return $this->name; }

    /**
     * Get the description
     *
     * @return string The description
     */
    public function getDescription() { return $this->description; }

    /**
     * Set the group id.
     *
     * @param int id The new group id.
     */
    public function setGroupId($id) { $this->groupId = $id; }

    /**
     * Set the name.
     *
     * @param string name The name.
     */
    public function setName($name) { $this->name = $name; }

    /**
     * Set the description.
     *
     * @param string description The description.
     */
    public function setDescription($description) { $this->description = $description; }

}
