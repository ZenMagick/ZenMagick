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
namespace ZenMagick\plugins\captcha;

define('CAPTCHA_FIELD', 'captcha');

use ZenMagick\Base\Plugins\Plugin;
use ZenMagick\Base\Runtime;
use ZenMagick\Base\Toolbox;


/**
 * Plugin to enable support for CAPTCHA in ZenMagick.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class CaptchaPlugin extends Plugin {
    private $captcha_;
    // page => (status, form_name)
    private $captchaEnabled_ = false;


    /**
     * Init done callback.
     *
     * <p>Setup additional validation rules; this is done here to avoid getting in the way of
     * custom global/theme validation rule setups.</p>
     */
    public function onDispatchStart($event) {
        if (Runtime::isContextMatch('admin')) return;
        // page => (status, form_name)
        $pageConfig = array(
            'create_account' => array('true'==CAPTCHA_CREATE_ACCOUNT, 'registration'),
            'contact_us' => array('true'==CAPTCHA_CONTACT_US, 'contactUs'),
            'tell_a_friend' => array('true'==CAPTCHA_TELL_A_FRIEND, 'tellAFriend'),
            'product_reviews_write' => array('true'==CAPTCHA_REVIEWS_WRITE, 'newReview')
        );

        $request = $event->get('request');
        $session = $request->getSession();

        // check if we need to do anything for this request...
        $disableRegistered = Toolbox::asBoolean($this->get('disableRegistered'));
        if ($disableRegistered && $session->isRegistered()) {
            return;
        }

        $requestId = $request->getRequestId();
        if (array_key_exists($requestId, $pageConfig)) {
            $this->captcha_ = new PCaptcha($request);
            $session->set('captcha_field', CAPTCHA_FIELD);
            $config = $pageConfig[$requestId];
            if ($config[0]) {
                $form = $pageConfig[$requestId][1];
                // active for this page
                $this->captchaEnabled_ = true;
                $rules = array(
                    array('ZMRequiredRule', CAPTCHA_FIELD, 'Please enter the captcha.'),
                    array("ZMWrapperRule", CAPTCHA_FIELD, 'The entered captcha is not correct.', array($this, 'vCaptcha'))
                );
                $this->container->get('zmvalidator')->addRules($form, $rules);
            }
        }
    }


    /**
     * Get the captcha instance.
     *
     * @return captcha The captcha.
     */
    public function getCaptcha() {
        return $this->captcha_;
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
     *
     * <p>This is a convenience method around <code>$captcha->img()</code>.</p>
     *
     * <p>This method will automatically generate an <code>onclick</code> attribute to
     * regenerate the image on click.</p>
     *
     * @param string width Optional width.
     * @param string height Optional height.
     * @param string parameter Optional additional parameter (name="value" name2="value2"...).
     */
    public function showImage($width='', $height='', $parameters='') {
        $alt = _zm("Click image to re-generate");
        $parameters .= ' onclick="document.getElementById(\'captcha-img\').src=\''.$this->captcha_->img_href.'?rand=\'+Math.random();return false;"';
        $parameters .= ' style="cursor:pointer;cursor:hand;"';
        echo $this->captcha_->img($alt, $width, $height, $parameters);
    }

    /**
     * Validate the captcha value.
     *
     * @param ZenMagick\Http\Request request The current request.
     * @param array data The data.
     * @return boolean <code>true</code> if the captcha is valid, <code>false</code> if not.
     */
    public function vCaptcha($request, $data) {
        if (Toolbox::isEmpty($request->getParameter(CAPTCHA_FIELD))) {
            // we have a required rule, so no need for additional checks
            return true;
        }

        return $this->getCaptcha()->validateCaptchaCode();
    }

}
