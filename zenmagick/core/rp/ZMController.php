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
 * Request controller base class.
 *
 * @author DerManoMann
 * @package org.zenmagick.rp
 * @version $Id$
 */
class ZMController extends ZMObject {
    private $id_;
    private $globals_;
    private $view_;
    private $formBean_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();

        // use as controller id
        $this->id_ = ZMRequest::getPageName();
        $this->globals_ = array();
        $this->view_ = null;
        $this->formBean_ = null;

        // always add toolbox
        $this->exportGlobal('toolbox', ZMToolbox::instance());
        foreach (ZMToolbox::instance()->getTools() as $name => $tool) {
            $this->exportGlobal($name, $tool);
        }

        // some generic objects that should always be there
        $this->exportGlobal('session', ZMRequest::getSession());

        if (ZMSettings::get('isLegacyAPI')) {
            foreach ($GLOBALS as $name => $instance) {
                if (ZMTools::startsWith($name, "zm_")) {
                    if (is_object($instance)) {
                        $this->exportGlobal($name, $GLOBALS[$name]);
                    }
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

        $enableTransactions = ZMSettings::get('isEnableTransactions');

        if ($enableTransactions) {
            ZMRuntime::getDatabase()->setAutoCommit(false);
        }

        ZMEvents::instance()->fireEvent($this, ZMEvents::CONTROLLER_PROCESS_START, array('controller' => $this));

        // XXX: add $request to globals
        // move custom template objects into ZMEventFixes (also $session?)

        // handle form bean
        if (null !== ($formBean = $this->getFormBean())) {
            // put form bean in context
            $this->exportGlobal($formBean->getFormId(), $formBean);
        }

        // method independant (pre-)processing
        $this->handleRequest();

        // and validation
        $view = null;
        if (null != $formBean && $this->isFormSubmit()) {
            // move to function
            if (null != ($view = $this->validateFormBean($formBean))) {
                ZMLogging::instance()->log('validation failed for : '.$formBean. '; returning: '.$view, ZMLogging::TRACE);
            }
        }

        if (null == $view) {
            try {
                switch (ZMRequest::getMethod()) {
                    case 'GET':
                        $view = $this->processGet();
                        break;
                    case 'POST':
                        $view = $this->processPost();
                        break;
                    default:
                        throw ZMLoader::make('ZMException', 'unsupported request method: ' . ZMRequest::getMethod());
                }
            } catch (Exception $e) {
                if ($enableTransactions) {
                    ZMRuntime::getDatabase()->rollback();
                    // re-throw
                    throw $e;
                }
            }
        }

        if (null != $view) {
            if (!$view->isValid()) {
                ZMLogging::instance()->log('invalid view: '.$view->getName().', expected: '.$view->getViewFilename(), ZMLogging::WARN);
                $view = $this->findView(ZMSettings::get('missingPageId'));
            }
            $view->setController($this);
            $this->view_ = $view;
        }

        ZMEvents::instance()->fireEvent($this, ZMEvents::CONTROLLER_PROCESS_END, array('controller' => $this, 'view' => $this->view_));

        if ($enableTransactions) {
            ZMRuntime::getDatabase()->commit();
            ZMRuntime::getDatabase()->setAutoCommit(true);
        }

        return $view;
    }


    /**
     * Generic callback for request processing independant from the method.
     */
    public function handleRequest() {
    }

    /**
     * Check if this request is a form submit.
     *
     * <p>This default implementation will return <code>true</code> for all <em>POST</em> requests.</p>
     *
     * @return boolean <code>true</code> if this is a form submit request.
     */
    public function isFormSubmit() {
        return 'POST' == ZMRequest::getMethod();
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
     * @param string id The controller id or <code>null</code> to return to the current page.
     * @param array parameter Optional map of name/value pairs to further configure the view; default is <code>null</code>.
     * @return ZMView The actual view to be used to render the response.
     */
    public function findView($id=null, $parameter=null) {
        $viewDefinition = ZMUrlMapper::instance()->getViewDefinition($this->id_, $id, $parameter);

        // ensure secure option is set
        if (ZMSacsmapper::instance()->secureRequired($this->id_)) {
            $viewDefinition .= '&secure=true';
        }

        return ZMBeanUtils::getBean($viewDefinition);
    }

    /**
     * Get the form bean (if any) for this request.
     *
     * @return ZMView The actual view to be used to render the response.
     */
    public function getFormBean() {
        if (null == $this->formBean_ && null !== ($mapping = ZMUrlMapper::instance()->findMapping($this->id_))) {
            if (null !== $mapping['formDefinition']) {
                $this->formBean_ =  ZMBeanUtils::getBean($mapping['formDefinition'].(false === strpos($viewInfo['viewDefinition'], '#') ? '#' : '&').'formId='.$mapping['formId']);
                if ($this->formBean_ instanceof ZMFormBean) {
                    $this->formBean_->populate();
                } else {
                    ZMBeanUtils::setAll($this->formBean_, ZMRequest::getParameterMap());
                }
            }
        }

        return $this->formBean_;
    }

    /**
     * Validate the given form bean.
     *
     * @param mixed formBean An object.
     * @return ZMView Either the error view (in case of validation errors), or <code>null</code> for success.
     */
    protected function validateFormBean($formBean) {
        if (!$this->validate($formBean->getFormId(), $formBean)) {
            // put form bean in context
            $this->exportGlobal($formBean->getFormId(), $formbean);
            // back to same form
            return $this->findView();
        }
        // all good
        return null;
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
            $req = ZMRequest::getParameterMap();
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
     * Set the current view.
     *
     * @param ZMView view The view or <code>null</code>.
     */
    public function setView($view) {
        $this->view_ = $view;
    }

    /**
     * Get the controller id.
     *
     * @return string The id (page name).
     */
    public function getId() { return $this->id_; }

}

?>
