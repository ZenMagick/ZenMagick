<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
if (!defined('DATE_RSS')) { define(DATE_RSS, "D, d M Y H:i:s T"); }


    /**
     * Parse RSS date.
     * 
     * @package net.radebatz.zenmagick.misc
     * @param string date The date.
     * @return array An array with 3 elements in the order [day] [month] [year].
    */
    function zm_parse_rss_date($date) {
        ereg("[a-zA-Z]+, ([0-3]?[0-9]) ([a-zA-Z]+) ([0-9]{2,4}) .*", $date, $regs);
        return $regs[1].'/'.$regs[2].'/'.$regs[3];
    } 

    /**
     * Convert date to RSS date format.
     * 
     * @package net.radebatz.zenmagick.misc
     * @param string date The date or <code>null</code>.
     * @return string A date string formatted according to RSS date rules.
    */
    function zm_mk_rss_date($date=null) {
        if (null === $date) {
            return date(DATE_RSS);
        }

        $date = strtotime($date);
        return date(DATE_RSS, $date);
    } 


    /**
     * Checks, if the current page is a checkout page.
     * 
     * @package net.radebatz.zenmagick.misc
     * @param bool includeCart If <code>true</code>, the shopping cart is considered a checkout page, too; (defaults to <code>true</code>)
     * @return bool <code>true</code> if the current page is a checkout page.
     */
    function zm_is_checkout_page($includeCart=true) {
    global $zm_request;

        $page = $zm_request->getPageName();
        return ($includeCard && 'shopping_cart' == $page) || !(false === strpos($page, 'checkout_'));
    }

    /**
     * Parse a date according to a given format.
     *
     * <p>This function will honour <code>DD</code>, <code>MM</code>, <code>CC</code>, <code>YY</code>
     * and <code>YYYY</code> in the format.</p>
     *
     * <p><strong>NOTE:</strong> The format is *not* case sensitive.</p>
     *
     * @package net.radebatz.zenmagick.misc
     * @param string date A date (usually provided by the user).
     * @param string format The date format
     * @param bool reverse If <code>true</code>, the returned data will be reversed.
     * @return array The individual date components in the order dd, mm, cc, yy.
     */
    function zm_parse_date($date, $format) {
        $dd = '??';
        $mm = '??';
        $cc = '??';
        $yy = '??';

        $format = strtoupper($format);

        // parse
        $dpos = strpos($format, 'DD');
        if (false !== $dpos) {
            $dd = substr($date, $dpos, 2);
        }
        $mpos = strpos($format, 'MM');
        if (false !== $mpos) {
            $mm = substr($date, $mpos, 2);
        }
        $cpos = strpos($format, 'CC');
        if (false !== $cpos) {
            $cc = substr($date, $cpos, 2);
        }
        $cypos = strpos($format, 'YYYY');
        if (false !== $cypos) {
            $cc = substr($date, $cypos, 2);
            $yy = substr($date, $cypos+2, 2);
        } else {
            $ypos = strpos($format, 'YY');
            if (false !== $ypos) {
                $yy = substr($date, $ypos, 2);
            }
        }

        return array($dd, $mm, $cc, $yy);
    }


    /**
     * Encode XML control characters.
     *
     * @package net.radebatz.zenmagick.misc
     * @param string s The input string.
     * @return string The encoded string.
     */
    function zm_xml_encode($s) {
        $encoding = array();
        $encoding['<'] = "&lt;";
        $encoding['>'] = "&gt;";
        $encoding['&'] = "&amp;";

        foreach ($encoding as $char => $entity) {
            $s = str_replace($char, $entity, $s);
        }

        return $s;
    }


    /**
     * Check in which format a given email template exists.
     *
     * @package net.radebatz.zenmagick.misc
     * @param string template The email template name.
     * @return string Valid return strings are: <code>html</code>, <code>text</code>, <code>both</code> or <code>none</code>.
     */
    function zm_email_formats($template) {
        $htmlView = new ZMEmailView($template, true);
        $textView = new ZMEmailView($template, false);
        if (file_exists($htmlView->getViewFilename()) && file_exists($textView->getViewFilename())) {
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
     * @package net.radebatz.zenmagick.misc
     * @param string subject The subject.
     * @param string template The email template name.
     * @param array args Additional stuff to be made available to the template.
     * @param string toEmail The recipients email address.
     * @param string toName Optional recipients name; default is <code>$toEmail</code>.
     * @param string fromEmail Optional sender email address; default is <code>storeEmailFrom</code>.
     * @param string fromName Optional sender name; default is <code>$fromEmail</code>.
     * @param string attachment Optional <strong>single</strong> file attachment.
     */
    function zm_mail($subject, $template, $args, $toEmail, $toName=null, $fromEmail=null, $fromName=null, $attachment=null) {
    global $zm_request;

        // some argument cleanup
        $args = null !== $args ? $args : array();
        $toName = null !== $toName ? $toName : $toEmail;
        $fromEmail = null !== $fromEmail ? $fromEmail : zm_setting('storeEmailFrom');
        $fromName = null !== $fromName ? $fromName : $fromEmail;
        // this is sooo weiyrd!
        $attparam = '';
        if (null !== $atttachment) {
            $attparam = array('file' => $attachment);
        }

        $formats = zm_email_formats($template);
        $hasTextTemplate = 'text' == $formats || 'both' == $formats;

        // generate text; $zc_args is an array with the original zen-cart values
        $view = new ZMEmailView($template, !$hasTextTemplate, array('zc_args' => $args));
        $view->setController($zm_request->getController());
        $text = $view->generate();

        // call actual mail function; the name corresponds to the one used in the installation patch
        $mailFunc = function_exists('zen_mail_org') ? 'zen_mail_org' : 'zen_mail';
        $mailFunc($toName, $toEmail, $subject, $text, $fromName, $fromEmail, $args, $template, $attparam);
    }

?>
