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
namespace ZenMagick\apps\admin\Controller;

use ZenMagick\Base\Beans;
use ZenMagick\StoreBundle\Entity\Blocks\Block;

/**
 * Ajax block group admin controller.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class AjaxBlockGroupAdminController extends \ZMRpcController {

    /**
     * Remove a block.
     *
     * @param ZMRpcRequest rpcRequest The RPC request.
     */
    public function removeBlockFromGroup($rpcRequest) {
        $data = $rpcRequest->getData();
        $rpcResponse = $rpcRequest->createResponse();

        $token = explode('@', $data->block);
        if (2 == count($token)) {
            $this->container->get('blockService')->deleteBlockForId($token[0]);
            $rpcResponse->setStatus(true);
        }

        return $rpcResponse;
    }

    /**
     * Add a block to a group.
     *
     * @param ZMRpcRequest rpcRequest The RPC request.
     */
    public function addBlockToGroup($rpcRequest) {
        $data = $rpcRequest->getData();
        $rpcResponse = $rpcRequest->createResponse();

        // process
        $newBlocks = $this->updateGroupBlockList($data->groupName, $data->groupBlockList);

        if (1 == count($newBlocks)) {
            $rpcResponse->setStatus(true);
            $block = $newBlocks[0];
            $rpcResponse->setData(array('blockId' => $block->getBlockId(), 'options' => false));
        }

        return $rpcResponse;
    }

    /**
     * Reorder a block group.
     *
     * @param ZMRpcRequest rpcRequest The RPC request.
     */
    public function reorderBlockGroup($rpcRequest) {
        $data = $rpcRequest->getData();
        $rpcResponse = $rpcRequest->createResponse();

        // process
        $this->updateGroupBlockList($data->groupName, $data->groupBlockList);

        $rpcResponse->setStatus(true);
        return $rpcResponse;
    }

    /**
     * Update the given group block list.
     *
     * @param string groupName The group name.
     * @param array groupBlockList The group block list details.
     * @return array List of newly created blocks;
     */
    protected function updateGroupBlockList($groupName, $groupBlockList) {
        $blockService = $this->container->get('blockService');
        $currentBlocks = $blockService->getBlocksForGroupName($groupName);

        // iterate, compare and re-sort
        $index = 0;
        $newBlocks = array();
        $updateBlocks = array();
        foreach ($groupBlockList as $blockInfo) {
            // try to split; format is: {blockId@}def
            $token = explode('@', $blockInfo);
            if (2 == count($token)) {
                // existing
                foreach ($currentBlocks as $block) {
                    if ($block->getBlockId() == $token[0]) {
                        if ($block->getSortOrder() != $index) {
                            // need to update sort order
                            $block->setSortOrder($index);
                            $updateBlocks[] = $block;
                        }
                        break;
                    }
                }
            } else {
                // new
                $blockWidget = Beans::getBean($token[0]);
                $block = new Block();
                $block->setName($blockWidget->getTitle());
                $block->setDefinition($token[0]);
                $block->setSortOrder($index);
                $newBlocks[] = $blockService->addBlockToBlockGroup($groupName, $block);
            }
            ++$index;
        }
        foreach ($updateBlocks as $block) {
            $blockService->updateBlock($block);
        }

        return $newBlocks;
    }

}
