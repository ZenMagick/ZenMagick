<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006,2009 ZenMagick
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
 * @package org.zenmagick.mvc.utils
 * @version $Id: ZMToolbox.php 2149 2009-04-13 22:59:14Z dermanomann $
 */
class ZMToolbox extends ZMObject {
    /** 
     * @var ZMToolboxHtml
     * @return ZMToolboxHtml
     */
    public $html;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->html = ZMLoader::make('ToolboxHtml');
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
        return array_merge(array('html' => $this->html, $this->properties_));
    }

}

?>
