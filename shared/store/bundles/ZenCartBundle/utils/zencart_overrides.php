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
use zenmagick\base\ZMException;


if (!function_exists('zen_href_link')) {

    /**
     * zen_href_link wrapper that delegates to the Zenmagick implementation.
     *
     * This function needs to work for 4 use cases.
     *
     * 1. zencart template storefront
     * 2. zenmagick themed storefront
     * 3. zenmagick admin with zencart admin integration via zc_admin bundle template and the zencart admin page name (minus .php) as zpid parameter.
     * 4. zenmagick admin integration using zenmagick native mappings using the zencart admin page name (minus .php)  as the request id.
     */
    function zen_href_link($page='', $params='', $transport='NONSSL', $addSessionId=true, $seo=true, $isStatic=false, $useContext=true) {
        $useNativeHrefLink = Runtime::getSettings()->get('apps.store.zencart.useNativeHrefLink', false);
        if ($useNativeHrefLink && class_exists('ZMStoreDefaultUrlRewriter') && !Runtime::isContextMatch('admin')) { // 1 or 2
            return ZMStoreDefaultUrlRewriter::furl($page, $params, $transport, $addSessionId, $seo, $isStatic, $useContext);
        } else if (Runtime::isContextMatch('admin')) { // 3 or 4
            $request = Runtime::getContainer()->get('request');
            parse_str($params, $tmp);
            $page = str_replace('.php', '', $page);
            $requestId = $request->getRequestId() == 'zc_admin' ? 'zc_admin' : $page;
            $params = '';
            if ($requestId == 'zc_admin') { // 4
                $params = 'zpid='.$page.'&';
            }
            unset($tmp['zpid']);
            unset($tmp['rid']);
            $params .= http_build_query($tmp);
            return $request->url($requestId, $params);
        } else if (function_exists('zen_href_link_DISABLED')) { // 1
            // just in case...
            return zen_href_link_DISABLED($page, $params, $transport, $addSessionId, $seo, $isStatic, $useContext);
        } else {
            throw new ZMException("can't find zen_href_link implementation");
        }
    }

}

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
