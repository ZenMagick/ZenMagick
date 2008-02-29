<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
 * Plugin for Ultimate SEO 3.x support.
 *
 * @package org.zenmagick.plugins.zm_useo3
 * @author mano
 * @version $Id: zm_useo3.php 216 2007-07-16 22:07:44Z DerManoMann $
 */
class zm_useo3 extends ZMPlugin {

    /**
     * Create new instance.
     */
    function zm_useo3() {
        parent::__construct('ZenMagick Ultimate SEO3', 'Ultimate SEO 3.x for ZenMagick', '${plugin.version}');
        $this->setLoaderSupport('ALL');
    }

    /**
     * Create new instance.
     */
    function __construct() {
        $this->zm_useo3();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

}

?>
