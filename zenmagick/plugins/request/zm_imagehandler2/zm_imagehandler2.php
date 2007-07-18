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
 * Plugin to enable support for ImageHandler2 in ZenMagick.
 *
 * @package net.radebatz.zenmagick.plugins.zm_imagehandler2
 * @author mano
 * @version $Id$
 */
class zm_imagehandler2 extends ZMPlugin {

    /**
     * Default c'tor.
     */
    function zm_imagehandler2() {
        parent::__construct('ZenMagick ImageHandler2', 'ImageHandler2 support for ZenMagick', '${plugin.version}');
        $this->setLoaderSupport('ALL');
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->zm_imagehandler2();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }

}

?>
