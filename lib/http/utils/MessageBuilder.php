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
namespace zenmagick\http\utils;

use Swift_Message;
use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;
use zenmagick\base\events\Event;
use zenmagick\http\view\View;

/**
 * Message builder for emails.
 *
 * <p>The code in this service assumes that email templates are located in a separate
 *  <em>emails</em> template folder.<p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class MessageBuilder extends ZMObject {
    private $view;


    /**
     * Set the view to render email content.
     *
     * @param View view The view.
     */
    public function setView(View $view) {
        $this->view = $view;
    }

    /**
     * Get the render viev.
     *
     * @return View The view.
     */
    public function getView() {
        return $this->view;
    }

    /**
     * Get available formats.
     *
     * @param string template The full template name.
     * @return array Map of available formats with the template type (file extension) as value.
     */
    public function getFormatsForTemplate($template) {
        $resourceResolver = $this->view->getResourceResolver();
        $formats = array();

        foreach ($resourceResolver->find('views/emails', '/'.$template.'/', View::TEMPLATE) as $template) {
            $tokens = explode('.', $template);
            if (3 == count($tokens)) {
                list($template, $format, $type) = $tokens;
                $formats[$format] = $type;
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
     * @request Request request The current request.
     * @param boolean html Indicate whether to create <em>HTML</em> or <em>text</em> content; default is <code>false</code>.
     * @param array context Optional context parameter; default is an empty array.
     * @return strintg The content.
     * @deprecated use createMessage(...) instead
     */
    public function createContents($template, $html=false, $request, $context=array()) {
        $formats = $this->getFormatsForTemplate($template);
        if (0 == count($formats)) {
            // no template found
            Runtime::getLogging()->error('no template found for email: '.$template);
            return '';
        }

        // pick format, fallback to text
        $format = 'text';
        if ($html && array_key_exists('html', $formats)) {
            $format = 'html';
        }

        // set up view
        $this->view->setTemplate('views/emails/'.$template.'.'.$format.'.'.$formats[$format]);
        // disable layout for now
        $this->view->setLayout(null);

        // make sure these prevail
        $this->view->setVariables($context);

        // create contents
        return $this->view->generate($request);
    }

    /**
     * Create a message for the given template and parameters.
     *
     * <p>If <em>HTML</em> is requested but not available, <em>text</em> will be returned.</p>
     *
     * @param string template The template name.
     * @request Request request The current request.
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

        // always have text body
        $textBody = $this->createContents($template, false, $request, $context);
        $message = $this->getMessage('', $textBody);
        if ($html) {
            $formats = $this->getFormatsForTemplate($template);
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
        return Swift_Message::newInstance($subject, $body, $contentType, $charset);
    }

}
