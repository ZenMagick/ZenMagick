<?php
/*
 * ZenMagick - Extensions for zen-cart
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
 * Block manager.
 *
 * <p>If attached to the events service, it will parse the generated HTML and replace
 * HTML comments following the <code>BLOCK_PATTERN</code> with registered contents.</p>
 *
 * <p>Contents will be inserted/replaced in the order registered.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.blockHandler
 * @version $Id$
 */
class ZMBlockManager extends ZMObject {
    const BLOCK_PATTERN = '/<!\-\-\s+block::(\S*)\s+\-\->/';
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
        return ZMObject::singleton('BlockManager');
    }

    /**
     * Get a list of all registered providers.
     *
     * <p>To allow to efficiently reuse existing plugin objects, it is possible to register plugins as provider
     * using the following syntax: <code>plugin:[pluginId]</code> instead of a bean definition or class name.</p>
     *
     * @return array A list of <code>ZMBlockContentsProvider</code> instances.
     */
    public function getProviders() {
        if (null == $this->providers_) {
            $this->providers_ = array();
            foreach (explode(',', ZMSettings::get('plugins.blockHandler.blockContentsProviders')) as $providerId) {
                if (ZMLangUtils::startsWith('plugin:', $providerId)) {
                    $pluginId = str_replace('plugin:', '', $providerId);
                    $provider = ZMPlugins::instance()->getPluginForId($pluginId);
                } else {
                    // bean definition
                    $provider = ZMBeanUtils::getBean($providerId);
                }
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
     * Register a new block.
     *
     * @param string blockId The block id.
     * @param mixed block The new block as either an instance of <code>ZMBlockContent</code>
     *  or a bean definition thereof.
     */
    public function registerBlock($blockId, $block) {
        if (!array_key_exists($blockId, $this->mappings_)) {
            $this->mappings_[$blockId] = array();
        }
        $this->mappings_[$blockId][] = $block;
    }

    /**
     * Find all blocks for a given content string.
     *
     * @param string contents The content to parse.
     * @return array List of blocks found.
     */
    protected function parseBlocks($contents) {
        preg_match_all(self::BLOCK_PATTERN, $contents, $matches);
        if (2 == count($matches)) {
            return $matches[1];
        }
        return array();
    }

   /**
     * Compare blocks.
     *
     * @param ZMBlockContent a First block.
     * @param ZMBlockContent b Second block.
     * @return integer Value less than, equal to, or greater than zero if the first argument is
     *  considered to be respectively less than, equal to, or greater than the second.
     */
    protected function compareBlocks($a, $b) { 
        if ($a->getSortOrder() == $b->getSortOrder()) {
            return 0;
        }
        return ($a->getSortOrder() < $b->getSortOrder()) ? -1 : 1;
    }

    /**
     * Set block mappings.
     *
     * <p>Replace existing mappings.</p>
     *
     * @param array mappings The new mappings.
     * @param boolean sort Optional flag to indicate that the mappings still need sorting; default is <code>false</code>.
     */
    public function setMappings($mappings, $sort=false) {
        if ($sort) {
            foreach ($mappings as $blockId => $blocks) {
                foreach ($blocks as $ii => $block) {
                    if (is_string($block)) {
                        $mappings[$blockId][$ii] = ZMBeanUtils::getBean($block);
                    }
                }
                usort($mappings[$blockId], array($this, 'compareBlocks'));
            }
        }
        $this->mappings_ = $mappings;
    }

    /**
     * Get the block mapppings.
     *
     * <p>This method will instantiate all blocks registered as bean definition, so use with care.</p>
     *
     * @return array Map of all registered blocks.
     */
    public function getMappings() {
        $mappings = array();
        foreach ($this->mappings_ as $blockId => $blockList) {
            $mappings[$blockId] = array();
            foreach ($blockList as $ii => $block) {
                if (is_string($block)) {
                    $this->mappings_[$blockId][$ii] = $block = ZMBeanUtils::getBean($block);
                }
                $mappings[$blockId][] = $block;
            }
        }

        return $mappings;
    }

    /**
     * Handle callback to actualy manipulate the HTML response.
     */
    public function onZMFinaliseContents($args) {
        $request = $args['request'];
        $contents = $args['contents'];

        $blockIds = $this->parseBlocks($contents);
        foreach ($blockIds as $blockId) {
            $blockContents = '';
            if (array_key_exists($blockId, $this->mappings_)) {
                foreach ($this->mappings_[$blockId] as $ii => $block) {
                    if (is_string($block)) {
                        $this->mappings_[$blockId][$ii] = $block = ZMBeanUtils::getBean($block);
                    }
                    $blockContents .= $block->getBlockContents($args);
                }
                // custom pattern for each block
                $pattern = str_replace('(\S*)', $blockId, self::BLOCK_PATTERN);
                $contents = preg_replace($pattern, $blockContents, $contents);
            }
        }

        // update
        $args['contents'] = $contents;
        return $args;
    }

}
