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
 *
 * $Id$
 */
?>
<?php


    /**
     * Create email contents based on the given parameter.
     *
     * @package org.zenmagick.email
     * @param string template The template.
     * @param boolean asHTML Flag whether HTML or text version should be generated.
     * @param array context The context data to be made available for the email template.
     * @return string The email contents.
     */
    function zm_get_email_contents($template, $asHTML=true, $context=array()) {
        $view = ZMLoader::make("EmailView", $template, $asHTML, $context);
        $view->setController(ZMRequest::getController());
        return  $view->generate();
    }


    /**
     * Check in which format a given email template exists.
     *
     * @package org.zenmagick.email
     * @param string template The email template name.
     * @return string Valid return strings are: <code>html</code>, <code>text</code>, <code>both</code> or <code>none</code>.
     */
    function zm_email_formats($template) {
        $htmlView = ZMLoader::make("EmailView", $template, true);
        $textView = ZMLoader::make("EmailView", $template, false);
        if ($htmlView->isValid() && $textView->isValid()) {
            return "both";
        } else if (!file_exists($htmlView->getViewFilename()) && !file_exists($textView->getViewFilename())) {
            return "none";
        } else if (file_exists($htmlView->getViewFilename())) {
            return "html";
        } else {
            return "text";
        }
    }


    /**
     * Send email.
     *
     * <p>Contents generation is delegated to a <code>ZMEmailView</code>.</p>
     *
     * <p>The environment will be se same as for the actual HTML response view. This is done
     * by attaching the current controller to the view.</p>
     *
     * @package org.zenmagick.email
     * @param string subject The subject.
     * @param string template The email template name.
     * @param array context Additional stuff to be made available to the template.
     * @param string toEmail The recipients email address.
     * @param string toName Optional recipients name; default is <code>$toEmail</code>.
     * @param string fromEmail Optional sender email address; default is <code>storeEmailFrom</code>.
     * @param string fromName Optional sender name; default is <code>$fromEmail</code>.
     * @param string attachment Optional <strong>single</strong> file attachment.
     */
    function zm_mail($subject, $template, $context, $toEmail, $toName=null, $fromEmail=null, $fromName=null, $attachment=null) {
        // some argument cleanup
        $toName = null !== $toName ? $toName : $toEmail;
        $fromEmail = null !== $fromEmail ? $fromEmail : ZMSettings::get('storeEmailFrom');
        $fromName = null !== $fromName ? $fromName : $fromEmail;
        // this is sooo weiyrd!
        $attparam = '';
        if (null !== $attachment) {
            $attparam = array('file' => $attachment);
        }

        $formats = zm_email_formats($template);
        $hasTextTemplate = 'text' == $formats || 'both' == $formats;

        // use text format unless only HTML available
        $view = ZMLoader::make("EmailView", $template, !$hasTextTemplate, $context);
        $view->setController(ZMRequest::getController());
        $text = $view->generate();

        // call actual mail function; the name corresponds to the one used in the installation patch
        $mailFunc = function_exists('zen_mail_org') ? 'zen_mail_org' : 'zen_mail';
        $mailFunc($toName, $toEmail, $subject, $text, $fromName, $fromEmail, $context, $template, $attparam);
    }

?>
