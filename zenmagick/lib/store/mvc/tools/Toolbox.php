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
 * Container for template related utilities.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.toolbox
 * @version $Id: ZMToolbox.php 2149 2009-04-13 22:59:14Z dermanomann $
 */
class Toolbox extends ZMToolbox {
    /** 
     * @var ZMToolboxForm
     * @return ZMToolboxForm
     */
    public $form;
    /** 
     * @var ZMToolboxNet
     * @return ZMToolboxNet
     */
    public $net;
    /** 
     * @var ZMToolboxMacro
     * @return ZMToolboxMacro
     */
    public $macro;
    /** 
     * @var ZMToolboxLocale
     * @return ZMToolboxLocale
     */
    public $locale;
    /** 
     * @var ZMToolboxUtils
     * @return ZMToolboxUtils
     */
    public $utils;
    /** 
     * @var ZMToolboxAdmin
     * @return ZMToolboxAdmin
     */
    public $admin;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();

        // setup build in tools
        $this->form = ZMLoader::make('ToolboxForm');
        $this->net = ZMLoader::make('ToolboxNet');
        $this->macro = ZMLoader::make('ToolboxMacro');
        $this->locale = ZMLoader::make('ToolboxLocale');
        $this->utils = ZMLoader::make('ToolboxUtils');
        $this->admin = ZMLoader::make('ToolboxAdmin');
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get a map of all tools.
     *
     * @return array A map of all available tools.
     */
    public function getTools($request) {
        return array_merge(array(
            'form' => $this->form, 
            'net' => $this->net, 
            'macro' => $this->macro, 
            'locale' => $this->locale, 
            'utils' => $this->utils, 
            'admin' => $this->admin), 
          parent::getTools($request));
    }

}

?>
