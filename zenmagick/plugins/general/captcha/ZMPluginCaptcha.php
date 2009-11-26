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

define('CAPTCHA_FIELD', 'captcha');


/**
 * Plugin to enable support for CAPTCHA in ZenMagick.
 *
 * @package org.zenmagick.plugins.captcha
 * @author DerManoMann
 * @version $Id: zm_captcha.php 2591 2009-11-11 03:48:45Z dermanomann $
 */
class ZMPluginCaptcha extends Plugin {
    private $captcha_;
    // page => (status, form_name)
    private $pageConfig_ = array(
        'create_account' => array(CAPTCHA_CREATE_ACCOUNT, 'registration'),
        'contact_us' => array(CAPTCHA_CONTACT_US, 'contactUs'),
        'tell_a_friend' => array(CAPTCHA_TELL_A_FRIEND, 'tellAFriend'),
        'product_reviews_write' => array(CAPTCHA_REVIEWS_WRITE, 'newReview')
    );
    private $captchaEnabled_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('CAPTCHA Plugin', 'CAPTCHA for ZenMagick', '${plugin.version}');
        $this->setLoaderPolicy(ZMPlugin::LP_ALL);
        $this->captchaEnabled_ = false;
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
        ZMDbUtils::executePatch(file(ZMDbUtils::resolveSQLFilename($this->getPluginDirectory()."sql/install.sql")), $this->messages_);

        $this->addConfigValue('Disable for registered users', 'disableRegistered', false, 'Disable the captcha for registered (logged in) users',
            'widget@BooleanFormWidget#name=disableRegistered&default=false&label=Disable&style=checkbox');
    }

    /**
     * {@inheritDoc}
     */
    public function init() {
        parent::init();
        ZMEvents::instance()->attach($this);
    }

    /**
     * {@inheritDoc}
     */
    public function remove($keepSettings=false) {
        parent::remove($keepSettings);
        ZMDbUtils::executePatch(file(ZMDbUtils::resolveSQLFilename($this->getPluginDirectory()."sql/uninstall.sql")), $this->messages_);
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

        // check if we need to do anything for this request...
        $disableRegistered = ZMLangUtils::asBoolean($this->get('disableRegistered'));
        if ($disableRegistered && $request->isRegistered()) {
            return;
        }

        $page = ZMRequest::instance()->getRequestId();
        if (array_key_exists($page, $this->pageConfig_)) {
            $this->captcha_ = new pcaptcha();
            $session = ZMRequest::instance()->getSession();
            $session->setValue('captcha_field', CAPTCHA_FIELD);
            $config = $this->pageConfig_[$page];
            if ('false' != $config[0]) {
                $form = $this->pageConfig_[$page][1];
                // active for this page
                $this->captchaEnabled_ = true;
                $rules = array(
                    array('RequiredRule', CAPTCHA_FIELD, 'Please enter the captcha.'),
                    array("WrapperRule", CAPTCHA_FIELD, 'The entered captcha is not correct.', array($this, 'vCaptcha'))
                );
                ZMValidator::instance()->addRules($form, $rules);
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
        $alt = zm_l10n_get("Click image to re-generate");
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

?>
