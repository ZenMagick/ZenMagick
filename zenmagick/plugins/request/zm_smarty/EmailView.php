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
 * Simple email view for Smarty templates.
 *
 * <p>Email template are expected in the directory <code>[theme-views-dir]/emails</code>.
 * Filenames follow the pattern <code>[$template].[html|text].php</code>.<p>
 *
 * @author mano
 * @package org.zenmagick.plugins.zm_smarty
 * @version $Id$
 */
class EmailView extends ZMEmailView {

    /**
     * Create new email view.
     *
     * @param string template The template name.
     * @param boolean html Flag to indicate whether to use the HTML or text template; default is <code>true</code>.
     * @param array args Additional context values.
     */
    function EmailView($template, $html=true, $args=array()) {
        parent::__construct($template, $html, $args);
    }

    /**
     * Create new email view.
     *
     * @param string template The template name.
     * @param boolean html Flag to indicate whether to use the HTML or text template; default is <code>true</code>.
     * @param array args Additional context values.
     */
    function __construct($template, $html=true, $args=array()) {
        $this->EmailView($template, $html, $args);
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Generate email content.
     *
     * <p>In contrast to other views, this version will actually not display anything, but rather
     * return the generated content in order to be captured and passed into the actual mail
     * code.</p>
     */
    function generate() {
    global $zm_smarty;

        // first, check for file
        $filename = $this->getViewFilename();
        if (!file_exists($filename)) {
            return "";
        }

        // get smarty instance
        $smarty = $zm_smarty->getSmarty();

        // *export* globals from controller into template space
        $controller = $this->getController();
        foreach ($controller->getGlobals() as $name => $instance) {
            $smarty->assign($name, $instance);
        }
        // same for custom args
        foreach ($this->args_ as $name => $instance) {
            $smarty->assign($name, $instance);
        }

        // function proxy 
        $smarty->assign('zm', $this->create('FunctionProxy'));

        ob_start();
        $smarty->display($filename);
        return ob_get_clean();
    }

}

?>
