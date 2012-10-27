<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2012 zenmagick.org
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

use ZenMagick\Base\Beans;
use ZenMagick\Base\Runtime;
use ZenMagick\Base\ZMException;
use ZenMagick\Http\Request;
use ZenMagick\Http\Forms\Form;
use ZenMagick\Http\View\TemplateView;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Request controller base class.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ZMController extends Controller {
    protected $messageService;
    private $requestId_;
    private $isAjax_;
    private $method_;
    private $view_;
    private $formData_;


    /**
     * Create new instance.
     *
     * @param string requestId Optional requestId; default is <code>null</code> to use the request id.
     */
    function __construct($requestId=null) {
        $this->requestId_ = $requestId;
        $this->view_ = null;
        $this->method_ = null;
        $this->formData_ = null;
        // a little bit of convenience
        $this->messageService = Runtime::getContainer()->get('session')->getFlashBag();
    }


    /**
     * Init view vars.
     *
     * @param View view The view to init.
     * @param ZenMagick\Http\Request request The current request.
     * @param mixed formData Optional form data; default is <code>null</code>.
     */
    public function initViewVars($view, $request, $formData=null) {
        if (!($view instanceof TemplateView)) {
            return;
        }

        // safe data set via findView() in the controller to avoid losing that to getViewData()
        $initialVars = $view->getVariables();

        // custom view data
        $view->setVariables($this->getViewData($request));

        if (null != $formData && !array_key_exists($formData->getFormId(), $view->getVariables())) {
            // avoid overriding default data set by the controller
            $view->setVariable($formData->getFormId(), $formData);
        }

        // make sure these prevail
        $view->setVariables($initialVars);
    }

    /**
     * An alias for processAction()
     * @see self::processAction
     */
    public function process(Request $request) {
        return $this->processAction($request);
    }
    /**
     * Process a HTTP request.
     *
     * <p>Supported request methods are <code>GET</code> and <code>POST</code>.</p>
     * <p><strong>This method should not be overridded!</strong>.</p>
     *
     * @param ZenMagick\Http\Request request The request to process.
     * @return View A <code>View</code> instance or <code>null</code>.
     */
    public function processAction(Request $request) {
        // ensure a usable id is set
        $this->requestId_ = null != $this->requestId_ ? $this->requestId_ : $request->getRequestId();
        $this->isAjax_ = $request->isXmlHttpRequest();

        $settingsService = $this->container->get('settingsService');

        // method independant (pre-)processing
        $this->preProcess($request);

        // default is no view to allow the controller to generate content
        $view = null;

        // form validation (only if not already error view from session validation...)
        $formData = $this->getFormData($request);
        if (null == $view && null != $formData && $this->isFormSubmit($request)) {
            // move to function
            if (null != ($view = $this->validateFormData($request, $formData))) {
                $this->get('logger')->debug('validation failed for : '.$formData. '; returning: '.$view);
            }
        }

        if (null == $view) {
            $method = null != $this->getMethod() ? $this->getMethod() : $request->getMethod();
            switch ($method) {
                case 'HEAD':
                    $view = $this->processHead($request);
                    break;
                case 'GET':
                    $view = $this->processGet($request);
                    break;
                case 'POST':
                    $view = $this->processPost($request);
                    break;
                default:
                    //return call_user_func_array($target, $margs);
                    if (method_exists($this, $method) || in_array($method, $this->getAttachedMethods())) {
                        // (re-)check on method level if mapping exists
                        $methodRequestId = $request->getRequestId().'#'.$method;
                        $view = $this->$method($request);
                        break;
                    }
                    throw new ZMException('unsupported method: ' . $method);
            }
        }

        if ($view instanceof Response) {
            return $view;
        }
        if (null != $view) {
            $this->initViewVars($view, $request, $formData);
            if (!$view->isValid()) {
                Runtime::getLogging()->warn('invalid view: '.$view->getTemplate().', expected: '.$view->getViewFilename());
                $view = $this->findView($settingsService->get('zenmagick.mvc.request.missingPage'));
                $this->initViewVars($view, $request, $formData);
            }
            $this->view_ = $view;
        }

        if ($this->isAjax_) {
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
     * @param ZenMagick\Http\Request request The current request.
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
     * @param ZenMagick\Http\Request request The request to process.
     */
    public function preProcess($request) {
        // nothing
    }

    /**
     * Check if this request is a form submit.
     *
     * <p>This default implementation will return <code>true</code> for all <em>POST</em> requests.</p>
     *
     * @param ZenMagick\Http\Request request The request to process.
     * @return boolean <code>true</code> if this is a form submit request.
     */
    public function isFormSubmit($request) {
        return 'POST' == $request->getMethod();
    }

    /**
     * Process a HTTP HEAD request.
     *
     * @param ZenMagick\Http\Request request The request to process.
     * @return View A <code>View</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    public function processHead($request) {
        return null;
    }

    /**
     * Process a HTTP GET request.
     *
     * @param ZenMagick\Http\Request request The request to process.
     * @return View A <code>View</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    public function processGet($request) {
        return $this->findView();
    }


    /**
     * Process a HTTP POST request.
     *
     * @param ZenMagick\Http\Request request The request to process.
     * @return View A <code>View</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    public function processPost($request) { return $this->processGet($request); }

    /**
     * Set the response content type.
     *
     * @param string type The content type.
     * @param string charset Optional charset; default is utf-8; <code>null</code> will omit the charset part.
     * @todo migrate to Symfony\Component\HttpFoundation\Response and drop this
     */
    public static function setContentType($type, $charset="utf-8") {
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
     * @return View The actual view to be used to render the response.
     */
    public function findView($id=null, $data=array(), $parameter=null) {
        if ($this->isAjax_) {
            $id = 'ajax_'.$id;
        }

        // TODO: doh!
        $request = $this->container->get('request');
        $view = $this->container->get('routeResolver')->getViewForId($id, $request, $data);
        Beans::setAll($view, (array)$parameter);

        $view->setVariables($data);
        $this->view_ = $view;
        return $view;
    }

    /**
     * Get the form data object (if any) for this request.
     *
     * @param ZenMagick\Http\Request request The request to process.
     * @param string formDef Optional form container definition; default is <code>null</code> to use the global mapping.
     * @param string formId Optional form id; default is <code>null</code> to use the global mapping.
     * @return ZMObject An object instance or <code>null</code>
     */
    public function getFormData($request, $formDef=null, $formId=null) {
        $routeResolver = $this->container->get('routeResolver');

        if (null != ($route = $routeResolver->getRouteForUri($request->getPathInfo()))) {
            if (null != ($options = $route->getOptions()) && array_key_exists('form', $options)) {
                $this->formData_ = Beans::getBean($options['form']);
                if ($this->formData_ instanceof Form) {
                    $this->formData_->populate($request);
                } else {
                    $this->formData_ = Beans::setAll($this->formData_, $request->getParameterMap());
                }
            }
        }

        return $this->formData_;
    }

    /**
     * Validate the given form bean.
     *
     * @param ZenMagick\Http\Request request The request to process.
     * @param mixed formData An object.
     * @return View Either the error view (in case of validation errors), or <code>null</code> for success.
     */
    protected function validateFormData($request, $formData) {
        if (!$this->validate($request, $formData->getFormId(), $formData)) {
            // back to same form
            $view = $this->findView();
            // put form bean in context
            $view->setVariable($formData->getFormId(), $formData);
            return $view;
        }

        // all good
        return null;
    }

    /**
     * Validate the current request using the given rule id.
     *
     * @param ZenMagick\Http\Request request The request to process.
     * @param string formId The <code>ZMRuleSet</code> id.
     * @param mixed formData A map, (bean) object instance or <code>null</code> for all current request parameter.
     * @return boolean <code>true</code> if the validation was successful, <code>false</code> if not.
     */
    public function validate($request, $formId, $formData=null) {
        if (null === $formData) {
            $formData = $request->getParameterMap();
        }

        $validator = $this->container->get('zmvalidator');
        if (!$validator->hasRuleSet($formId)) {
            return true;
        }

        $valid = $validator->validate($request, $formData, $formId);
        if (!$valid) {
            foreach ($validator->getMessages() as $field => $fieldMessages) {
                foreach ($fieldMessages as $msg) {
                    $this->messageService->error($msg, $field);
                }
            }
        }

        return $valid;
    }

    /**
     * Get the current view.
     *
     * @return View The view or <code>null</code>.
     * @deprecated Not used at all
     */
    public function getView() {
        return $this->view_;
    }

    /**
     * Set the current view.
     *
     * @param View view The view or <code>null</code>.
     * @deprecated Not used at all
     */
    public function setView($view) {
        $this->view_ = $view;
    }

    /**
     * Get the method to be used for processing.
     *
     * @return string Either a method name or <code>null</code> to pick the method based on the request method (GET, POST, etc).
     */
    public function getMethod() {
        return $this->method_;
    }

    /**
     * Set the method to be used for processing.
     *
     * @param string method The method name.
     */
    public function setMethod($method) {
        $this->method_ = $method;
    }

    /**
     * Get the user (if any) for authentication.
     *
     * <p>Creation of the user object is delegated to the configured <code>ZenMagick\Http\Session\UserFactory</code> instance.
     * The factory may be configured as bean defintion via the setting 'zenmagick.http.session.userFactory'.</p>
     *
     * @return mixed A user/credentials object. Default is <code>null</code>.
     */
    public function getUser() {
        if ($this->container->has('userFactory') && null != ($userFactory = $this->container->get('userFactory'))) {
            return $userFactory->getUser($this->container->get('request'));
        }
        return null;
    }

    /**
     * Deal with demo/non-live user.
     *
     * <p>Will create a message that the requested functionallity is not availale for demo users.</p>
     *
     * @return boolean <code>true</code> if the current user is a demo user.
     *
     * @todo this should be dealt with in sacs
     */
    public function handleDemo() {
        if (!$this->getUser()->isLive()) {
            $this->messageService->warn(_zm('Sorry, the action you tried to excute is not available to demo users'));
            return true;
        }
        return false;
    }

}
