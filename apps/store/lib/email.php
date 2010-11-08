<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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
     * Send email.
     *
     * <p>Contents generation is delegated to a <code>ZMEmails</code>.</p>
     *
     * <p>The environment will be se same as for the actual HTML response view. This is done
     * by attaching the current controller to the view.</p>
     *
     * @param string subject The subject.
     * @param string template The email template name.
     * @param array context Additional stuff to be made available to the template.
     * @param string toEmail The recipients email address.
     * @param string toName Optional recipients name; default is <code>$toEmail</code>.
     * @param string fromEmail Optional sender email address; default is <code>storeEmailFrom</code>.
     * @param string fromName Optional sender name; default is <code>$fromEmail</code>.
     * @param string attachment Optional <strong>single</strong> file attachment.
     * @package zenmagick.store.shared.email
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

        // need that
        $request = ZMRequest::instance();

        // event to allow additions to context or view or...
        $args = ZMEvents::instance()->fireEvent(null, Events::GENERATE_EMAIL, array('template' => $template, 'context' => $context, 'request' => $request));
        $context = $args['context'];
        // save context for legacy HTML generation...
        $request->set('ZM_EMAIL_CONTEXT', $context);

        // generate text content if text version exists
        $formats = ZMEmails::instance()->getFormatsForTemplate($template, ZMRequest::instance());
        if (in_array('text', $formats)) {
            $text = ZMEmails::instance()->createContents($template, false, $request, $context);
        } else {
            $text = null;
        }

        // call actual mail function; the name must match the one used in the installation patch
        $mailFunc = function_exists('zen_mail_org') ? 'zen_mail_org' : 'zen_mail';
        $mailFunc($toName, $toEmail, $subject, $text, $fromName, $fromEmail, $context, $template, $attparam);
    }
