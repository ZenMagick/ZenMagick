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

define('ZM_CAPTCHA_FIELD', 'captcha');


/**
 * Plugin to enable support for CAPTCHA in ZenMagick.
 *
 * @package org.zenmagick.plugins.zm_captcha
 * @author mano
 * @version $Id$
 */
class zm_captcha extends ZMPlugin {
    var $captcha_;
    // page => (status, form_name)
    var $pageConfig_ = array(
        'create_account' => array(CAPTCHA_CREATE_ACCOUNT, 'create_account'),
        'contact_us' => array(CAPTCHA_CONTACT_US, 'contact_us'),
        'tell_a_friend' => array(CAPTCHA_TELL_A_FRIEND, 'tell_a_friend'),
        'links_submit' => array(CAPTCHA_LINKS_SUBMIT, 'links_submit'),
        'product_reviews_write' => array(CAPTCHA_REVIEWS_WRITE, 'review')
    );
    var $captchaEnabled_;


    /**
     * Create new instance.
     */
    function zm_captcha() {
        parent::__construct('ZenMagick CAPTCHA Plugin', 'CAPTCHA for ZenMagick', '${plugin.version}');
        $this->setLoaderSupport('ALL');
        $this->captchaEnabled_ = false;
    }

    /**
     * Create new instance.
     */
    function __construct() {
        $this->zm_captcha();
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
        ZMDbUtils::executePatch(file($this->getPluginDir()."install.sql"), $this->messages_);
    }

    /**
     * Remove this plugin.
     *
     * @param boolean keepSettings If set to <code>true</code>, the settings will not be removed; default is <code>false</code>.
     */
    function remove($keepSettings=false) {
        parent::remove($keepSettings);
        ZMDbUtils::executePatch(file($this->getPluginDir()."uninstall.sql"), $this->messages_);
    }


    /**
     * Init this plugin.
     */
    function init() {
        parent::init();

        // check if we need to do anything for this request...
        $page = ZMRequest::getPageName();
        if (array_key_exists($page, $this->pageConfig_)) {
            $this->captcha_ = new pcaptcha();
            $session = ZMRequest::getSession();
            $session->setValue('captcha_field', ZM_CAPTCHA_FIELD);
            $config = $this->pageConfig_[$page];
            if ('false' != $config[0]) {
                // active for this page
                $this->captchaEnabled_ = true;
                ZMValidator::instance()->addRule($config[1], new ZMRequiredRule(ZM_CAPTCHA_FIELD, 'Please enter the captcha.'));
                $captchaRule = $this->create("WrapperRule", ZM_CAPTCHA_FIELD, 'The entered captcha is not correct.');
                $captchaRule->setFunction('zm_captcha_validate');
                ZMValidator::instance()->addRule($config[1], $captchaRule);
            }
        }
    }


    /**
     * Get the captcha instance.
     *
     * @return captcha The captcha.
     */
    function getCaptcha() {
        return $this->captcha_;
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
    function showImage($width='', $height='', $parameters='') {
        $alt = zm_l10n_get("Click image to re-generate");
        $parameters .= ' onclick="document.getElementById(\'captcha-img\').src=\''.$this->captcha_->img_href.'&amp;rand=\'+Math.random();return false;"';
        $parameters .= ' style="cursor:pointer;cursor:hand;"';
        echo $this->captcha_->img($alt, $width, $height, $parameters);
    }

}


/**
 * Validate the captcha value.
 *
 * @package org.zenmagick.plugins.zm_captcha
 * @param array req The request data.
 * @return boolean <code>true</code> if the captcha is valid, <code>false</code> if not.
 */
function zm_captcha_validate($req) {
global $zm_captcha;

    if (zm_is_empty(ZMRequest::getParameter(ZM_CAPTCHA_FIELD))) {
        // we have a required rule, so no need for additional checks
        return true;
    }
    $captcha = $zm_captcha->getCaptcha();
    return $captcha->validateCaptchaCode();
}

?>
