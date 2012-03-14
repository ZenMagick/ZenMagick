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

define('CAPTCHA_FIELD', 'captcha');

use zenmagick\base\Runtime;
use zenmagick\base\Toolbox;

/**
 * Plugin to enable support for CAPTCHA in ZenMagick.
 *
 * @package org.zenmagick.plugins.captcha
 * @author DerManoMann <mano@zenmagick.org>
 */
class ZMCaptchaPlugin extends Plugin {
    private $captcha_;
    // page => (status, form_name)
    private $pageConfig_;
    private $captchaEnabled_;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct('CAPTCHA Plugin', 'CAPTCHA for ZenMagick', '${plugin.version}');
        $this->captchaEnabled_ = false;
    }


    /**
     * {@inheritDoc}
     */
    public function install() {
        parent::install();
        ZMDbUtils::executePatch(file(ZMDbUtils::resolveSQLFilename($this->getPluginDirectory()."/sql/install.sql")), $this->messages_);

        $this->addConfigValue('Disable for registered users', 'disableRegistered', false, 'Disable the captcha for registered (logged in) users',
            'widget@booleanFormWidget#name=disableRegistered&default=false&label=Disable&style=checkbox');
    }

    /**
     * {@inheritDoc}
     */
    public function init() {
        parent::init();
        Runtime::getEventDispatcher()->listen($this);
        // page => (status, form_name)
        $this->pageConfig_ = array(
            'create_account' => array('true'==CAPTCHA_CREATE_ACCOUNT, 'registration'),
            'contact_us' => array('true'==CAPTCHA_CONTACT_US, 'contactUs'),
            'tell_a_friend' => array('true'==CAPTCHA_TELL_A_FRIEND, 'tellAFriend'),
            'product_reviews_write' => array('true'==CAPTCHA_REVIEWS_WRITE, 'newReview')
        );
    }

    /**
     * {@inheritDoc}
     */
    public function remove($keepSettings=false) {
        parent::remove($keepSettings);
        ZMDbUtils::executePatch(file(ZMDbUtils::resolveSQLFilename($this->getPluginDirectory()."/sql/uninstall.sql")), $this->messages_);
    }


    /**
     * Init done callback.
     *
     * <p>Setup additional validation rules; this is done here to avoid getting in the way of
     * custom global/theme validation rule setups.</p>
     */
    public function onContainerReady($event) {
        $request = $event->get('request');

        // check if we need to do anything for this request...
        $disableRegistered = Toolbox::asBoolean($this->get('disableRegistered'));
        if ($disableRegistered && $request->isRegistered()) {
            return;
        }

        $requestId = $request->getRequestId();
        if (array_key_exists($requestId, $this->pageConfig_)) {
            $this->captcha_ = new PCaptcha($request);
            $session = $request->getSession();
            $session->setValue('captcha_field', CAPTCHA_FIELD);
            $config = $this->pageConfig_[$requestId];
            if ($config[0]) {
                $form = $this->pageConfig_[$requestId][1];
                // active for this page
                $this->captchaEnabled_ = true;
                $rules = array(
                    array('ZMRequiredRule', CAPTCHA_FIELD, 'Please enter the captcha.'),
                    array("ZMWrapperRule", CAPTCHA_FIELD, 'The entered captcha is not correct.', array($this, 'vCaptcha'))
                );
                $this->container->get('validator')->addRules($form, $rules);
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
        $parameters .= ' onclick="document.getElementById(\'captcha-img\').src=\''.$this->captcha_->img_href.'&amp;rand=\'+Math.random();return false;"';
        $parameters .= ' style="cursor:pointer;cursor:hand;"';
        echo $this->captcha_->img($alt, $width, $height, $parameters);
    }

    /**
     * Validate the captcha value.
     *
     * @param ZMRequest request The current request.
     * @param array data The data.
     * @return boolean <code>true</code> if the captcha is valid, <code>false</code> if not.
     */
    public function vCaptcha($request, $data) {
        if (ZMLangUtils::isEmpty($request->getParameter(CAPTCHA_FIELD))) {
            // we have a required rule, so no need for additional checks
            return true;
        }

        return $this->getCaptcha()->validateCaptchaCode();
    }

}
