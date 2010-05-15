<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2010 zenmagick.org
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
 * @package org.zenmagick.mvc.controller
 * @version $Id$
 */
class ZMController extends ZMObject {
    private $id_;
    private $view_;
    private $formData_;


    /**
     * Create new instance.
     *
     * @param string id Optional id; default is <code>null</code> to use the request id.
     */
    function __construct($id=null) {
        parent::__construct();
        $this->id_ = $id;
        $this->view_ = null;
        $this->formData_ = null;
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
     * <p><strong>This method should not be overridded!</strong>.</p>
     *
     * @param ZMRequest request The request to process.
     * @return ZMView A <code>ZMView</code> instance or <code>null</code>.
     */
    public function process($request) { 
        // ensure a usable id is set
        $this->id_ = null != $this->id_ ? $this->id_ : $request->getRequestId();

        // check authorization
        ZMSacsManager::instance()->authorize($request, $request->getRequestId(), $request->getUser());

        $enableTransactions = ZMSettings::get('zenmagick.mvc.transactions.enabled', false);

        if ($enableTransactions) {
            ZMRuntime::getDatabase()->beginTransaction();
        }

        ZMEvents::instance()->fireEvent($this, ZMMVCConstants::CONTROLLER_PROCESS_START, array('request' => $request, 'controller' => $this));

        // method independant (pre-)processing
        $this->preProcess($request);

        // default is no view to allow the controller to generate content
        $view = null;

        // session validation
        if ($this->isFormSubmit($request) && null != ($view = $this->validateSession($request))) {
            ZMLogging::instance()->log('session validation failed returning: '.$view, ZMLogging::TRACE);
        }

        // form validation (only if not already error view from session validation...)
        $formData = $this->getFormData($request);
        if (null == $view && null != $formData && $this->isFormSubmit($request)) {
            // move to function
            if (null != ($view = $this->validateFormData($request, $formData))) {
                ZMLogging::instance()->log('validation failed for : '.$formData. '; returning: '.$view, ZMLogging::TRACE);
            }
        }

        if (null == $view) {
            try {
                switch ($request->getMethod()) {
                    case 'GET':
                        $view = $this->processGet($request);
                        break;
                    case 'POST':
                        $view = $this->processPost($request);
                        break;
                    default:
                        throw new ZMException('unsupported request method: ' . $request->getMethod());
                }
            } catch (Exception $e) {
                if ($enableTransactions) {
                    ZMRuntime::getDatabase()->rollback();
                }
                // re-throw
                throw $e;
            }
        }

        if (null != $view) {
            // safe data set via findView() in the controller to avoid losing that to getViewData()
            $initialVars = $view->getVars();

            // set a few default things...
            $view->setVar('request', $request);
            $view->setVar('session', $request->getSession());
            $toolbox = $request->getToolbox();
            $view->setVar('toolbox', $toolbox);

            // custom view data
            $view->setVars($this->getViewData($request));
            // make sure these prevail
            $view->setVars($initialVars);

            // also set individual tools
            $view->setVars($toolbox->getTools());
            if (null != $formData && !array_key_exists($formData->getFormId(), $view->getVars())) {
                // avoid overriding default data set by the controller
                $view->setVar($formData->getFormId(), $formData);
            }

            if (!$view->isValid()) {
                ZMLogging::instance()->log('invalid view: '.$view->getName().', expected: '.$view->getViewFilename(), ZMLogging::WARN);
                $view = $this->findView(ZMSettings::get('zenmagick.mvc.request.missingPage'));
            }
            $this->view_ = $view;
        }

        ZMEvents::instance()->fireEvent($this, ZMMVCConstants::CONTROLLER_PROCESS_END, array('request' => $request, 'controller' => $this, 'view' => $this->view_));

        if ($enableTransactions) {
            ZMRuntime::getDatabase()->commit();
        }

        if ($this->isAjax($request)) {
            $view->setLayout('');
            $view->setContentType('text/plain');
        }

        return $view;
    }


    /**
     * Get general page data.
     *
     * <p>Good to override if a custom controller needs to provide some data for both <em>GET</em> and <em>POST</em>
     * requests.</p>
     *
     * @param ZMRequest request The current request.
     * @return array Some data map.
     */
    public function getViewData($request) {
        return array();
    }

    /**
     * Convenience method for request processing shared by request methods.
     *
     * <p>Despite the name this is called as part of the controllers <code>process($request)</code> method.
     * That ensures that all processing is within the boundaries of a single transaction (if enabled).</p>
     *
     * @param ZMRequest request The request to process.
     */
    public function preProcess($request) {
        // nothing
    }

    /**
     * Check if the current request is an Ajax request.
     *
     * <p>This default implementation will check for a 'X-Requested-With' header. Subclasses are free to
     * extend and override this method for custom Ajax detecting.</p>
     *
     * @param ZMRequest request The request to process.
     * @return boolean <code>true</code> if this request is considered an Ajax request.
     */
    public function isAjax($request) {
        $headers = ZMNetUtils::getAllHeaders();
        return array_key_exists('X-Requested-With', $headers) && 'XMLHttpRequest' == $headers['X-Requested-With'];
    }

    /**
     * Check if this request is a form submit.
     *
     * <p>This default implementation will return <code>true</code> for all <em>POST</em> requests.</p>
     *
     * @param ZMRequest request The request to process.
     * @return boolean <code>true</code> if this is a form submit request.
     */
    public function isFormSubmit($request) {
        return 'POST' == $request->getMethod();
    }

    /**
     * Process a HTTP GET request.
     * 
     * @param ZMRequest request The request to process.
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    public function processGet($request) {
        return $this->findView();
    }


    /**
     * Process a HTTP POST request.
     * 
     * @param ZMRequest request The request to process.
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    public function processPost($request) { return $this->processGet($request); }


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
     * Lookup the appropriate view for the given name.
     *
     * @param string id The controller id or <code>null</code> to return to the current page.
     * @param array data Optional model data; default is an empty array.
     * @param array parameter Optional map of name/value pairs to further configure the view; default is <code>null</code>.
     * @return ZMView The actual view to be used to render the response.
     */
    public function findView($id=null, $data=array(), $parameter=null) {
        $view = ZMUrlManager::instance()->findView($this->id_, $id, $parameter);

        // ensure secure option is set if required
        if (ZMSacsManager::instance()->requiresSecurity($this->id_)) {
            $view->setSecure(true);
        }

        $view->setVars($data);
        $view->setController($this);
        $this->view_ = $view;
        return $view;
    }

    /**
     * Get the form data object (if any) for this request.
     *
     * @param ZMRequest request The request to process.
     * @return ZMObject An object instance or <code>null</code>
     */
    public function getFormData($request) {
        if (null == $this->formData_ && null !== ($mapping = ZMUrlManager::instance()->findMapping($this->id_))) {
            if (array_key_exists('form', $mapping)) {
                $this->formData_ =  ZMBeanUtils::getBean($mapping['form'].(false === strpos($mapping['view'], '#') ? '#' : '&').'formId='.$mapping['formId']);
                if ($this->formData_ instanceof ZMFormData) {
                    $this->formData_->populate($request);
                } else {
                    $this->formData_ = ZMBeanUtils::setAll($this->formData_, $request->getParameterMap());
                }
            }
        }

        return $this->formData_;
    }

    /**
     * Validate session token.
     *
     * @param ZMRequest request The request to process.
     * @return ZMView Either the error view (in case of validation errors), or <code>null</code> for success.
     */
    protected function validateSession($request) {
        return $request->validateSessionToken() ? null : $this->findView();
    }

    /**
     * Validate the given form bean.
     *
     * @param ZMRequest request The request to process.
     * @param mixed formData An object.
     * @return ZMView Either the error view (in case of validation errors), or <code>null</code> for success.
     */
    protected function validateFormData($request, $formData) {
        if (!$this->validate($request, $formData->getFormId(), $formData)) {
            // back to same form
            $view = $this->findView();
            // put form bean in context
            $view->setVar($formData->getFormId(), $formData);
            return $view;
        }

        // all good
        return null;
    }

    /**
     * Validate the current request using the given rule id.
     *
     * @param ZMRequest request The request to process.
     * @param string formId The <code>ZMRuleSet</code> id.
     * @param mixed formData A map, (bean) object instance or <code>null</code> for all current request parameter.
     * @return boolean <code>true</code> if the validation was successful, <code>false</code> if not.
     */
    public function validate($request, $formId, $formData=null) {
        if (null === $formData) {
            $formData = $request->getParameterMap();
        }

        if (!ZMValidator::instance()->hasRuleSet($formId)) {
            return true;
        }

        $valid = ZMValidator::instance()->validate($request, $formData, $formId);
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
     * Set the controller id.
     *
     * @param string id The id (page name).
     */
    public function setId($id) { throw new ZMException('deprecated'); }

    /**
     * Get the controller id.
     *
     * @return string The id (page name).
     */
    public function getId() {throw new ZMException('deprecated'); }

}
