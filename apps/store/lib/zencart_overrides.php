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
if (!function_exists('zen_date_raw')) {

    /**
     * Convert UI date into a <em>raw date format</em> that zen-cart
     * understands.
     *
     * <p>This generic implementation will work as long as <code>UI_DATE_FORMAT</code>
     * is defined.<br>
     * The function will honour <code>DD</code>, <code>MM</code>, <code>CC</code>, <code>YY</code>
     * and <code>YYYY</code> in the format.</p>
     *
     * <p><strong>NOTE:</strong> The format is *not* case sensitive.</p>
     *
     * @package zenmagick.store.sf.override
     * @param string date A date (usually part of a form submit by the user).
     * @param boolean reverse If <code>true</code>, the returned data will be reversed.
     * @return string The provided date converted into the format <code>YYYYDDMM</code> or <code>MMDDYYYY</code>, respectivley.
     */
    function zen_date_raw($date, $reverse=false) {
        $da = ZMTools::parseDateString($date, UI_DATE_FORMAT);
        $raw = $reverse ? $da['mm'].$da['dd'].$da['cc'].$da['yy'] : $da['cc'].$da['yy'].$da['mm'].$da['dd'];
        return $raw;
    }

}

if (!function_exists('zen_href_link')) {

    /**
     * zen_href_link wrapper that delegates to the Zenmagick implementation.
     *
     * @package zenmagick.store.sf.override
     */
    function zen_href_link($page='', $params='', $transport='NONSSL', $addSessionId=true, $seo=true, $isStatic=false, $useContext=true) {
        if (class_exists('ZMStoreDefaultSeoRewriter') && (!defined('IS_ADMIN_FLAG') || !IS_ADMIN_FLAG)) {
            return ZMStoreDefaultSeoRewriter::furl($page, $params, $transport, $addSessionId, $seo, $isStatic, $useContext);
        } else if (function_exists('zen_href_link_DISABLED')) {
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
     *
     * @package zenmagick.store.sf.override
     */
    function zen_mail($toName, $toAddress, $subject, $text, $fromName, $fromAddress, $block=array(), $module='default', $attachments_list='') {
        // uncomment to trace mail calls and figure out module names (ie template names)
        //ZMLogging::instance()->trace('mail: '.$module);

        // use zen_mail_org as fallback for emails without ZenMagick template
        $formats = ZMEmails::instance()->getFormatsForTemplate($module, ZMRequest::instance());
        if (0 < count($formats)) {
            // call ZenMagick implementation
            // NOTE: zm_mail will eventually call zen_mail_org to actually send the generated email...
            
            // preserve original text
            $block['text_msg'] = $text;
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
     *
     * @package zenmagick.store.sf.override
     */
    function zen_build_html_email_from_template($template, $args=array()) {
        if (!class_exists('ZMEmails')) { 
            return zen_build_html_email_from_template_org($template, $args);
        }
        $request = ZMRequest::instance();
        return ZMEmails::instance()->createContents($template, true, $request, $request->get('ZM_EMAIL_CONTEXT'));
    }

}
