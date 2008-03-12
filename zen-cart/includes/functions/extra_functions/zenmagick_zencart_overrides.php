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

if (!function_exists('zen_mail')) {

    /**
     * zen_mail wrapper that delegates to either the Zenmagick implementation or the renamed original
     * version of it.
     */
    function zen_mail($toName, $toAddress, $subject, $text, $fromName, $fromAddress, $block=array(), $module='default', $attachments_list='') {
        // uncomment to trace mail calls and figure out module names (ie template names)
        //zm_backtrace('mail: '.$module);

        // use zen_mail_org as fallback for emails without ZenMagick template
        if ('none' != zm_email_formats($module)) {
            // call ZenMagick implementation
            // NOTE: zm_mail will eventually call zen_mail_org to actually send the generated email...
            zm_mail($subject, $module, $block, $toAddress, $toName, $fromAddress, $fromName);
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
        if (!isset($zm_request) || !class_exists('ZMEmailView')) { return zen_build_html_email_from_template_org($template, $args); }
        $view = ZMLoader::make("EmailView", $template, true, $args);
        if (!file_exists($view->getViewFilename()) && function_exists('zen_build_html_email_from_template_org')) {
            // default to zen-cart
            return zen_build_html_email_from_template_org($template, $args);
        }
        $view->setController(ZMRequest::getController());
        $html = $view->generate();
        return $html;
    }

}

?>
