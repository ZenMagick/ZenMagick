<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 *
 * $Id$
 */
?>
<?php

// some default defaults ;)
define ('_ZM_RSS_BOXES_COUNT', 2);
define ('_ZM_RSS_BOXES_PREFIX', 'rss_box_');
define ('_ZM_RSS_BOXES_TEMPLATE', 'box-template.php');


/**
 * Plugin providing functionallity for one or more RSS sideboxes.
 *
 * <p>Example of a box plugin managing multiple sideboxes.</p>
 *
 * @author mano
 * @package org.zenmagick.plugins.zm_rss_boxes
 * @version $Id$
 */
class zm_rss_boxes extends ZMBoxPlugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('RSS Boxes', 'Plugin for up to '._ZM_RSS_BOXES_COUNT.' RSS sideboxes.', '${plugin.version}');
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Install this plugin.
     */
    function install() {
        parent::install();

        for ($ii=1; $ii <= _ZM_RSS_BOXES_COUNT; ++$ii) {
            $this->addConfigValue('RSS URL #'.$ii, _ZM_RSS_BOXES_PREFIX.$ii, '',
              'The URL pointing to a RSS feed to be displayed in box #'.$ii);
        }
    }

    /**
     * Get the ids/names of the boxes supported by this plugin.
     *
     * @return array List of box names.
     */
    function getBoxNames() {
        $keys = array();
        for ($ii=1; $ii <= _ZM_RSS_BOXES_COUNT; ++$ii) {
            array_push($keys, _ZM_RSS_BOXES_PREFIX.$ii);
        }
        return $keys;
    }

    /**
     * Get the contents for the given box id.
     *
     * @return string Contents for the box implementation.
     */
    function getBoxContents($id) {
        $contents = file_get_contents($this->getPluginDirectory()._ZM_RSS_BOXES_TEMPLATE);

        // make them unique
        $contents = str_replace('RSS_URL', $id, $contents);
        return $contents;
    }

}


    /**
     * Get RSS URL for the given id.
     *
     * <p>If the id is <code>null</code>, the id will be determined by analyzing the
     * current (box) filename.</p>
     *
     * @package org.zenmagick.plugins.zm_rss_boxes
     * @param int id The box id; default is <code>null</code>.
     * @param boolean echo If <code>true</code>, the code will be echo'ed as well as returned.
     * @return string The URL or <code>null</code>.
     */
    function zm_rss_box($id=null, $echo=ZM_ECHO_DEFAULT) {
    global $zm_rss_boxes;

        if (!isset($zm_rss_boxes)) {
            return null;
        }

        if (null === $id) {
            $boxName = str_replace('.php', '', basename(__FILE__));
            $id = str_replace(_ZM_RSS_BOXES_PREFIX, '', $boxName);
        }

        $js = $zm_rss_boxes->get($id);

        if ($echo) echo $js;
        return $js;
    }


?>
