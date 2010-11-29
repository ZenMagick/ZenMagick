<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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


/**
 * Admin controller for block groups.
 *
 * @author DerManoMann
 * @package zenmagick.store.admin.mvc.controller
 */
class ZMBlockGroupsController extends ZMController {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * {@inheritDoc}
     */
    public function getViewData($request) {
        $blockGroups = array();
        if (null == ($configValue = ZMConfig::instance()->getConfigValue('ZENMAGICK_BLOCK_GROUPS'))) {
            // create if not exist
            ZMConfig::instance()->createConfigValue('Block Groups', 'ZENMAGICK_BLOCK_GROUPS', '', ZENMAGICK_CONFIG_GROUP_ID);
        } else {
            $blockGroups = explode(',', $configValue->getValue());
        }

        return array('blockGroups' => $blockGroups);
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $action = $request->getParameter('action');
        switch ($action) {
        case 'addGroup':
            $groupId = $request->getParameter('groupId');
            if (!empty($groupId)) {
                $configValue = ZMConfig::instance()->getConfigValue('ZENMAGICK_BLOCK_GROUPS');
                $values = explode(',', $configValue->getValue());
                $values[] = $groupId;
                ZMConfig::instance()->updateConfigValue('ZENMAGICK_BLOCK_GROUPS', implode(',', $values));
                ZMMessages::instance()->success(sprintf(_zm('Block group %s added.'), $groupId));
            }
            break;
        case 'removeGroup':
            $groupId = $request->getParameter('groupId');
            if (!empty($groupId)) {
                $configValue = ZMConfig::instance()->getConfigValue('ZENMAGICK_BLOCK_GROUPS');
                $tmp = array();
                foreach (explode(',', $configValue->getValue()) as $value) {
                    if (!empty($value) && $value!= $groupId) {
                        $tmp[] = $value;
                    }
                }
                ZMConfig::instance()->updateConfigValue('ZENMAGICK_BLOCK_GROUPS', implode(',', $tmp));
                ZMMessages::instance()->success(sprintf(_zm('Block group %s removed.'), $groupId));
            }
            break;
        }

        return $this->findView('success');
    }

}
