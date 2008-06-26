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
 * A view.
 *
 * @author mano
 * @package org.zenmagick.rp
 * @version $Id$
 */
class ZMView extends ZMObject {
    private $controller_;
    private $page_;
    private $mappingId_;
    private $subdir_;


    /**
     * Create new view for the given name.
     *
     * @param string page The page (view) name.
     * @param string mapping The mapping id.
     */
    function __construct($page, $mappingId=null) {
        parent::__construct();
        $this->page_ = $page;
        $this->mapping_ = $mappingId;
        $this->subdir_ = null;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Returns the full view filename to be included by a template.
     *
     * @return string The full view filename.
     */
    public function getViewFilename() {
        return $this->_getViewFilename();
    }

    /**
     * Check if this view is valid.
     *
     * @return boolean <code>true</code> if the view is valid, <code>false</code> if not.
     */
    public function isValid() {
        return file_exists($this->_getViewFilename());
    }

    /**
     * Returns the full view filename to be included by a template.
     *
     * <p>The subdir parameter will override the subdir that might have previously set on the instance.</p>
     *
     * @param string subdir Optional subdirectory name within the views directory.
     * @param boolean $prefixToDir If <code>true</code> the subdir is assumed to be the view filename prefix; eg: 'popup_'. If this is the case,
     *  it gets converted into an additional ssubdir instead. Example: <code>popup_cvv_help.php</code> = <code>popup/cvv_help.php</code>.
     * @return string The full view filename.
     */
    protected function _getViewFilename($subdir=null, $prefixToDir=true) {
        $filename = ZMRuntime::getTheme()->getViewsDir();
        $subdir = null != $subdir ? $subdir : $this->subdir_;
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
        $filename .= ZMSettings::get('templateSuffix');

        return ZMRuntime::getTheme()->themeFile($filename);
    }

    /**
     * Return the view name.
     *
     * @return string The view name.
     */
    public function getName() { return $this->page_; }

    /**
     * Set the view name.
     *
     * @param string name The view name.
     */
    public function setName($name) { $this->page_ = $name; }

    /**
     * Set an optional subdir.
     *
     * @param string subdir The subdirectory.
     */
    public function setSubdir($subdir) { $this->subdir_ = $subdir; }

    /**
     * Get optional subdir.
     *
     * @return subdir The subdirectory.
     */
    public function getSubdir() { return $this->subdir_; }

    /**
     * Generate view response.
     */
    public function generate() { die('not implemented'); }

    /**
     * Set the controller for this view.
     *
     * @param controller ZMController The corresponding controller.
     */
    public function setController($controller) { $this->controller_ = $controller; }

    /**
     * Get the controller for this view.
     *
     * @return ZMController The corresponding controller.
     */
    public function getController() { return $this->controller_; }

    /**
     * Get the mapping id for this view.
     *
     * @return string The mapping id.
     */
    public function getMappingId() {
        return $this->mappingId_;
    }

    /**
     * Set the mapping id for this view.
     *
     * @param string mapping The mapping id.
     */
    public function setMappingId($mappingId) {
        $this->mappingId_ = $mappingId;
    }

    /**
     * Check if the page is generated by a function or file.
     *
     * @return boolean <code>true</code> if the view content is generated
     *  by a function, <code>false</code> if not.
     */
    public function isViewFunction() {
        return function_exists($this->page_);
    }

    /**
     * Call the function that generates the view contents.
     *
     * @return boolean <code>true</code> if the view was generated using a function.
     */
    public function callView() {
        if ($this->isViewFunction()) {
            call_user_func($this->page_);
            return true;
        }
        return false;
    }
}

?>
