<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
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
 *
 * $Id$
 */
?>
<?php


/**
 * Custom index controller.
 *
 * <p>Shows how extra filter can be implemented and attached to result lists.</p>
 */
class IndexController extends ZMIndexController {

    // create new instance
    function IndexController() {
        parent::__construct();
    }

    // create new instance
    function __construct() {
        $this->IndexController();
    }

    function __destruct() {
    }


    /** API implementation */

    // process a GET request
    function processGet() {
        // normal processing
        $view = parent::processGet();

        $resultList =& $this->getGlobal("zm_resultList");
        if (null != $resultList) {
            // set refresh flag to true
            $resultList->addFilter(new AlphaFilter(), true);
            // update global
            $this->exportGlobal("zm_resultList", $resultList);
        }

        return $view;
    }

}

?>
