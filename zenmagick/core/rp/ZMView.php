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
 * A view.
 *
 * @author mano
 * @package net.radebatz.zenmagick.rp
 * @version $Id$
 */
class ZMView extends ZMObject {
    var $controller_;
    var $name_;



    /**
     * Create new view for the given name.
     *
     * @param string name The view name.
     */
    function ZMView($name) {
        parent::__construct();

        $this->name_ = $name;
    }

    /**
     * Create new view for the given name.
     *
     * @param string name The view name.
     */
    function __construct($name) {
        $this->ZMView($name);
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Returns the full view filename to be included by a template.
     *
     * @param string subdir Optional subdirectory name within the views directory.
     * @param bool $prefixToDir If <code>true</code> the subdir is assumed to be the view filename prefix; eg: 'popup_'. If this is the case,
     *  it gets converted into an additional ssubdir instead. Example: <code>popup_cvv_help.php</code> = <code>popup/cvv_help.php</code>.
     * @return string The full view filename.
     */
    function getViewFilename($subdir=null, $prefixToDir=true) {
    global $zm_theme;

        $filename = $zm_theme->getViewsDir();
        if (null != $subdir) {
            $filename .= $subdir.'/';
            if ($prefixToDir) {
                $off = strpos($this->name_, '_');
                // if no '_' found, just use the full name
                if (false !== $off) {
                    $filename .= substr($this->name_, strlen($subdir)+1);
                } else {
                    $filename .= $this->name_;
                }
            } else {
                $filename .= $this->name_;
            }
        } else {
            $filename .= $this->name_;
        }
        $filename .= '.php';

        return $zm_theme->themeFile($filename);
    }

    /**
     * Return the view name.
     *
     * @return string The view name.
     */
    function getName() { return $this->name_; }

    /**
     * Generate view response.
     */
    function generate() { die('not implemented'); }

    /**
     * Set the controller for this view.
     *
     * @param controller ZMController The corresponding controller.
     */
    function setController(&$controller) { $this->controller_ =& $controller; }

    /**
     * Get the controller for this view.
     *
     * @return ZMController The corresponding controller.
     */
    function getController() { return $this->controller_; }

}

?>
