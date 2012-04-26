<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace zenmagick\plugins\recaptcha;

use Plugin;
use zenmagick\base\Runtime;
use zenmagick\base\Toolbox;


define('RECAPTCHA_FIELD', 'recaptcha_response_field');

/**
 * Plugin to enable support for ReCAPTCHA in ZenMagick.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class RecaptchaPlugin extends Plugin {
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
    public function __construct() {
        parent::__construct('ReCAPTCHA Plugin', 'ReCAPTCHA for ZenMagick');
        $this->captchaEnabled_ = false;
        $this->error_ = null;
    }


    /**
     * {@inheritDoc}
     */
    public function install() {
        parent::install();

        $this->addConfigValue('Public Key', 'publicKey', '', 'ReCAPTCHA public key');
        $this->addConfigValue('Private Key', 'privateKey', '', 'ReCAPTCHA private key');
        $this->addConfigValue('Disable for registered users', 'disableRegistered', false, 'Disable the captcha for registered (logged in) users',
            'widget@booleanFormWidget#name=disableRegistered&default=false&label=Disable&style=checkbox');
        foreach ($this->pageConfig_ as $key => $config) {
            $this->addConfigValue($config[0].' page', $key, true,
                'Use ReCAPTCHA on '.$config[0].' page',
                'widget@booleanFormWidget#name='.$key.'&default=true&label=Enable&style=checkbox');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function init() {
        parent::init();
        Runtime::getEventDispatcher()->listen($this);
    }

    /**
     * Init done callback.
     *
     * <p>Setup additional validation rules; this is done here to avoid getting in the way of
     * custom global/theme validation rule setups.</p>
     */
    public function onContainerReady($event) {
        $request = $event->get('request');
        $disableRegistered = Toolbox::asBoolean($this->get('disableRegistered'));
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
                array('ZMRequiredRule', RECAPTCHA_FIELD, 'Please enter the captcha.'),
                array("ZMWrapperRule", RECAPTCHA_FIELD, 'The entered captcha is not correct.', array($this, 'vRecaptcha'))
            );
            \ZMValidator::instance()->addRules($form, $rules);
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

    /**
     * Validate the captcha value.
     *
     * @param ZMRequest request The current request.
     * @param array data The data.
     * @return boolean <code>true</code> if the captcha is valid, <code>false</code> if not.
     */
    public function vRecaptcha($request, $data) {
        if (\ZMLangUtils::isEmpty($request->getParameter(RECAPTCHA_FIELD))) {
            // we have a required rule, so no need for additional checks
            return true;
        }

        $resp = recaptcha_check_answer ($this->get('privateKey'),
                                        $_SERVER["REMOTE_ADDR"],
                                        $_POST["recaptcha_challenge_field"],
                                        $_POST["recaptcha_response_field"]);

        if ($resp->is_valid) {
            return true;
        } else {
            $this->setError($resp->error);
            return false;
        }
    }

}
