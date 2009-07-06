<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 * @author DerManoMann
 * @package org.zenmagick.store.mvc
 * @version $Id: ZMView.php 2350 2009-06-29 04:22:59Z dermanomann $
 */
class ZMView extends ZMObject {
    protected $vars_;
    protected $controller_;
    protected $view_;
    protected $viewId_;
    protected $subdir_;


    /**
     * Create new view for the given view name and id.
     *
     * @param string view The view template name; default is </code>null</code>.
     * @param string mapping The mapping id; default is </code>null</code>.
     * @deprecated: contructor arguments
     */
    function __construct($view=null, $viewId=null) {
        parent::__construct();
        $this->vars_ = array();
        $this->view_ = $view;
        $this->viewId_ = $viewId;
        $this->subdir_ = null;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Add a variable.
     *
     * @param string name The variable name.
     * @param mixed value The value.
     */
    public function setVar($name, $value) {
        $this->vars_[$name] = $value;
    }

    /**
     * Add a list of variable.
     *
     * @param array vars The name/value pairs.
     */
    public function setVars($vars) {
        $this->vars_ = array_merge($this->vars_, $vars);
    }

    /**
     * Get the list of variable.
     *
     * @return array The name/value pairs.
     */
    public function getVars() {
        return $this->vars_;
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
        //XXX: was using themeInfo
        $filename = 'views'.DIRECTORY_SEPARATOR;
        $subdir = null != $subdir ? $subdir : $this->subdir_;
        if (null != $subdir) {
            $filename .= $subdir.'/';
            if ($prefixToDir) {
                $off = strpos($this->view_, '_');
                // if no '_' found, just use the full name
                if (false !== $off) {
                    $filename .= substr($this->view_, strlen($subdir)+1);
                } else {
                    $filename .= $this->view_;
                }
            } else {
                $filename .= $this->view_;
            }
        } else {
            $filename .= $this->view_;
        }
        $filename .= ZMSettings::get('templateSuffix');

        return Runtime::getTheme()->themeFile($filename);
    }

    /**
     * Return the view name.
     *
     * @return string The view name.
     */
    public function getName() { return $this->view_; }

    /**
     * Set the view name.
     *
     * @param string name The view name.
     */
    public function setName($name) { $this->view_ = $name; }

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
     *
     * @param ZMRequest request The current request.
     */
    public function generate($request) { throw new ZMException('not implemented'); }

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
     * Get the view.
     *
     * @return string The view.
     */
    public function getView() {
        return $this->view_;
    }

    /**
     * Set the view name.
     *
     * <p>In most cases this will be used as template name (plus the extension
     * as configued via the setting <em>templateSuffix</em>.</p>
     *
     * @param string view The view.
     */
    public function setView($view) {
        $this->view_ = $view;
    }

    /**
     * Get the view id.
     *
     * @return string The view id.
     */
    public function getViewId() {
        return $this->viewId_;
    }

    /**
     * Set the view id.
     *
     * @param string viewId The view id.
     */
    public function setViewId($viewId) {
        $this->viewId_ = $viewId;
    }

    /**
     * Check if the response is generated by a function or file.
     *
     * @return boolean <code>true</code> if the view content is generated
     *  by a function, <code>false</code> if not.
     */
    public function isViewFunction() {
        return function_exists($this->view_);
    }

    /**
     * Call the function that generates the view contents.
     *
     * @return boolean <code>true</code> if the view was generated using a function.
     */
    public function callView() {
        if ($this->isViewFunction()) {
            call_user_func($this->view_);
            return true;
        }
        return false;
    }

    /**
     * Get the content type.
     *
     * @return string The content type.
     */
    public function getContentType() {
        return 'text/html';
    }

    /**
     * Get the character encoding.
     *
     * @return string The encoding.
     */
    public function getEncoding() {
        return zm_i18n('HTML_CHARSET');
    }

}

?>
