<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
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

use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;

/**
 * Blocks.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.services
 */
class ZMBlocks extends ZMObject {

    /**
     * Get instance.
     */
    public static function instance() {
        return Runtime::getContainer()->get('blockService');
    }


    /**
     * Get a list of all block group names.
     *
     * @return array List of block group names.
     */
    public function getBlockGroups() {
        $sql = 'SELECT DISTINCT group_name FROM '.DB_PREFIX.'block_groups';
        $ids = array();
        foreach (ZMRuntime::getDatabase()->fetchAll($sql, array(), DB_PREFIX.'block_groups') as $result) {
            $ids[] = $result['group_name'];
        }
        return $ids;
    }

    /**
     * Create a new block group.
     *
     * @param ZMBlockGroup blockGroup The block group.
     * @return ZMBlockGroup The updated block group (incl. id).
     */
    public function createBlockGroup(ZMBlockGroup $blockGroup) {
        $sql = 'INSERT INTO '.DB_PREFIX.'block_groups' . '(group_name, description) VALUES (:group_name, :description)';
        $args = array('group_name' => $blockGroup->getName(), 'description' => $blockGroup->getDescription());
        $conn = ZMRuntime::getDatabase();
        $conn->update($sql, $args, DB_PREFIX.'block_groups');
        $blockGroup->setId($conn->getResource()->lastInsertId());
        return $blockGroup;
        //return ZMRuntime::getDatabase()->createModel(DB_PREFIX.'block_groups', $blockGroup);
    }

    /**
     * Delete block group.
     *
     * @param string groupName The group name.
     */
    public function deleteGroupForName($groupName) {
        $sql = 'DELETE FROM '.DB_PREFIX.'block_groups' . ' WHERE group_name = :group_name';
        $args = array('group_name' => $groupName);
        ZMRuntime::getDatabase()->update($sql, $args, DB_PREFIX.'block_groups');

        //TODO: delete group blocks
        //return ZMRuntime::getDatabase()->removeModel(DB_PREFIX.'block_groups', array('group_name' => $groupName));
    }

    /**
     * Get blocks for block group.
     *
     * @param string groupName The group name.
     * @return array List of <code>ZMBlock</code> instances.
     */
    public function getBlocksForGroupName($groupName) {
        // TODO: cache loading all groups when first accessed
        $sql = 'SELECT block_group_id FROM '.DB_PREFIX.'block_groups WHERE group_name = :group_name';
        $result = ZMRuntime::getDatabase()->querySingle($sql, array('group_name' => $groupName), DB_PREFIX.'block_groups');

        $sql = "SELECT * FROM ".DB_PREFIX.'blocks_to_groups WHERE block_group_id = :block_group_id ORDER BY sort_order';
        return ZMRuntime::getDatabase()->fetchAll($sql, array('block_group_id' => $result['block_group_id']), DB_PREFIX.'blocks_to_groups', 'ZMBlock');
    }

    /**
     * Add blocks to block group.
     *
     * @param string groupName The group name.
     * @param ZMBlock block The new block.
     * @return ZMBlock The block (incl. id).
     */
    public function addBlockToBlockGroup($groupName, $block) {
        // TODO: cache loading all groups when first accessed
        $sql = 'SELECT block_group_id FROM '.DB_PREFIX.'block_groups WHERE group_name = :group_name';
        $result = ZMRuntime::getDatabase()->querySingle($sql, array('group_name' => $groupName), DB_PREFIX.'block_groups');

        $data = array(
            'block_group_id' => $result['block_group_id'],
            'block_name' => $block->getName(),
            'definition' => $block->getDefinition(),
            'sort_order' => $block->getSortOrder()
        );
        $data = ZMRuntime::getDatabase()->createModel(DB_PREFIX.'blocks_to_groups', $data);
        $block->setBlockId($data['blocks_to_groups_id']);
        return $block;
    }

    /**
     * Update the given block.
     *
     * @param ZMBlock block The block to update.
     */
    public function updateBlock($block) {
        $data = array(
            'blocks_to_groups_id' => $block->getBlockId(),
            'block_group_id' => $block->getGroupId(),
            'block_name' => $block->getName(),
            'definition' => $block->getDefinition(),
            'sort_order' => $block->getSortOrder()
        );
        return ZMRuntime::getDatabase()->updateModel(DB_PREFIX.'blocks_to_groups', $data);
    }

    /**
     * Delete block for the given id.
     *
     * @param int blockId The block id.
     */
    public function deleteBlockForId($blockId) {
        return ZMRuntime::getDatabase()->removeModel(DB_PREFIX.'blocks_to_groups', array('blocks_to_groups_id' => $blockId));
    }

}
