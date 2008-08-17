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
 */
?>
<?php


/**
 * Email sender class.
 *
 * @author DerManoMann
 * @package org.zenmagick
 * @version $Id$
 */
class ZMMailer extends ZMObject {
    var $phpMailer_;

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->phpMailer_ = null;
        $this->init();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Prepare (internal PHP) mailer.
     */
    function init() {
        $this->phpMailer_ = new PHPMailer();
        $languageCode = strtolower(($_SESSION['languages_code'] == '' ? 'en' : $_SESSION['languages_code'] ));
        $this->phpMailer_->SetLanguage($languageCode, DIR_FS_CATALOG . DIR_WS_CLASSES . 'support/');
        $this->phpMailer_->CharSet =  (defined('CHARSET')) ? CHARSET : "iso-8859-1";
        $this->phpMailer_->Encoding = (defined('EMAIL_ENCODING_METHOD')) ? EMAIL_ENCODING_METHOD : "7bit";
        if ((int)EMAIL_SYSTEM_DEBUG > 0) {
            $this->phpMailer_->SMTPDebug = (int)EMAIL_SYSTEM_DEBUG;
        }
        // set word wrap to 76 characters
        $this->phpMailer_->WordWrap = 76;
        // set proper line-endings based on switch ... important for windows vs linux hosts:
        $this->phpMailer_->LE = (EMAIL_LINEFEED == 'CRLF') ? "\r\n" : "\n";

        switch (EMAIL_TRANSPORT) {
        case 'smtp':
            $this->phpMailer_->IsSMTP();
            $this->phpMailer_->Host = trim(EMAIL_SMTPAUTH_MAIL_SERVER);
            if (EMAIL_SMTPAUTH_MAIL_SERVER_PORT != '25' && EMAIL_SMTPAUTH_MAIL_SERVER_PORT != '') {
                $this->phpMailer_->Port = trim(EMAIL_SMTPAUTH_MAIL_SERVER_PORT);
            }
            $this->phpMailer_->LE = "\r\n";
            break;
        case 'smtpauth':
            $this->phpMailer_->IsSMTP();
            $this->phpMailer_->SMTPAuth = true;
            $this->phpMailer_->Username = (zen_not_null(EMAIL_SMTPAUTH_MAILBOX)) ? trim(EMAIL_SMTPAUTH_MAILBOX) : EMAIL_FROM;
            $this->phpMailer_->Password = trim(EMAIL_SMTPAUTH_PASSWORD);
            $this->phpMailer_->Host = trim(EMAIL_SMTPAUTH_MAIL_SERVER);
            if (EMAIL_SMTPAUTH_MAIL_SERVER_PORT != '25' && EMAIL_SMTPAUTH_MAIL_SERVER_PORT != '') {
                $this->phpMailer_->Port = trim(EMAIL_SMTPAUTH_MAIL_SERVER_PORT);
            }
            $this->phpMailer_->LE = "\r\n";
            //set encryption protocol to allow support for Gmail
            if (EMAIL_SMTPAUTH_MAIL_SERVER_PORT == '465' && EMAIL_SMTPAUTH_MAIL_SERVER == 'smtp.gmail.com') {
                $this->phpMailer_->Protocol = 'ssl';
            }
            if (defined('SMTPAUTH_EMAIL_PROTOCOL') && SMTPAUTH_EMAIL_PROTOCOL != 'none') {
                $this->phpMailer_->Protocol = SMTPAUTH_EMAIL_PROTOCOL;
                if (SMTPAUTH_EMAIL_PROTOCOL == 'starttls'){
                    $this->phpMailer_->Starttls = true;
                    // TODO: what is this??
                    $this->phpMailer_->Context = $Email_Certificate_Context;
                }
            }
            break;
        case 'PHP':
            $this->phpMailer_->IsMail();
            break;
        case 'Qmail':
            $this->phpMailer_->IsQmail();
            break;
        case 'sendmail':
        case 'sendmail-f':
            $this->phpMailer_->LE = "\n";
        default:
            $this->phpMailer_->IsSendmail();
            if (defined('EMAIL_SENDMAIL_PATH')) {
                $this->phpMailer_->Sendmail = trim(EMAIL_SENDMAIL_PATH);
            }
            break;
        }
    }

}

?>
