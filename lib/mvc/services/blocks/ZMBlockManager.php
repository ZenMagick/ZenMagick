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

use zenmagick\base\Runtime;

/**
 * Manage template blocks.
 *
 * @author DerManoMann
 * @package org.zenmagick.mvc.services.blocks
 */
class ZMBlockManager extends ZMObject {
    private $providers_;
    private $mappings_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->providers_ = null;
        $this->mappings_ = array();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get instance.
     */
    public static function instance() {
        return Runtime::getContainer()->getService('ZMBlockManager');
    }

    /**
     * Get a list of all registered providers.
     *
     * @return array A list of <code>ZMBlockContentsProvider</code> instances.
     */
    public function getProviders() {
        if (null == $this->providers_) {
            $this->providers_ = array();
            foreach (explode(',', ZMSettings::get('zenmagick.mvc.blocks.blockProviders')) as $providerId) {
                $provider = ZMBeanUtils::getBean($providerId);
                if (null != $provider && $provider instanceof ZMBlockProvider) {
                    $this->providers_[] = $provider;
                } else {
                    ZMLogging::instance()->log('invalid block contents provider: '.$providerId, ZMLogging::WARN);
                }
            }
        }

        return $this->providers_;
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
                    $widget = ZMBeanUtils::getBean($block);
                } else if (is_object($block) && $block instanceof ZMBlockWidget) {
                    $widget = $block;
                }
                if (null != $widget) {
                    ZMBeanUtils::setAll($widget, $args);
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
