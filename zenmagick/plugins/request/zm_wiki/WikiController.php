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
 * Request controller for wiki pages.
 *
 * @author mano
 * @package org.zenmagick.plugins.zm_wiki
 * @version $Id$
 */
class WikiController extends ZMController {

    /**
     * Create new instance.
     */
    function WikiController() {
        parent::__construct();
    }

    /**
     * Create new instance.
     */
    function __construct() {
        $this->WikiController();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Process a HTTP GET request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    function processGet() {
    global $zm_request, $zm_wiki;

        ZMCrumbtrail::instance()->clear();
        ZMCrumbtrail::instance()->addCrumb("Wiki", zm_href(ZM_FILENAME_WIKI, '', false));
        $page = $zm_request->getParameter('page', 'WikiRoot');
        ZMCrumbtrail::instance()->addCrumb(zm_format_title($page));

        return $this->create("PluginView", zm_view_wiki, $zm_wiki);
    }


    /**
     * Process a HTTP POST request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    function processPost() {
    global $zm_request;

        ZMCrumbtrail::instance()->clear();
        ZMCrumbtrail::instance()->addCrumb("Wiki", zm_href(ZM_FILENAME_WIKI, '', false));
        $page = $zm_request->getParameter('page', 'WikiRoot');
        ZMCrumbtrail::instance()->addCrumb(zm_format_title($page));

        return $this->create("PluginView", zm_view_wiki_edit, $zm_wiki);
    }

}

?>
