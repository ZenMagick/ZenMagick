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
namespace ZenMagick\plugins\recaptcha;

use ZenMagick\apps\store\plugins\Plugin;
use ZenMagick\Base\Runtime;
use ZenMagick\Base\Toolbox;


define('RECAPTCHA_FIELD', 'recaptcha_response_field');

/**
 * Plugin to enable support for ReCAPTCHA in ZenMagick.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class RecaptchaPlugin extends Plugin {
    private $captchaEnabled_ = false;
    private $error_ = null;
    // page => (name, form)
    private $pageConfig_ = array(
        'create_account' => array('Create Account', 'registration'),
        'contact_us' => array('Contact Us', 'contactUs'),
        'tell_a_friend' => array('Tell A Friend', 'tellAFriend'),
        'product_reviews_write' => array('Write Review', 'newReview')
    );


    /**
     * Init done callback.
     *
     * <p>Setup additional validation rules; this is done here to avoid getting in the way of
     * custom global/theme validation rule setups.</p>
     */
    public function onContainerReady($event) {
        $request = $event->get('request');
        $disableRegistered = Toolbox::asBoolean($this->get('disableRegistered'));
        if ($disableRegistered && $request->getSession()->isRegistered()) {
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
            $this->container->get('zmvalidator')->addRules($form, $rules);
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
     * @param ZenMagick\Http\Request request The current request.
     * @param array data The data.
     * @return boolean <code>true</code> if the captcha is valid, <code>false</code> if not.
     */
    public function vRecaptcha($request, $data) {
        if (Toolbox::isEmpty($request->getParameter(RECAPTCHA_FIELD))) {
            // we have a required rule, so no need for additional checks
            return true;
        }

        $resp = recaptcha_check_answer ($this->get('privateKey'),
                                        $request->getClientIp(),
                                        $request->request->get('recaptcha_challenge_field'),
                                        $request->request->get('recaptcha_response_field'));

        if ($resp->is_valid) {
            return true;
        } else {
            $this->setError($resp->error);
            return false;
        }
    }

}
