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
 * Manage template blocks.
 *
 * @author DerManoMann
 * @package org.zenmagick.mvc.services.blocks
 */
class ZMBlockManager extends ZMObject {
    private $providers_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->providers_ = null;
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
        return ZMObject::singleton('BlockManager');
    }

    /**
     * Get a list of all registered providers.
     *
     * @return array A list of <code>ZMBlockContentsProvider</code> instances.
     */
    public function getProviders() {
        if (null == $this->providers_) {
            $this->providers_ = array();
            foreach (explode(',', ZMSettings::get('plugins.blockHandler.blockContentsProviders')) as $providerId) {
                $provider = ZMBeanUtils::getBean($providerId);
                if (null != $provider && $provider instanceof ZMBlockContentsProvider) {
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
     * @return array List of <code>ZMBlockWidget</code> instances.
     */
    public function getBlocksForId($request, $groupId) {
        return array();
    }

}
