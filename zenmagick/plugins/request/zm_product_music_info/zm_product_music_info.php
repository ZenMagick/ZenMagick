<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
 */
?>
<?php


/**
 * Plugin for <em>product_music_info</em> product template.
 *
 * <p>This plugin contains code that previously was part of the core package.</p>
 *
 * <p>Please see the <em>Readme.txt</em> file for information on how to use the
 * included classes and code.</p>
 *
 * @author mano
 * @package net.radebatz.zenmagick.plugins.zm_product_music_info
 * @version $Id$
 */
class zm_product_music_info extends ZMPlugin {

    /**
     * Default c'tor.
     */
    function zm_product_music_info() {
        parent::__construct('Music Product Extras', 'Code for product_music_info product template.', '${plugin.version}');
        $this->setLoaderSupport('ALL');
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->zm_product_music_info();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }

}

?>
