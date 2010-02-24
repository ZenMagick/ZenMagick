<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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
    private $mappings_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
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
     * Set block mappings.
     *
     * <p>Replace existing mappings.</p>
     *
     * @param array mappings The new mappings.
     */
    public function setMappings($mappings) {
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
        $view = $args['view'];

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

?>
