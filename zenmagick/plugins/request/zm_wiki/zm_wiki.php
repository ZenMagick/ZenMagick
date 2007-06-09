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
 * Plugin adding a simple wiki.
 *
 * <p>This plugin is based on pawfaliki (http://www.pawfal.org/pawfaliki).</p>
 *
 * @author mano
 * @package net.radebatz.zenmagick.plugins.zm_wiki
 * @version $Id$
 */
class zm_wiki extends ZMPlugin {

    /**
     * Default c'tor.
     */
    function zm_wiki() {
        parent::__construct('Pawfaliki Wiki', 'Adds a Wiki.');
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->zm_wiki();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Install this plugin.
     */
    function install() {
        parent::install();

        zm_mkdir(str_replace('/', DIRECTORY_SEPARATOR, DIR_FS_CATALOG."wiki/files/"));
        zm_mkdir(str_replace('/', DIRECTORY_SEPARATOR, DIR_FS_CATALOG."wiki/tmp/"));
    }

}

?>
