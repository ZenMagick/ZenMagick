<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2011 zenmagick.org
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
namespace zenmagick\http\utils;

use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;
use zenmagick\base\events\Event;

/**
 * Message builder for emails.
 *
 * <p>The code in this service assumes that email templates are located in a separate
 *  <em>emails</em> template folder.<p>
 * <p>The view used to resolve template names and create content is accquired via
 *  <code>ZMUrlManager::instance()->findView(null, 'emails')</code>, so if you need special settings, etc.
 *  it is possible to configure that via the regular url mappings.</p>
 *
 * <p>The default view view id is <em>emails</em>.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package zenmagick.http.utils
 */
class MessageBuilder extends ZMObject {
    private $viewViewId_;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->viewViewId_ = 'emails';
    }

    /**
     * Destruct instance.
     */
    public function __destruct() {
        parent::__destruct();
    }


    /**
     * Set the view id to be used to lookup the view to format email content.
     *
     * @param string viewId The new view id.
     */
    public function setViewViewId($viewId) {
        $this->viewViewId_ = $viewId;
    }

    /**
     * Get the view id to be used to lookup the view to format email content.
     *
     * @return string The view id.
     */
    public function getViewViewId() {
        return $this->viewViewId_;
    }

    /**
     * Get available formats.
     *
     * @param string template The template name.
     * @request ZMRequest request The current request.
     * @return array List of available formats.
     */
    public function getFormatsForTemplate($template, $request) {
        // use configured/default view for viewId 'emails'
        $view = \ZMUrlManager::instance()->findView(null, $this->viewViewId_);
        // check for html/text versions
        $templateBase = 'emails'.DIRECTORY_SEPARATOR.$template;
        $formats = array();
        foreach (array('html', 'text') as $format) {
            $view->setTemplate($templateBase.'.'.$format);
            if ($view->isValid($request)) {
                $formats[] = $format;
            }
        }

        return $formats;
    }

    /**
     * Generate the contents for the given template and parameters.
     *
     * <p>If <em>HTML</em> is requested but not available, <em>text</em> will be returned.</p>
     *
     * @param string template The template name.
     * @request ZMRequest request The current request.
     * @param boolean html Indicate whether to create <em>HTML</em> or <em>text</em> content; default is <code>false</code>.
     * @param array context Optional context parameter; default is an empty array.
     * @return strintg The content.
     * @deprecated use createMessage(...) instead
     */
    public function createContents($template, $html=false, $request, $context=array()) {
        $formats = $this->getFormatsForTemplate($template, $request);
        if (0 == count($formats)) {
            // no template found
            Runtime::getLogging()->error('no template found for email: '.$template);
            return '';
        }

        // pick format, fallback to text
        $format = 'text';
        if ($html && in_array('html', $formats)) {
            $format = 'html';
        }

        // set up view
        $view = \ZMUrlManager::instance()->findView(null, $this->viewViewId_);
        $view->setTemplate('emails'.DIRECTORY_SEPARATOR.$template.'.'.$format);
        // disable layout for now
        $view->setLayout(null);

        // make sure these prevail
        $view->setVarables($context);

        // create contents
        return $view->generate($request);
    }

    /**
     * Create a message for the given template and parameters.
     *
     * <p>If <em>HTML</em> is requested but not available, <em>text</em> will be returned.</p>
     *
     * @param string template The template name.
     * @request ZMRequest request The current request.
     * @param boolean html Indicate whether to create <em>HTML</em> or <em>text</em> content; default is <code>false</code>.
     * @param array context Optional context parameter; default is an empty array.
     * @return mixed The message.
     */
    public function createMessage($template, $html=false, $request, $context=array()) {
        // event to allow additions to context or view or...
        $args = array('template' => $template, 'request' => $request, 'context' => $context);
        $event = new Event(null, $args);
        $this->container->get('eventDispatcher')->dispatch('generate_email', $event);
        $context = $event->get('context');
        // save context for legacy HTML generation...
        $request->set('ZM_EMAIL_CONTEXT', $context);

        $textBody = $this->createContents($template, false, $request, $context);
        $message = $this->getMessage('', $textBody);
        if ($html) {
            $formats = $this->getFormatsForTemplate($template, $request);
            if (in_array('html', $formats)) {
                $message->addPart($this->createContents($template, true, $request, $context), 'text/html');
            }
        }

        // TODO: eventually remove: attach some additional things we might need for the legacy code
        $message->request = $request;
        $message->template = $template;
        $message->context = $context;
        return $message;
    }

    /**
     * Get message.
     *
     * @param string subject Optional subject; default is an empty string.
     * @param string body Optional body text; default is an empty string.
     * @param string contentType Optional content type; default is <code>null</code>.
     * @param string charset Optional character set; default is <code>utf-8</code>>
     * @return mixed A message obect.
     */
    public function getMessage($subject='', $body='', $contentType=null, $charset='utf-8') {
        return \Swift_Message::newInstance($subject, $body, $contentType, $charset);
    }

}
