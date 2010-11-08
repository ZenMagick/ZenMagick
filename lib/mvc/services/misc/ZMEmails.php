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
 * Useful stuff to create email contents.
 *
 * <p>The code in this service assumes that email templates are located in a separate
 *  <em>emails</em> template folder.<p>
 * <p>The view used to resolve template names and create content is accquired via
 *  <code>ZMUrlManager::instance()->findView(null, 'emails')</code>, so if you need special settings, etc.
 *  it is possible to configure that via the regular url mappings.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.mvc.services.misc
 */
class ZMEmails extends ZMObject {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Get instance.
     */
    public static function instance() {
        return ZMObject::singleton('Emails');
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
        $view = ZMUrlManager::instance()->findView(null, 'emails');
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
     */
    public function createContents($template, $html=false, $request, $context=array()) {
        $formats = $this->getFormatsForTemplate($template, $request);
        if (0 == count($formats)) {
            // no template found
            ZMLogging::instance()->log('no template found for email: '.$template, ZMLogging::ERROR);
            return '';
        }

        // pick format, fallback to text
        $format = 'text';
        if ($html && in_array('html', $formats)) {
            $format = 'html';
        }

        // set up view
        $view = ZMUrlManager::instance()->findView(null, 'emails');
        $view->setTemplate('emails'.DIRECTORY_SEPARATOR.$template.'.'.$format);
        // disable layout for now
        $view->setLayout(null);

        // set a few default things...
        $view->setVar('request', $request);
        $view->setVar('session', $request->getSession());
        $toolbox = $request->getToolbox();
        $view->setVar('toolbox', $toolbox);

        // also set individual tools
        $view->setVars($toolbox->getTools());

        // make sure these prevail
        $view->setVars($context);

        // create contents
        return $view->generate($request);
    }

}
