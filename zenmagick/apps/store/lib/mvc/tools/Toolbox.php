<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
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
 * Container for store template utilities.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.tools
 * @version $Id$
 */
class Toolbox extends ZMToolbox {
    /** 
     * @var ZMToolboxNet
     * @return ZMToolboxNet
     */
    public $net;
    /** 
     * @var ZMToolboxHtml
     * @return ZMToolboxHtml
     */
    public $html;
    /** 
     * @var ZMToolboxForm
     * @return ZMToolboxForm
     */
    public $form;
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
     * @var ZMToolboxCrumbtrail
     * @return ZMToolboxCrumbtrail
     */
    public $crumbtrail;
    /** 
     * @var ZMToolboxMetaTags
     * @return ZMToolboxMetaTags
     */
    public $metaTags;


    /**
     * Create new instance.
     */
    function __construct($request) {
        // add store tools
        $tools = array(
            'net' => 'ToolboxNet', 
            'html' => 'ToolboxHtml', 
            'form' => 'ToolboxForm', 
            'macro' => 'ToolboxMacro', 
            'locale' => 'ToolboxLocale', 
            'utils' => 'ToolboxUtils', 
            'admin' => 'ToolboxAdmin',
            'crumbtrail' => 'ToolboxCrumbtrail',
            'metaTags' => 'ToolboxMetaTags'
        ); 
        foreach ($tools as $name => $class) {
            ZMSettings::append('zenmagick.mvc.toolbox.tools', $name.':'.$class);
        }
        parent::__construct($request);
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

}
