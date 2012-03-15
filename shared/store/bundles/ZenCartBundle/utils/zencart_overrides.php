<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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

use zenmagick\base\Runtime;

if (!function_exists('zen_mail')) {

    /**
     * zen_mail wrapper that delegates to either the Zenmagick implementation or the renamed original
     * version of it.
     */
    function zen_mail($toName, $toAddress, $subject, $text, $fromName, $fromAddress, $block=array(), $module='default', $attachments_list='') {
        // uncomment to trace mail calls and figure out module names (ie template names)
        //Runtime::getLogging()->trace('mail: '.$module);

        $request = Runtime::getContainer()->get('request');
        $container = Runtime::getContainer();
        $messageBuilder = $container->get('messageBuilder');

        // use zen_mail_org as fallback for emails without ZenMagick template
        $formats = $messageBuilder->getFormatsForTemplate($module);
        if (0 < count($formats) && Runtime::getSettings()->get('isEnableZMThemes', true)) {
            $block['text_msg'] = $text;
            $container = Runtime::getContainer();
            $message = $container->get('messageBuilder')->createMessage($module, true, $request, $block);
            $message->setSubject($subject)->setTo($toAddress, $toName)->setFrom($fromAddress, $fromName);
            $container->get('mailer')->send($message);
        } else {
            // call renamed original function
            zen_mail_org($toName, $toAddress, $subject, $text, $fromName, $fromAddress, $block, $module, $attachments_list);
        }
    }

}

if (!function_exists('zen_build_html_email_from_template')) {

    /**
     * zen_build_html_email_from_template wrapper that delegates to either the Zenmagick implementation or the renamed original
     * version of it.
     */
    function zen_build_html_email_from_template($template, $args=array()) {
        $container = Runtime::getContainer();
        $messageBuilder = $container->get('messageBuilder');
        if (!Runtime::getSettings()->get('isEnableZMThemes', true)) {
            return zen_build_html_email_from_template_org($template, $args);
        }
        $request = Runtime::getContainer()->get('request');
        return $messageBuilder->createContents($template, true, $request, $request->get('ZM_EMAIL_CONTEXT'));
    }

}
