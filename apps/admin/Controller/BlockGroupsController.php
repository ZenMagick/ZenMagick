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


/**
 * Admin controller for block groups.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class BlockGroupsController extends \ZMController {

    /**
     * {@inheritDoc}
     */
    public function getViewData($request) {
        $blockGroups = array();
        $configService = $this->container->get('configService');
        if (null == ($configValue = $configService->getConfigValue('ZENMAGICK_BLOCK_GROUPS'))) {
            // create if not exist
            $configService->createConfigValue('Block Groups', 'ZENMAGICK_BLOCK_GROUPS', '', ZENMAGICK_CONFIG_GROUP_ID);
        } else {
            $blockGroups = explode(',', $configValue->getValue());
        }

        return array('blockGroups' => $this->container->get('blockService')->getBlockGroups());
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $blockService = $this->container->get('blockService');
        $action = $request->request->get('action');
        switch ($action) {
        case 'addGroup':
            $groupName = $request->request->get('groupName');
            if (!empty($groupName)) {
                $blockGroup = new BlockGroup();
                $blockGroup->setName($groupName);
                $blockService->createBlockGroup($blockGroup);
                $this->messageService->success(sprintf(_zm('Block group %s added.'), $groupName));
            }
            break;
        case 'removeGroup':
            $groupName = $request->request->get('groupName');
            if (!empty($groupName)) {
                $blockService->deleteGroupForName($groupName);
                $this->messageService->success(sprintf(_zm('Block group %s removed.'), $groupName));
            }
            break;
        }

        return $this->findView('success');
    }

}
