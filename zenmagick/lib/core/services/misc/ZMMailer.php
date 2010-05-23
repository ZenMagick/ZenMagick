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
 * <em>SwiftMailer</em> Email service.
 *
 * <p>Thin wrapper for SwiftMailer, mostly to centralize initialization and such.</p>
 *
 * <p>Due to the mostly generic function/method names it should be easy to replace this
 * with some other library without causing too much trouble.</p>
 *
 * <p>Supports the following transports (the transport is configured via the setting 'zenmagick.core.email.transport'):</p>
 * <dl>
 *  <dt>smtp</dt>
 *  <dd>
 *   <p>Requires the following settings:</p>
 *   <ul>
 *    <li>zenmagick.core.email.smtp.host</li>
 *    <li>zenmagick.core.email.smtp.port</li>
 *    <li>zenmagick.core.email.smtp.user (optional)</li>
 *    <li>zenmagick.core.email.smtp.password (optional)</li>
 *   </ul>
 *  </dd>
 *  <dt>PHP</dt>
 *  <dd>
 *   <p>Use PHP's mail interface.</p>
 *  </dd>
 *  <dt>sendmail</dt>
 *  <dd>
 *   <p>Use sendmail. The default sendmail path (and parameters) is <code>/usr/sbin/sendmail -bs</code>. If this needs to be changed you may set
 *    <em>'zenmagick.core.email.sendmail'</em> to whatever the path is.</p>
 *  </dd>
 * </dl>
 *
 * @author DerManoMann
 * @package org.zenmagick.core.services.misc
 */
class ZMMailer extends ZMObject {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        ZMSwiftInit::init();
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
        return ZMObject::singleton('Mailer');
    }


    /**
     * Get a transport.
     *
     * @return mixed The transport.
     */
    public function getTransport() {
        $transport = ZMSettings::get('zenmagick.core.email.transport', 'smtp');
        if ('smtp' == $transport) {
            //XXX: do we need a new instance for each call??
            $transport = Swift_SmtpTransport::newInstance(ZMSettings::get('zenmagick.core.email.smtp.host', 'localhost'), 
                  (int)ZMSettings::get('zenmagick.core.email.smtp.port', 25))
              ->setUsername(ZMSettings::get('zenmagick.core.email.smtp.user', ''))
              ->setPassword(ZMSettings::get('zenmagick.core.email.smtp.password', ''))
              ;
        } else if ('PHP' == $transport) {
            $transport = Swift_MailTransport::newInstance();
        } else if ('sendmail' == $transport) {
            $transport = Swift_SendmailTransport::newInstance(ZMSettings::get('zenmagick.core.email.sendmail', '/usr/sbin/sendmail -bs'));
        } else {
            ZMLogging::instance()->log('invalid transport: '.$transport, ZMLogging::ERROR);
            throw new ZMException('invalid transport: '.$transport);
        }

        return $transport;
    }

    /**
     * Get a mailer instance.
     *
     * @param mixed transport Optional transport; default is <code>null</code> to use the default transport.
     * @return mixed A mailer.
     */
    public function getMailer($transport=null) {
        $transport = null != $transport ? $transport : $this->getTransport();
        //XXX: do we need a new instance for each call??
        $mailer = Swift_Mailer::newInstance($transport);
        // set up ZenMagick logger
        $mailer->registerPlugin(new Swift_Plugins_LoggerPlugin(new ZMSwiftLogger()));
        return $mailer;
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
