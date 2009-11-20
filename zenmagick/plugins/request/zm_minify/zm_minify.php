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
 */
?>
<?php

/**
 * Plugin to use minify.
 *
 * @package org.zenmagick.plugins.zm_minify
 * @author DerManoMann
 * @version $Id$
 */
class zm_minify extends Plugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Minify', 'Minify for ZenMagick', '${plugin.version}');
        $this->setLoaderPolicy(ZMPlugin::LP_FOLDER);
        $this->setScope(Plugin::SCOPE_STORE);
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * {@inheritDoc}
     */
    public function install() {
        parent::install();
        // create minify cache dir
        ZMFileUtils::mkdir(dirname(ZMRuntime::getInstallationPath()).DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'zenmagick'.DIRECTORY_SEPARATOR.'minify');
    }

    //TODO: install options: =f only, create dynamica groups, enable js, enable/disable css, PHP support: off, simple, ZenMagick Context (controller)
    // minify logger
    // controller for ZM context?
    // how to allow simple PHP?
    // delegate minify config to plugin? generate static config via plugin admin page?
    // do not use FirePHP logging option when in ZM context mode
    // custom TemplateManager class with custom onZMFinaliseContents() method; call parent, then examine returned context and replace as appropriate
}

?>
