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
 */
?>
<?php

define('ZM_RECAPTCHA_FIELD', 'recaptcha_response_field');


/**
 * Plugin to enable support for ReCAPTCHA in ZenMagick.
 *
 * @package org.zenmagick.plugins.zm_recaptcha
 * @author mano
 * @version $Id$
 */
class zm_recaptcha extends ZMPlugin {
    var $captchaEnabled_;
    var $error_;
    // page => (name, form)
    var $pageConfig_ = array(
        'create_account' => array('Create Account', 'create_account'),
        'contact_us' => array('Contact Us', 'contact_us'),
        'tell_a_friend' => array('Tell A Friend', 'tell_a_friend'),
        'links_submit' => array('Links Submit', 'links_submit'),
        'product_reviews_write' => array('Write Review', 'review')
    );


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('ReCAPTCHA Plugin', 'ReCAPTCHA for ZenMagick');
        $this->setLoaderSupport('ALL');
        $this->captchaEnabled_ = false;
        $this->error_ = null;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Install this plugin.
     */
    function install() {
        parent::install();

        $this->addConfigValue('Public Key', 'publicKey', '', 'ReCAPTCHA public key');
        $this->addConfigValue('Private Key', 'privateKey', '', 'ReCAPTCHA private key');
        foreach ($this->pageConfig_ as $key => $config) {
            $this->addConfigValue($config[0].' page', $key, true,
                'Use ReCAPTCHA on '.$config[0].' page',
                "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>'Yes'), array('id'=>'0', 'text'=>'No')), ");
        }
    }

    /**
     * Init this plugin.
     */
    function init() {
        parent::init();

        // check if we need to do anything for this request...
        $page = ZMRequest::getPageName();
        if (true == $this->get($page) && isset($this->pageConfig_[$page])) { 
            $form = $this->pageConfig_[$page][1];
            // active for this page
            $this->captchaEnabled_ = true;
            $rules = array(
                array('RequiredRule', ZM_RECAPTCHA_FIELD, 'Please enter the captcha.'),
                array("WrapperRule", ZM_RECAPTCHA_FIELD, 'The entered captcha is not correct.', 'zm_recaptcha_validate')
            );
            ZMValidator::instance()->addRules($form, $rules);
        }
    }


    /**
     * Check if captcha is enabled for this request.
     *
     * @return boolean <code>true</code> if the captcha is enabled, <code>false</code> if not.
     */
    function isCaptchaEnabled() {
        return $this->captchaEnabled_;
    }

    /**
     * Create the captcha image.
     */
    function showCaptcha() {
        if ($this->captchaEnabled_) {
            echo recaptcha_get_html($this->get('publicKey'), $this->getError());
        }
    }

    /**
     * Get error (if any).
     *
     * @return string The error or <code>null</code>.
     */
    function getError() {
        return $this->error_;
    }

    /**
     * Set error.
     *
     * @param string error The error.
     */
    function setError($error) {
        $this->error_ = $error;
    }

}


/**
 * Validate the captcha value.
 *
 * @package org.zenmagick.plugins.zm_recaptcha
 * @param array req The request data.
 * @return boolean <code>true</code> if the captcha is valid, <code>false</code> if not.
 */
function zm_recaptcha_validate($req) {
global $zm_recaptcha;

    if (ZMTools::isEmpty(ZMRequest::getParameter(ZM_RECAPTCHA_FIELD))) {
        // we have a required rule, so no need for additional checks
        return true;
    }

    $resp = recaptcha_check_answer ($zm_recaptcha->get('privateKey'),
                                    $_SERVER["REMOTE_ADDR"],
                                    $_POST["recaptcha_challenge_field"],
                                    $_POST["recaptcha_response_field"]);

    if ($resp->is_valid) {
        return true;
    } else {
        $zm_recaptcha->setError($resp->error);
        return false;
    }
}

?>
