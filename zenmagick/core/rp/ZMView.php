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
    var $page_;
    var $mappingId_;


    /**
     * Create new view for the given name.
     *
     * @param string page The page (view) name.
     * @param string mapping The mapping id.
     */
    function ZMView($page, $mappingId=null) {
        parent::__construct();

        $this->page_ = $page;
        $this->mapping_ = $mappingId;
    }

    /**
     * Create new view for the given name.
     *
     * @param string page The page (view) name.
     * @param string mappingId The mapping id.
     */
    function __construct($page, $mappingId=null) {
        $this->ZMView($page, $mappingId);
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
        return $this->_getViewFilename($subdir, $prefixToDir);
    }

    /**
     * Check if this view is valid.
     *
     * @param string subdir Optional subdirectory name within the views directory.
     * @param bool $prefixToDir If <code>true</code> the subdir is assumed to be the view filename prefix; eg: 'popup_'. If this is the case,
     *  it gets converted into an additional ssubdir instead. Example: <code>popup_cvv_help.php</code> = <code>popup/cvv_help.php</code>.
     * @return bool <code>true</code> if the view is valid, <code>false</code> if not.
     */
    function isValid($subdir=null, $prefixToDir=true) {
        return file_exists($this->_getViewFilename($subdir, $prefixToDir));
    }

    /**
     * Returns the full view filename to be included by a template.
     *
     * @param string subdir Optional subdirectory name within the views directory.
     * @param bool $prefixToDir If <code>true</code> the subdir is assumed to be the view filename prefix; eg: 'popup_'. If this is the case,
     *  it gets converted into an additional ssubdir instead. Example: <code>popup_cvv_help.php</code> = <code>popup/cvv_help.php</code>.
     * @return string The full view filename.
     */
    function _getViewFilename($subdir=null, $prefixToDir=true) {
    global $zm_theme;

        $filename = $zm_theme->getViewsDir();
        if (null != $subdir) {
            $filename .= $subdir.'/';
            if ($prefixToDir) {
                $off = strpos($this->page_, '_');
                // if no '_' found, just use the full name
                if (false !== $off) {
                    $filename .= substr($this->page_, strlen($subdir)+1);
                } else {
                    $filename .= $this->page_;
                }
            } else {
                $filename .= $this->page_;
            }
        } else {
            $filename .= $this->page_;
        }
        $filename .= '.php';

        return $zm_theme->themeFile($filename);
    }

    /**
     * Return the view name.
     *
     * @return string The view name.
     */
    function getName() { return $this->page_; }

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

    /**
     * Get the mapping id for this view.
     *
     * @return string The mapping id.
     */
    function getMappingId() {
        return $this->mappingId_;
    }

    /**
     * Set the mapping id for this view.
     *
     * @param string mapping The mapping id.
     */
    function setMappingId($mappingId) {
        $this->mappingId_ = $mappingId;
    }

    /**
     * Check if the page is generated by a function or file.
     *
     * @return bool <code>true</code> if the view content is generated
     *  by a function, <code>false</code> if not.
     */
    function isViewFunction() {
        return function_exists($this->page_);
    }

    /**
     * Call the function that generates the view contents.
     */
    function callView() {
        if ($this->isViewFunction()) {
            call_user_func($this->page_);
        }
    }
}

?>
