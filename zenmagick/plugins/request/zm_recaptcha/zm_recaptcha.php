<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
class zm_recaptcha extends Plugin {
    private $captchaEnabled_;
    private $error_;
    // page => (name, form)
    private $pageConfig_ = array(
        'create_account' => array('Create Account', 'registration'),
        'contact_us' => array('Contact Us', 'contactUs'),
        'tell_a_friend' => array('Tell A Friend', 'tellAFriend'),
        'product_reviews_write' => array('Write Review', 'newReview')
    );


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('ReCAPTCHA Plugin', 'ReCAPTCHA for ZenMagick');
        $this->setLoaderPolicy(ZMPlugin::LP_ALL);
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
     * {@inheritDoc}
     */
    public function install() {
        parent::install();

        $this->addConfigValue('Public Key', 'publicKey', '', 'ReCAPTCHA public key');
        $this->addConfigValue('Private Key', 'privateKey', '', 'ReCAPTCHA private key');
        $this->addConfigValue('Disable for registered users', 'disableRegistered', false, 'Disable the captcha for registered (logged in) users',
            "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>'Yes'), array('id'=>'0', 'text'=>'No')), ");
        foreach ($this->pageConfig_ as $key => $config) {
            $this->addConfigValue($config[0].' page', $key, true,
                'Use ReCAPTCHA on '.$config[0].' page',
                "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>'Yes'), array('id'=>'0', 'text'=>'No')), ");
        }
    }

    /**
     * {@inheritDoc}
     */
    public function init() {
        parent::init();
        ZMEvents::instance()->attach($this);
    }

    /**
     * Init done callback.
     *
     * <p>Setup additional validation rules; this is done here to avoid getting in the way of
     * custom global/theme validation rule setups.</p>
     *
     * @param array args Optional parameter.
     */
    public function onZMInitDone($args=null) {
        $request = $args['request'];
        $disableRegistered = ZMLangUtils::asBoolean($this->get('disableRegistered'));
        if ($disableRegistered && $request->isRegistered()) {
            // skip
            return;
        }

        // check if we need to do anything for this request...
        $requestId = $request->getRequestId();
        if (true == $this->get($requestId) && isset($this->pageConfig_[$requestId])) { 
            $form = $this->pageConfig_[$requestId][1];
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
    public function isCaptchaEnabled() {
        return $this->captchaEnabled_;
    }

    /**
     * Create the captcha image.
     */
    public function showCaptcha() {
        if ($this->captchaEnabled_) {
            echo recaptcha_get_html($this->get('publicKey'), $this->getError());
        }
    }

    /**
     * Get error (if any).
     *
     * @return string The error or <code>null</code>.
     */
    public function getError() {
        return $this->error_;
    }

    /**
     * Set error.
     *
     * @param string error The error.
     */
    public function setError($error) {
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
    if (ZMLangUtils::isEmpty(ZMRequest::instance()->getParameter(ZM_RECAPTCHA_FIELD))) {
        // we have a required rule, so no need for additional checks
        return true;
    }

    $plugin = ZMPlugins::instance()->getPluginForId('zm_recaptcha');

    $resp = recaptcha_check_answer ($plugin->get('privateKey'),
                                    $_SERVER["REMOTE_ADDR"],
                                    $_POST["recaptcha_challenge_field"],
                                    $_POST["recaptcha_response_field"]);

    if ($resp->is_valid) {
        return true;
    } else {
        $plugin->setError($resp->error);
        return false;
    }
}

?>
