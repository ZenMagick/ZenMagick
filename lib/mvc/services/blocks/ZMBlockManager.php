<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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

use zenmagick\base\Beans;
use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;

/**
 * Manage template blocks.
 *
 * @author DerManoMann
 * @package org.zenmagick.mvc.services.blocks
 */
class ZMBlockManager extends ZMObject {
    private $mappings_;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->mappings_ = array();
    }


    /**
     * Get a list of all registered providers.
     *
     * @return array A list of <code>ZMBlockContentsProvider</code> instances.
     */
    public function getProviders() {
        $providers = array();
        foreach ($this->container->findTaggedServiceIds('zenmagick.http.blocks.provider') as $id => $args) {
            $providers[] = $this->container->get($id);
        }
        return $providers;
    }

    /**
     * Get all blocks for the given block group id.
     *
     * @param ZMRequest request The current request.
     * @param string groupId The block group id.
     * @param array args Optional parameter; default is an empty array.
     * @return array List of <code>ZMBlockWidget</code> instances.
     */
    public function getBlocksForId($request, $groupId, $args) {
        if (array_key_exists($groupId, $this->mappings_)) {
            // ensure bean definitions are resolved first...
            $group = array();
            foreach ($this->mappings_[$groupId] as $block) {
                $widget = null;
                if (is_string($block)) {
                    $widget = Beans::getBean($block);
                } else if (is_object($block) && $block instanceof ZMBlockWidget) {
                    $widget = $block;
                }
                if (null != $widget) {
                    Beans::setAll($widget, $args);
                    $group[] = $widget;
                }
            }
            $this->mappings_[$groupId] = $group;
            return $this->mappings_[$groupId];
        }

        return array();
    }

    /**
     * Set mappings.
     *
     * @param array mappings The mappings.
     */
    public function setMappings($mappings) {
        $this->mappings_ = $mappings;
    }

}
