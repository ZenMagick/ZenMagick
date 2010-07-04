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
 * Admin controller for legacy config.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.controller
 */
class ZMLegacyConfigController extends ZMController {

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
    public function processGet($request) {
        $groupId = $request->getParameter('groupId');
        $group = ZMConfig::instance()->getConfigGroupForId($groupId);
        $groupValues = ZMConfig::instance()->getValuesForGroupId($groupId);
        return $this->findView(null, array('group' => $group, 'groupValues' => $groupValues));
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $groupId = $request->getParameter('groupId');

        if ($request->handleDemo()) {
            return $this->findView('success-demo', array(), array('parameter' => 'groupId='.$groupId));
        }

        $group = ZMConfig::instance()->getConfigGroupForId($groupId);
        $groupValues = ZMConfig::instance()->getValuesForGroupId($groupId);

        // update changed
        $updated = array();
        foreach ($groupValues as $widget) {
            $name = $widget->getName();
            $oldValue = $widget->getValue();
            if (null !== ($newValue = $request->getParameter($name)) && $newValue != $oldValue) {
                // update
                ZMConfig::instance()->updateConfigValue($name, $newValue);
                $updated[] = $widget->getTitle();
            }
        }
        if (0 < count($updated)) {
            ZMMessages::instance()->success(sprintf(_zm('Sucessfully updated: %s.'), "'".implode("', '", $updated)."'"));
        }

        // 'parameter' is a property on the view class...
        return $this->findView('success', array(), array('parameter' => 'groupId='.$groupId));
    }

}
