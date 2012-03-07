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

use zenmagick\base\ZMObject;

use Doctrine\ORM\Mapping as ORM;

/**
 * Block model class.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.model.blocks
 * @ORM\Table(name="blocks_to_groups")
 * @ORM\Entity
 */
class ZMBlock extends ZMObject {
    /**
     * @var integer $blockId
     *
     * @ORM\Column(name="blocks_to_groups_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $blockId;
    /**
     * @var integer $groupId
     *
     * @ORM\Column(name="block_group_id", type="integer", nullable=false)
     * @todo: foreign key
     */
    private $groupId;
    /**
     * @var string $name
     *
     * @ORM\Column(name="block_name", type="string", length=32, nullable=false)
     */
    private $name;
    /**
     * @var string $definition
     *
     * @ORM\Column(name="definition", type="text")
     */
    private $definition;
    /**
     * @var string $sortOrder
     *
     * @ORM\Column(name="sort_order", type="integer")
     */
    private $sortOrder;

    /*
     * TODO:
     * - template
     * - format
     */

    /**
     * Get the block id.
     *
     * @return int The block id.
     */
    public function getBlockId() { return $this->blockId; }

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
     * Get the definition
     *
     * @return string The definition
     */
    public function getDefinition() { return $this->definition; }

    /**
     * Get the sort order
     *
     * @return integer The sort order
     */
    public function getSortOrder() { return $this->sortOrder; }

    /**
     * Set the block id.
     *
     * @param id int The block id.
     */
    public function setBlockId($id) { $this->blockId = $id; }

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
     * Set the definition.
     *
     * @param string definition The definition.
     */
    public function setDefinition($definition) { $this->definition = $definition; }

    /**
     * Set the sort order
     *
     * @param integer sortOrder The sort order
     */
    public function setSortOrder($sortOrder) { $this->sortOrder = $sortOrder; }


    //TODO: remove
    public function setBlocks_to_groups_id($id) { $this->blockId = $id; }
    public function setBlock_group_id($id) { $this->groupId = $id; }
    public function setBlock_name($name) { $this->name = $name; }
    public function setSort_order($sortOrder) { $this->sortOrder = $sortOrder; }

}
