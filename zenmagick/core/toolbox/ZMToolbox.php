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
 * Container for template related utilities.
 *
 * @author mano
 * @package org.zenmagick.toolbox
 * @version $Id$
 */
class ZMToolbox extends ZMModel {
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
     * @var ZMToolboxDate
     * @return ZMToolboxDate
     */
    public $date;
    /** 
     * @var ZMToolboxUtils
     * @return ZMToolboxUtils
     */
    public $utils;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();

        // setup build in tools
        $this->html = ZMLoader::make('ToolboxHtml');
        $this->form = ZMLoader::make('ToolboxForm');
        $this->net = ZMLoader::make('ToolboxNet');
        $this->macro = ZMLoader::make('ToolboxMacro');
        $this->date = ZMLoader::make('ToolboxDate');
        $this->utils = ZMLoader::make('ToolboxUtils');
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
        return ZMObject::singleton('Toolbox');
    }


    /**
     * Get a map of all tools.
     *
     * @return array A map of all available tools.
     */
    public function getTools() {
        return array_merge(array(
            'html' => $this->html, 
            'form' => $this->form, 
            'net' => $this->net, 
            'macro' => $this->macro, 
            'date' => $this->date, 
            'utils' => $this->utils), 
          $this->properties_);
    }

}

?>
