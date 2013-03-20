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

namespace ZenMagick\StoreBundle\Services;

use ZMRuntime;
use ZenMagick\Base\ZMObject;
use ZenMagick\StoreBundle\Entity\Blocks\BlockGroup;
use ZenMagick\StoreBundle\Entity\Blocks\Block;

/**
 * Blocks.
 *
 * @author DerManoMann
 */
class Blocks extends ZMObject
{
    /**
     * Get a list of all block group names.
     *
     * @return array List of block group names.
     */
    public function getBlockGroups()
    {
        $sql = 'SELECT DISTINCT group_name FROM %table.block_groups%';
        $ids = array();
        foreach (ZMRuntime::getDatabase()->fetchAll($sql, array(), 'block_groups') as $result) {
            $ids[] = $result['group_name'];
        }

        return $ids;
    }

    /**
     * Create a new block group.
     *
     * @param ZenMagick\StoreBundle\Entity\Blocks\BlockGroup blockGroup The block group.
     * @return ZenMagick\StoreBundle\Entity\Blocks\BlockGroup The updated block group (incl. id).
     */
    public function createBlockGroup(BlockGroup $blockGroup)
    {
        $sql = 'INSERT INTO %table.block_groups% (group_name, description) VALUES (:group_name, :description)';
        $args = array('group_name' => $blockGroup->getName(), 'description' => $blockGroup->getDescription());
        $conn = ZMRuntime::getDatabase();
        $conn->updateObj($sql, $args, 'block_groups');
        $blockGroup->setId($conn->getResource()->lastInsertId());

        return $blockGroup;
        //return ZMRuntime::getDatabase()->createModel('block_groups', $blockGroup);
    }

    /**
     * Delete block group.
     *
     * @param string groupName The group name.
     */
    public function deleteGroupForName($groupName)
    {
        $sql = 'DELETE FROM %table.block_groups% WHERE group_name = :group_name';
        $args = array('group_name' => $groupName);
        ZMRuntime::getDatabase()->updateObj($sql, $args, 'block_groups');

        //TODO: delete group blocks
        //return ZMRuntime::getDatabase()->removeModel('block_groups', array('group_name' => $groupName));
    }

    /**
     * Get blocks for block group.
     *
     * @param string groupName The group name.
     * @return array List of <code>ZenMagick\StoreBundle\Entity\Blocks\Block</code> instances.
     */
    public function getBlocksForGroupName($groupName)
    {
        // TODO: cache loading all groups when first accessed
        $sql = 'SELECT block_group_id FROM %table.block_groups% WHERE group_name = :group_name';
        $result = ZMRuntime::getDatabase()->querySingle($sql, array('group_name' => $groupName), 'block_groups');

        $sql = "SELECT * FROM %table.blocks_to_groups% WHERE block_group_id = :block_group_id ORDER BY sort_order";

        return ZMRuntime::getDatabase()->fetchAll($sql, array('block_group_id' => $result['block_group_id']), 'blocks_to_groups', 'ZenMagick\StoreBundle\Entity\Blocks\Block');
    }

    /**
     * Add blocks to block group.
     *
     * @param string groupName The group name.
     * @param ZenMagick\StoreBundle\Entity\Blocks\Block block The new block.
     * @return ZenMagick\StoreBundle\Entity\Blocks\Block The block (incl. id).
     */
    public function addBlockToBlockGroup($groupName, $block)
    {
        // TODO: cache loading all groups when first accessed
        $sql = 'SELECT block_group_id FROM %table.block_groups% WHERE group_name = :group_name';
        $result = ZMRuntime::getDatabase()->querySingle($sql, array('group_name' => $groupName), 'block_groups');

        $data = array(
            'block_group_id' => $result['block_group_id'],
            'block_name' => $block->getName(),
            'definition' => $block->getDefinition(),
            'sort_order' => $block->getSortOrder()
        );
        $data = ZMRuntime::getDatabase()->createModel('blocks_to_groups', $data);
        $block->setBlockId($data['blocks_to_groups_id']);

        return $block;
    }

    /**
     * Update the given block.
     *
     * @param ZenMagick\StoreBundle\Entity\Blocks\Block block The block to update.
     */
    public function updateBlock($block)
    {
        $data = array(
            'blocks_to_groups_id' => $block->getBlockId(),
            'block_group_id' => $block->getGroupId(),
            'block_name' => $block->getName(),
            'definition' => $block->getDefinition(),
            'sort_order' => $block->getSortOrder()
        );

        return ZMRuntime::getDatabase()->updateModel('blocks_to_groups', $data);
    }

    /**
     * Delete block for the given id.
     *
     * @param int blockId The block id.
     */
    public function deleteBlockForId($blockId)
    {
        return ZMRuntime::getDatabase()->removeModel('blocks_to_groups', array('blocks_to_groups_id' => $blockId));
    }

}
