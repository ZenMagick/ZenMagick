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
 * Request controller base class.
 *
 * @author mano
 * @package org.zenmagick.rp
 * @version $Id$
 */
class ZMController extends ZMObject {
    private $id_;
    private $globals_;
    private $view_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();

        $this->globals_ = array();
        // use as controller id
        $this->id_ = ZMRequest::getPageName();

        // always add toolbox
        $this->exportGlobal('_t', ZMToolbox::instance());
        foreach (ZMToolbox::instance()->getTools() as $name => $tool) {
            $this->exportGlobal($name, $tool);
        }

        foreach ($GLOBALS as $name => $instance) {
            if (ZMTools::startsWith($name, "zm_")) {
                if (is_object($instance)) {
                    $this->exportGlobal($name, $GLOBALS[$name]);
                }
            }
        }
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Process a HTTP request.
     *
     * <p>Supported request methods are <code>GET</code> and <code>POST</code>.</p>
     *
     * @return ZMView A <code>ZMView</code> instance or <code>null</code>.
     */
    public function process() { 
        ZMSacsMapper::instance()->ensureAuthorization($this->id_);
        ZMEvents::instance()->fireEvent($this, ZM_EVENT_CONTROLLER_PROCESS_START);

        $view = null;
        switch (ZMRequest::getMethod()) {
            case 'GET':
                $view = $this->processGet();
                break;
            case 'POST':
                $view = $this->processPost();
                break;
            default:
                die('Unsupported request method: ' . $_SERVER['REQUEST_METHOD']);
        }

        if (null != $view) {
            if (!$view->isValid()) {
                $this->log('Invalid view: '.$view->getName(), ZM_LOG_WARN);
                $view = $this->findView(ZMSettings::get('missingPageId'));
            }
            $view->setController($this);
            $this->view_ = $view;
        }

        ZMEvents::instance()->fireEvent($this, ZM_EVENT_CONTROLLER_PROCESS_END, array('view' => $this->view_));

        return $view;
    }


    /**
     * Process a HTTP GET request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    public function processGet() {
        return $this->findView();
    }


    /**
     * Process a HTTP POST request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    public function processPost() { return $this->processGet(); }


    /**
     * Set the response content type.
     *
     * @param string type The content type.
     * @param string charset Optional charset; default is utf-8; <code>null</code> will omit the charset part.
     */
    public function setContentType($type, $charset="utf-8") {
        $text = "Content-Type: " . $type;
        if (null != $charset) {
            $text .= "; charset=" . $charset;
        }
        header($text);
    }


    /**
     * Returns a <code>name => object</code> hash of variables that need to be exported
     * into the theme space.
     *
     * @return array An associative array of <code>name => object</code> for all variables
     *  that need to be exported into the theme space.
     */
    public function getGlobals() {
        return $this->globals_;
    }


    /**
     * Export the given object under the given name.
     * <p>Controller may use this method to make objects available to the response template/view.</p>
     *
     * @param string name The name under which the object should be visible.
     * @param mixed instance An object.
     */
    public function exportGlobal($name, $instance) {
        if (null === $instance)
            return;
        $this->globals_[$name] = $instance;
    }

    /**
     * Returns the named global.
     *
     * @param string name The object name.
     * @param mixed instance An object instance or <code>null</code>.
     */
    public function getGlobal($name) {
        return array_key_exists($name, $this->globals_) ? $this->globals_[$name] : null;
    }

    /**
     * Lookup the appropriate view for the given name.
     *
     * <p>This implementation might be changed later on to allow for view mapping to make the page
     * flow configurable and extract that knowlege from the controller into some sort of config
     * file or other piece of logic.</p>
     *
     * @param string id The controller id or <code>null</code> to return to the current page.
     * @param array parameter Optional map of name/value pairs to further configure the view; default is <code>null</code>.
     * @return ZMView The actual view to be used to render the response.
     */
    public function findView($id=null, $parameter=null) {
        // page and controller name *must* be the same as the logic to 
        // build the controller name is based on that fact!
        $view = ZMUrlMapper::instance()->findView($this->id_, $id, $parameter);
        return $view;
    }

    /**
     * Validate the current request using the given rule id.
     *
     * @param string id The <code>ZMRuleSet</code> id.
     * @param mixed req A (request) map, an object or <code>null</code> to default to <code>$_POST</code>.
     * @return boolean <code>true</code> if the validation was successful, <code>false</code> if not.
     */
    public function validate($id, $req=null) {
        if (null === $req) {
            $req = zm_sanitize($_POST);
        }

        if (!ZMValidator::instance()->hasRuleSet($id)) {
            return true;
        }

        $valid = ZMValidator::instance()->validate($req, $id);
        if (!$valid) {
            foreach (ZMValidator::instance()->getMessages() as $field => $fieldMessages) {
                foreach ($fieldMessages as $msg) {
                    ZMMessages::instance()->error($msg, $field);
                }
            }
        }

        return $valid;
    }

    /**
     * Get the current view.
     *
     * @return ZMView The view or <code>null</code>.
     */
    public function getView() {
        return $this->view_;
    }

    /**
     * Get the controller id.
     *
     * @return string The id (page name).
     */
    public function getId() { return $this->id_; }

}

?>
